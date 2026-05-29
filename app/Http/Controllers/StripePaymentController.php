<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentRequest;
use App\Models\Order;
use App\Models\Store;
use App\Models\DeliveryMan;
use App\Services\StripeConnectService;
use App\Traits\Processor;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePaymentController extends Controller
{
    use Processor;

    private $config_values;
    private PaymentRequest $payment;
    private StripeConnectService $stripeService;

    public function __construct(PaymentRequest $payment, StripeConnectService $stripeService)
    {
        $config = $this->payment_config('stripe', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }
        $this->payment = $payment;
        $this->stripeService = $stripeService;
    }

    public function index(Request $request): View|Factory|JsonResponse|Application
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $config = $this->config_values;

        return view('payment-views.stripe', compact('data', 'config'));
    }

    public function payment_process_3d(Request $request): JsonResponse
    {
        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $payment_amount = $data['payment_amount'];

        Stripe::setApiKey($this->config_values->api_key);
        header('Content-Type: application/json');
        $currency_code = $data->currency_code;

        if (count(json_decode($data['additional_data'],true))>0) {
            $business = json_decode($data['additional_data']);
            $business_name = $business->business_name ?? "my_business";
            $business_logo = $business->business_logo ??  url('/');
        } else {
            $logo = \App\Models\BusinessSetting::where('key', 'logo')->first();
            $logo = $logo->value ?? '';
            $name = \App\Models\BusinessSetting::where('key', 'business_name')->first();
            $business_name = $name->value ?? "my_business";
            $business_logo = $logo ? asset('storage/app/public/business/' . $logo): url('/');
        }

        $currencies_not_supported_cents = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];
        
        $sessionParams = [
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency_code ?? 'usd',
                    'unit_amount' => in_array($currency_code, $currencies_not_supported_cents) ? (int)$payment_amount : ($payment_amount * 100),
                    'product_data' => [
                        'name' => $business_name,
                        'images' => [$business_logo],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url('/') . '/payment/stripe/success?session_id={CHECKOUT_SESSION_ID}&payment_id=' . $data->id,
            'cancel_url' => url('/') . '/payment/stripe/canceled?payment_id=' . $data->id,
        ];

        // Se for pagamento de pedido, adicionar metadata para split automático no webhook
        if ($data->attribute === 'order' && $data->attribute_id) {
            $order = Order::with(['store', 'delivery_man'])->find($data->attribute_id);
            
            if ($order) {
                $store = $order->store;
                $deliveryMan = $order->delivery_man;
                
                $totalCents = (int) round($order->order_amount * 100);
                $platformFeeCents = $order->order_type === 'take_away' ? 100 : 60;
                $restaurantCents = (int) round(($order->order_amount - $order->delivery_charge - ($order->dm_tips ?? 0)) * 100) - $platformFeeCents;
                $deliveryManCents = (int) round(($order->delivery_charge + ($order->dm_tips ?? 0)) * 100);
                $restaurantCents = max(0, $restaurantCents);
                $deliveryManCents = max(0, $deliveryManCents);
                
                $transferGroup = 'order_' . $order->id;
                
                $sessionParams['payment_intent_data'] = [
                    'metadata' => [
                        'order_id' => (string) $order->id,
                        'store_id' => $store ? (string) $store->id : null,
                        'delivery_man_id' => $deliveryMan ? (string) $deliveryMan->id : null,
                        'platform_fee_cents' => (string) $platformFeeCents,
                        'restaurant_cents' => (string) $restaurantCents,
                        'delivery_man_cents' => (string) $deliveryManCents,
                        'transfer_group' => $transferGroup,
                        'order_type' => $order->order_type,
                        'payment_request_id' => (string) $data->id,
                    ],
                    'transfer_group' => $transferGroup,
                ];
            }
        }

        $checkout_session = Session::create($sessionParams);

        return response()->json(['id' => $checkout_session->id]);
    }

    public function success(Request $request)
    {
        Stripe::setApiKey($this->config_values->api_key);
        $session = Session::retrieve($request->get('session_id'));

        if ($session->payment_status == 'paid' && $session->status == 'complete') {

            $this->payment::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'stripe',
                'is_paid' => 1,
                'transaction_id' => $session->payment_intent,
            ]);

            $data = $this->payment::where(['id' => $request['payment_id']])->first();

            if (isset($data) && function_exists($data->success_hook)) {
                call_user_func($data->success_hook, $data);
            }

            return $this->payment_response($data,'success');
        }
        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data,'fail');
    }

    public function canceled(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'cancel');
    }
}
