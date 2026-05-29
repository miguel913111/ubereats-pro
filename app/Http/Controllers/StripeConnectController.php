<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Store;
use App\Models\DeliveryMan;
use App\Models\BusinessSetting;
use App\Services\StripeConnectService;
use App\CentralLogics\OrderLogic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeConnectController extends Controller
{
    private StripeConnectService $stripeService;

    public function __construct(StripeConnectService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Criar PaymentIntent quando o cliente faz checkout
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validator = validator()->make($request->all(), [
            'order_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $order = Order::with(['store', 'delivery_man'])->find($request->order_id);

        if (!$order) {
            return response()->json(['error' => 'Pedido não encontrado'], 404);
        }

        $store = $order->store;
        $deliveryMan = $order->delivery_man;

        // Verificar se o restaurante tem conta Stripe ativa (log apenas, não bloquear)
        if (!$store?->stripe_account_id || !$store?->stripe_onboarding_complete) {
            \Log::warning('Stripe Connect: Restaurante sem onboarding completo', ['store_id' => $store?->id, 'order_id' => $order->id]);
        }

        // Se for delivery, verificar entregador também (log apenas, não bloquear)
        if ($order->order_type === 'delivery' && $deliveryMan) {
            if (!$deliveryMan->stripe_account_id || !$deliveryMan->stripe_onboarding_complete) {
                \Log::warning('Stripe Connect: Entregador sem onboarding completo', ['dm_id' => $deliveryMan->id, 'order_id' => $order->id]);
            }
        }

        // Calcular valores em cêntimos
        $totalCents = (int) round($order->order_amount * 100);
        
        // Fee da plataforma: 0,60€ em delivery, 1,00€ em take away
        $platformFeeCents = $order->order_type === 'take_away' ? 100 : 60;
        
        // Restaurante recebe: total - delivery_charge - dm_tips - fee
        $restaurantCents = (int) round(($order->order_amount - $order->delivery_charge - ($order->dm_tips ?? 0)) * 100) - $platformFeeCents;
        
        // Entregador recebe: delivery_charge + dm_tips
        $deliveryManCents = (int) round(($order->delivery_charge + ($order->dm_tips ?? 0)) * 100);

        // Garantir que não há valores negativos
        $restaurantCents = max(0, $restaurantCents);
        $deliveryManCents = max(0, $deliveryManCents);

        $transferGroup = 'order_' . $order->id;

        $metadata = [
            'order_id' => (string) $order->id,
            'store_id' => (string) $store->id,
            'delivery_man_id' => $deliveryMan ? (string) $deliveryMan->id : null,
            'platform_fee_cents' => (string) $platformFeeCents,
            'restaurant_cents' => (string) $restaurantCents,
            'delivery_man_cents' => (string) $deliveryManCents,
            'transfer_group' => $transferGroup,
            'order_type' => $order->order_type,
        ];

        $result = $this->stripeService->createPaymentIntent($totalCents, 'eur', $metadata);

        if (!$result) {
            return response()->json(['error' => 'Erro ao criar pagamento. Tente novamente.'], 500);
        }

        // Guardar PaymentIntent ID no pedido para referência
        $order->transaction_reference = $result['id'];
        $order->save();

        return response()->json([
            'client_secret' => $result['client_secret'],
            'payment_intent_id' => $result['id'],
        ]);
    }

    /**
     * Webhook do Stripe — executa split automático
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        
        // Obter webhook secret das configurações ou env
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET', '');

        try {
            if ($endpointSecret) {
                $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            } else {
                // Se não houver webhook secret configurado, decodificar manualmente (menos seguro)
                $event = json_decode($payload);
            }
        } catch (SignatureVerificationException $e) {
            return response('Webhook signature invalid: ' . $e->getMessage(), 400);
        } catch (\Exception $e) {
            return response('Webhook error: ' . $e->getMessage(), 400);
        }

        // Pagamento confirmado
        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            
            $metadata = $paymentIntent->metadata;
            
            $orderId = $metadata->order_id ?? null;
            $storeId = $metadata->store_id ?? null;
            $deliveryManId = $metadata->delivery_man_id ?? null;
            
            $restaurantCents = (int) ($metadata->restaurant_cents ?? 0);
            $deliveryManCents = (int) ($metadata->delivery_man_cents ?? 0);
            $transferGroup = $metadata->transfer_group ?? ('order_' . $orderId);

            if (!$orderId || !$storeId) {
                return response('Missing metadata', 400);
            }

            $order = Order::find($orderId);
            $store = Store::find($storeId);
            $deliveryMan = $deliveryManId ? DeliveryMan::find($deliveryManId) : null;

            if (!$order || !$store) {
                return response('Order or store not found', 400);
            }

            // Evitar processar o mesmo pagamento duas vezes
            if ($order->payment_status === 'paid') {
                return response('Already processed', 200);
            }

            $splitSuccess = true;

            try {
                // 1. Transferir para o restaurante
                if ($restaurantCents > 0 && $store->stripe_account_id && $store->stripe_onboarding_complete) {
                    $transferOk = $this->stripeService->createTransfer(
                        $restaurantCents,
                        'eur',
                        $store->stripe_account_id,
                        $transferGroup,
                        'Pagamento pedido #' . $orderId
                    );
                    if (!$transferOk) {
                        $splitSuccess = false;
                        info('Stripe split: Restaurant transfer failed for order ' . $orderId);
                    }
                }

                // 2. Transferir para o entregador
                if ($deliveryManCents > 0 && $deliveryMan && $deliveryMan->stripe_account_id && $deliveryMan->stripe_onboarding_complete) {
                    $transferOk = $this->stripeService->createTransfer(
                        $deliveryManCents,
                        'eur',
                        $deliveryMan->stripe_account_id,
                        $transferGroup,
                        'Entrega pedido #' . $orderId
                    );
                    if (!$transferOk) {
                        $splitSuccess = false;
                        info('Stripe split: Deliveryman transfer failed for order ' . $orderId);
                    }
                }

                // 3. O que sobra fica na conta da plataforma (platform fee)

                // 4. Atualizar pedido como pago
                $order->payment_status = 'paid';
                $order->order_status = 'confirmed';
                $order->confirmed = now();
                $order->save();

                // 5. Criar transaction record
                OrderLogic::create_transaction($order, 'admin', 'completed');

                // 6. Notificar (opcional)
                if (!$splitSuccess) {
                    info('Stripe split completed with warnings for order ' . $orderId);
                } else {
                    info('Stripe split successful for order ' . $orderId);
                }

                return response('Webhook processed', 200);

            } catch (\Exception $e) {
                info('Stripe webhook exception: ' . $e->getMessage());
                
                // Mesmo que o split falhe, marca como pago para não bloquear o cliente
                $order->payment_status = 'paid';
                $order->order_status = 'confirmed';
                $order->confirmed = now();
                $order->save();

                return response('Payment marked as paid, split failed', 200);
            }
        }

        // Outros eventos que podem interessar
        if ($event->type === 'account.updated') {
            $account = $event->data->object;
            $this->handleAccountUpdated($account);
        }

        return response('Webhook received', 200);
    }

    /**
     * Atualizar status de onboarding quando a conta Stripe é atualizada
     */
    private function handleAccountUpdated($account): void
    {
        $accountId = $account->id;
        $chargesEnabled = $account->charges_enabled ?? false;
        $payoutsEnabled = $account->payouts_enabled ?? false;
        $isComplete = $chargesEnabled && $payoutsEnabled;

        // Procurar em stores
        $store = Store::where('stripe_account_id', $accountId)->first();
        if ($store) {
            $store->stripe_onboarding_complete = $isComplete ? 1 : 0;
            $store->save();
        }

        // Procurar em delivery_men
        $deliveryMan = DeliveryMan::where('stripe_account_id', $accountId)->first();
        if ($deliveryMan) {
            $deliveryMan->stripe_onboarding_complete = $isComplete ? 1 : 0;
            $deliveryMan->save();
        }
    }

    /**
     * Página de sucesso do onboarding
     */
    public function onboardingSuccess(Request $request)
    {
        $accountId = $request->get('account_id');
        
        if ($accountId) {
            $isComplete = $this->stripeService->checkOnboardingStatus($accountId);
            
            $store = Store::where('stripe_account_id', $accountId)->first();
            if ($store) {
                $store->stripe_onboarding_complete = $isComplete ? 1 : 0;
                $store->save();
                return redirect()->route('admin.store.edit', $store->id)
                    ->with('success', 'Stripe onboarding ' . ($isComplete ? 'completo!' : 'em progresso. Complete todos os passos.'));
            }

            $deliveryMan = DeliveryMan::where('stripe_account_id', $accountId)->first();
            if ($deliveryMan) {
                $deliveryMan->stripe_onboarding_complete = $isComplete ? 1 : 0;
                $deliveryMan->save();
                return redirect()->route('admin.users.delivery-man.list')
                    ->with('success', 'Stripe onboarding ' . ($isComplete ? 'completo!' : 'em progresso. Complete todos os passos.'));
            }
        }

        return redirect('/')->with('info', 'Onboarding processado.');
    }

    /**
     * Página de refresh do onboarding
     */
    public function onboardingRefresh(Request $request)
    {
        $accountId = $request->get('account_id');
        
        // Gerar novo link de onboarding
        if ($accountId && $this->stripeService->isConfigured()) {
            try {
                $stripe = $this->stripeService->getStripeClient();
                $accountLink = $stripe->accountLinks->create([
                    'account' => $accountId,
                    'refresh_url' => url('/stripe/onboarding/refresh?account_id=' . $accountId),
                    'return_url' => url('/stripe/onboarding/success?account_id=' . $accountId),
                    'type' => 'account_onboarding',
                ]);
                
                return redirect($accountLink->url);
            } catch (\Exception $e) {
                info('Stripe onboarding refresh error: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'Erro ao renovar link de onboarding.');
    }

    /**
     * API para verificar status da conta Stripe de um vendor/entregador
     */
    public function checkAccountStatus(Request $request): JsonResponse
    {
        $storeId = $request->get('store_id');
        $deliveryManId = $request->get('delivery_man_id');

        if ($storeId) {
            $store = Store::find($storeId);
            if (!$store) {
                return response()->json(['error' => 'Loja não encontrada'], 404);
            }

            $status = [
                'has_account' => !empty($store->stripe_account_id),
                'onboarding_complete' => (bool) $store->stripe_onboarding_complete,
            ];

            if ($store->stripe_account_id && !$store->stripe_onboarding_complete) {
                $status['is_active_now'] = $this->stripeService->checkOnboardingStatus($store->stripe_account_id);
                if ($status['is_active_now']) {
                    $store->stripe_onboarding_complete = 1;
                    $store->save();
                    $status['onboarding_complete'] = true;
                }
            }

            return response()->json($status);
        }

        if ($deliveryManId) {
            $dm = DeliveryMan::find($deliveryManId);
            if (!$dm) {
                return response()->json(['error' => 'Entregador não encontrado'], 404);
            }

            $status = [
                'has_account' => !empty($dm->stripe_account_id),
                'onboarding_complete' => (bool) $dm->stripe_onboarding_complete,
            ];

            if ($dm->stripe_account_id && !$dm->stripe_onboarding_complete) {
                $status['is_active_now'] = $this->stripeService->checkOnboardingStatus($dm->stripe_account_id);
                if ($status['is_active_now']) {
                    $dm->stripe_onboarding_complete = 1;
                    $dm->save();
                    $status['onboarding_complete'] = true;
                }
            }

            return response()->json($status);
        }

        return response()->json(['error' => 'store_id ou delivery_man_id necessário'], 400);
    }
}
