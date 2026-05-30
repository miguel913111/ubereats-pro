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
     * Obter utilizador autenticado a partir do Bearer token (validação manual)
     */
    private function getAuthUser(Request $request): ?\App\Models\User
    {
        $token = $request->bearerToken();
        if (!$token) {
            \Log::info('[StripeAuth] No bearer token provided');
            return null;
        }

        \Log::info('[StripeAuth] Token received: ' . substr($token, 0, 15) . '...');

        $hashed = hash('sha256', $token);
        \Log::info('[StripeAuth] SHA256 hash: ' . substr($hashed, 0, 15) . '...');

        // Passport - try hashed token
        $passportToken = \DB::table('oauth_access_tokens')
            ->where('id', $hashed)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($passportToken) {
            \Log::info('[StripeAuth] Found via oauth_access_tokens (hashed)');
            return \App\Models\User::find($passportToken->user_id);
        }

        // Passport - try plain token
        $passportTokenPlain = \DB::table('oauth_access_tokens')
            ->where('id', $token)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($passportTokenPlain) {
            \Log::info('[StripeAuth] Found via oauth_access_tokens (plain)');
            return \App\Models\User::find($passportTokenPlain->user_id);
        }

        // Check table exists and count tokens
        try {
            $tokenCount = \DB::table('oauth_access_tokens')->count();
            \Log::info('[StripeAuth] oauth_access_tokens count: ' . $tokenCount);
        } catch (\Exception $e) {
            \Log::info('[StripeAuth] oauth_access_tokens table error: ' . $e->getMessage());
        }

        // Sanctum fallback
        if (class_exists(\Laravel\Sanctum\PersonalAccessToken::class)) {
            $sanctumToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($sanctumToken) {
                \Log::info('[StripeAuth] Found via Sanctum');
                return $sanctumToken->tokenable;
            }
        }

        // Check personal_access_tokens (Sanctum table)
        $personalToken = \DB::table('personal_access_tokens')
            ->where('token', $hashed)
            ->first();

        if ($personalToken) {
            \Log::info('[StripeAuth] Found via personal_access_tokens');
            return \App\Models\User::find($personalToken->tokenable_id);
        }

        \Log::info('[StripeAuth] Token not found in any table');
        return null;
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

    /**
     * Criar ou obter Stripe Customer para o utilizador autenticado
     */
    public function getOrCreateCustomer(Request $request): JsonResponse
    {
        $user = $this->getAuthUser($request);
        if (!$user) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }

        // Se já tem customer_id, retornar
        if ($user->stripe_customer_id) {
            return response()->json(['customer_id' => $user->stripe_customer_id]);
        }

        // Criar novo customer
        $customerId = $this->stripeService->createCustomer(
            $user->email ?? '',
            $user->f_name . ' ' . ($user->l_name ?? ''),
            $user->phone
        );

        if (!$customerId) {
            return response()->json(['error' => 'Erro ao criar customer Stripe'], 500);
        }

        $user->stripe_customer_id = $customerId;
        $user->save();

        return response()->json(['customer_id' => $customerId]);
    }

    /**
     * Criar SetupIntent para salvar cartão
     */
    public function createSetupIntent(Request $request): JsonResponse
    {
        $user = $this->getAuthUser($request);
        if (!$user) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }

        // Garantir que o customer existe
        if (!$user->stripe_customer_id) {
            $customerResponse = $this->getOrCreateCustomer($request);
            if ($customerResponse->getStatusCode() !== 200) {
                return $customerResponse;
            }
            $user->refresh();
        }

        $result = $this->stripeService->createSetupIntent($user->stripe_customer_id);

        if (!$result) {
            return response()->json(['error' => 'Erro ao criar SetupIntent'], 500);
        }

        return response()->json($result);
    }

    /**
     * Listar cartões salvos do utilizador
     */
    public function listPaymentMethods(Request $request): JsonResponse
    {
        $user = $this->getAuthUser($request);
        if (!$user) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }

        if (!$user->stripe_customer_id) {
            return response()->json(['cards' => []]);
        }

        $cards = $this->stripeService->listPaymentMethods($user->stripe_customer_id);

        return response()->json(['cards' => $cards]);
    }

    /**
     * Remover cartão salvo
     */
    public function detachPaymentMethod(Request $request, string $paymentMethodId): JsonResponse
    {
        $user = $this->getAuthUser($request);
        if (!$user) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }

        $success = $this->stripeService->detachPaymentMethod($paymentMethodId);

        if (!$success) {
            return response()->json(['error' => 'Erro ao remover cartão'], 500);
        }

        return response()->json(['message' => 'Cartão removido com sucesso']);
    }

    /**
     * Modificar PaymentIntent para aceitar payment_method e customer
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validator = validator()->make($request->all(), [
            'order_id' => 'required|integer',
            'payment_method' => 'nullable|string',
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

        if (!$store?->stripe_account_id || !$store?->stripe_onboarding_complete) {
            \Log::warning('Stripe Connect: Restaurante sem onboarding completo', ['store_id' => $store?->id, 'order_id' => $order->id]);
        }

        if ($order->order_type === 'delivery' && $deliveryMan) {
            if (!$deliveryMan->stripe_account_id || !$deliveryMan->stripe_onboarding_complete) {
                \Log::warning('Stripe Connect: Entregador sem onboarding completo', ['dm_id' => $deliveryMan->id, 'order_id' => $order->id]);
            }
        }

        $totalCents = (int) round($order->order_amount * 100);
        $platformFeeCents = $order->order_type === 'take_away' ? 100 : 60;
        $restaurantCents = (int) round(($order->order_amount - $order->delivery_charge - ($order->dm_tips ?? 0)) * 100) - $platformFeeCents;
        $deliveryManCents = (int) round(($order->delivery_charge + ($order->dm_tips ?? 0)) * 100);

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

        $params = [
            'amount' => $totalCents,
            'currency' => 'eur',
            'automatic_payment_methods' => ['enabled' => true],
            'transfer_group' => $transferGroup,
            'metadata' => $metadata,
        ];

        // Se tem customer autenticado, associar ao PaymentIntent
        $user = $this->getAuthUser($request);
        if ($user && $user->stripe_customer_id) {
            $params['customer'] = $user->stripe_customer_id;
        }

        // Se enviou payment_method específico, usar esse
        if ($request->payment_method) {
            $params['payment_method'] = $request->payment_method;
        }

        $result = $this->stripeService->createPaymentIntentAdvanced($params);

        if (!$result) {
            return response()->json(['error' => 'Erro ao criar pagamento. Tente novamente.'], 500);
        }

        $order->transaction_reference = $result['id'];
        $order->save();

        return response()->json([
            'client_secret' => $result['client_secret'],
            'payment_intent_id' => $result['id'],
        ]);
    }

    /**
     * Criar PaymentIntent para MBWay
     */
    public function createMbwayPaymentIntent(Request $request): JsonResponse
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

        $totalCents = (int) round($order->order_amount * 100);
        $platformFeeCents = $order->order_type === 'take_away' ? 100 : 60;
        $restaurantCents = (int) round(($order->order_amount - $order->delivery_charge - ($order->dm_tips ?? 0)) * 100) - $platformFeeCents;
        $deliveryManCents = (int) round(($order->delivery_charge + ($order->dm_tips ?? 0)) * 100);

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

        $params = [
            'amount' => $totalCents,
            'currency' => 'eur',
            'payment_method_types' => ['mbway'],
            'transfer_group' => $transferGroup,
            'metadata' => $metadata,
        ];

        $result = $this->stripeService->createPaymentIntentAdvanced($params);

        if (!$result) {
            return response()->json(['error' => 'Erro ao criar pagamento MBWay. Tente novamente.'], 500);
        }

        $order->transaction_reference = $result['id'];
        $order->save();

        return response()->json([
            'client_secret' => $result['client_secret'],
            'payment_intent_id' => $result['id'],
        ]);
    }

    /**
     * Confirmar PaymentIntent MBWay com número de telefone
     */
    public function confirmMbwayPayment(Request $request): JsonResponse
    {
        $validator = validator()->make($request->all(), [
            'payment_intent_id' => 'required|string',
            'phone_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $stripe = $this->stripeService->getStripeClient();
            if (!$stripe) {
                return response()->json(['error' => 'Stripe não configurado'], 500);
            }

            $paymentIntent = $stripe->paymentIntents->confirm(
                $request->payment_intent_id,
                [
                    'payment_method_data' => [
                        'type' => 'mbway',
                        'mbway' => [
                            'phone_number' => $request->phone_number,
                        ],
                    ],
                ]
            );

            return response()->json([
                'status' => $paymentIntent->status,
                'payment_intent_id' => $paymentIntent->id,
                'requires_action' => $paymentIntent->status === 'requires_action',
            ]);
        } catch (\Exception $e) {
            info('Stripe MBWay confirm error: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao confirmar pagamento MBWay: ' . $e->getMessage()], 500);
        }
    }
}
