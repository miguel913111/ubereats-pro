import 'package:flutter/material.dart';
import 'package:flutter_stripe/flutter_stripe.dart';
import 'package:get/get.dart';
import 'package:nexofood_user/common/widgets/custom_app_bar.dart';
import 'package:nexofood_user/common/widgets/custom_button.dart';
import 'package:nexofood_user/features/order/domain/models/order_model.dart';
import 'package:nexofood_user/features/payment/controllers/stripe_payment_controller.dart';
import 'package:nexofood_user/features/payment/screens/saved_cards_screen.dart';
import 'package:nexofood_user/helper/route_helper.dart';
import 'package:nexofood_user/util/dimensions.dart';
import 'package:nexofood_user/util/styles.dart';

class StripeNativePaymentScreen extends StatefulWidget {
  final OrderModel orderModel;
  final String? addFundUrl;
  final String? subscriptionUrl;
  final String guestId;
  final String contactNumber;
  final bool createAccount;

  const StripeNativePaymentScreen({
    super.key,
    required this.orderModel,
    this.addFundUrl,
    this.subscriptionUrl,
    required this.guestId,
    required this.contactNumber,
    this.createAccount = false,
  });

  @override
  State<StripeNativePaymentScreen> createState() => _StripeNativePaymentScreenState();
}

class _StripeNativePaymentScreenState extends State<StripeNativePaymentScreen> {
  final StripePaymentController _stripeController = Get.put(StripePaymentController());
  CardFieldInputDetails? _cardDetails;
  bool _isProcessing = false;
  String? _selectedPaymentMethodId;
  bool _useSavedCard = false;

  @override
  void initState() {
    super.initState();
    _initStripe();
  }

  Future<void> _initStripe() async {
    await _stripeController.getSavedCards();
    if (_stripeController.savedCards.isNotEmpty) {
      setState(() {
        _selectedPaymentMethodId = _stripeController.savedCards.first['id'];
        _useSavedCard = true;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: 'pagamento_com_cartão'.tr),
      body: GetBuilder<StripePaymentController>(
        builder: (controller) {
          return SafeArea(
            child: Padding(
              padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Valor do pedido
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
                    decoration: BoxDecoration(
                      color: Theme.of(context).primaryColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                    ),
                    child: Column(
                      children: [
                        Text('total_a_pagar'.tr, style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeDefault)),
                        const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                        Text(
                          '€ ${widget.orderModel.orderAmount?.toStringAsFixed(2) ?? '0.00'}',
                          style: robotoBold.copyWith(fontSize: 28, color: Theme.of(context).primaryColor),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: Dimensions.paddingSizeExtraLarge),

                  // Cartões salvos
                  if (controller.savedCards.isNotEmpty) ...[
                    Text('cartões_salvos'.tr, style: robotoBold.copyWith(fontSize: Dimensions.fontSizeLarge)),
                    const SizedBox(height: Dimensions.paddingSizeSmall),
                    ...controller.savedCards.map((card) {
                      final brand = card['card']?['brand'] ?? 'card';
                      final last4 = card['card']?['last4'] ?? '****';
                      final pmId = card['id'];
                      return _SavedCardTile(
                        brand: brand,
                        last4: last4,
                        pmId: pmId,
                        isSelected: _useSavedCard && _selectedPaymentMethodId == pmId,
                        onTap: () {
                          setState(() {
                            _selectedPaymentMethodId = pmId;
                            _useSavedCard = true;
                          });
                        },
                      );
                    }),
                    _NewCardTile(
                      isSelected: !_useSavedCard,
                      onTap: () => setState(() {
                        _useSavedCard = false;
                        _selectedPaymentMethodId = null;
                      }),
                    ),
                    const SizedBox(height: Dimensions.paddingSizeLarge),
                  ] else ...[
                    Text('dados_do_cartão'.tr, style: robotoBold.copyWith(fontSize: Dimensions.fontSizeLarge)),
                    const SizedBox(height: Dimensions.paddingSizeSmall),
                  ],

                  // CardFormField (apenas se não usar cartão salvo)
                  // Usar CardFormField em vez de CardField — muito mais estável no Android
                  if (!_useSavedCard) ...[
                    const SizedBox(height: Dimensions.paddingSizeDefault),
                    CardFormField(
                      style: CardFormStyle(
                        borderColor: Colors.grey,
                        borderWidth: 1,
                        borderRadius: 8,
                        textColor: Theme.of(context).textTheme.bodyLarge?.color,
                        fontSize: 16,
                        placeholderColor: Colors.grey,
                      ),
                      onCardChanged: (card) {
                        setState(() {
                          _cardDetails = card;
                        });
                      },
                    ),
                    const SizedBox(height: Dimensions.paddingSizeLarge),
                  ],

                  // Salvar cartão (novo) — sempre salva quando é novo
                  if (!_useSavedCard)
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeSmall),
                      child: Row(
                        children: [
                          Icon(Icons.check_circle, color: Theme.of(context).primaryColor, size: 20),
                          const SizedBox(width: Dimensions.paddingSizeSmall),
                          Expanded(
                            child: Text(
                              'salvar_cartão_para_futuro'.tr,
                              style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall),
                            ),
                          ),
                        ],
                      ),
                    ),

                  const Spacer(),

                  // Erro
                  if (controller.errorMessage != null)
                    Padding(
                      padding: const EdgeInsets.only(bottom: Dimensions.paddingSizeDefault),
                      child: Text(
                        controller.errorMessage!,
                        style: robotoRegular.copyWith(color: Colors.red),
                        textAlign: TextAlign.center,
                      ),
                    ),

                  // Botão pagar
                  CustomButton(
                    buttonText: _isProcessing
                        ? 'processando...'.tr
                        : '${'pagar_€'.tr}${widget.orderModel.orderAmount?.toStringAsFixed(2) ?? '0.00'}',
                    isLoading: controller.isLoading || _isProcessing,
                    onPressed: _isProcessing || (_useSavedCard && _selectedPaymentMethodId == null)
                        ? null
                        : () => _pay(),
                  ),
                  const SizedBox(height: Dimensions.paddingSizeDefault),

                  // Botão gerenciar cartões
                  if (controller.savedCards.isNotEmpty)
                    TextButton(
                      onPressed: () => Get.to(() => const SavedCardsScreen()),
                      child: Text('gerenciar_cartões'.tr, style: robotoMedium.copyWith(color: Theme.of(context).primaryColor)),
                    ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Future<void> _pay() async {
    setState(() => _isProcessing = true);
    _stripeController.clearError();

    try {
      if (_useSavedCard && _selectedPaymentMethodId != null) {
        await _payWithSavedCard(_selectedPaymentMethodId!);
      } else {
        await _payWithNewCard();
      }
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  Future<void> _payWithSavedCard(String paymentMethodId) async {
    final result = await _stripeController.createPaymentIntent(
      orderId: widget.orderModel.id!,
      paymentMethodId: paymentMethodId,
    );

    if (result == null || result['client_secret'] == null) {
      return;
    }

    // PaymentIntent já tem payment_method definido pelo backend
    final paymentIntentResult = await Stripe.instance.confirmPayment(
      paymentIntentClientSecret: result['client_secret']!,
    );

    if (paymentIntentResult.status == PaymentIntentsStatus.Succeeded) {
      _onPaymentSuccess();
    } else {
      _stripeController.setError('Pagamento não confirmado: ${paymentIntentResult.status}');
    }
  }

  Future<void> _payWithNewCard() async {
    // 1. Criar SetupIntent para salvar o cartão
    final setupOk = await _stripeController.createSetupIntent();
    if (!setupOk || _stripeController.setupIntentClientSecret == null) {
      return;
    }

    // 2. Confirmar SetupIntent (salva o cartão no Stripe)
    final setupResult = await Stripe.instance.confirmSetupIntent(
      paymentIntentClientSecret: _stripeController.setupIntentClientSecret!,
      params: PaymentMethodParams.card(
        paymentMethodData: PaymentMethodData(
          billingDetails: BillingDetails(),
        ),
      ),
    );

    if (setupResult.status != 'Succeeded' || setupResult.paymentMethodId == null) {
      _stripeController.setError('Erro ao salvar cartão: ${setupResult.status}');
      return;
    }

    // 3. Criar PaymentIntent para cobrar
    final paymentResult = await _stripeController.createPaymentIntent(
      orderId: widget.orderModel.id!,
      paymentMethodId: setupResult.paymentMethodId,
    );

    if (paymentResult == null || paymentResult['client_secret'] == null) {
      return;
    }

    // 4. Confirmar pagamento (PaymentIntent já tem payment_method)
    final paymentIntentResult = await Stripe.instance.confirmPayment(
      paymentIntentClientSecret: paymentResult['client_secret']!,
    );

    if (paymentIntentResult.status == PaymentIntentsStatus.Succeeded) {
      _onPaymentSuccess();
    } else {
      _stripeController.setError('Pagamento não confirmado: ${paymentIntentResult.status}');
    }
  }

  void _onPaymentSuccess() {
    Get.offNamed(
      RouteHelper.getOrderSuccessRoute(
        widget.orderModel.id.toString(),
        widget.contactNumber,
        createAccount: widget.createAccount,
      ),
    );
  }
}

// ── Widgets auxiliares ────────────────────────────────────────────────────

class _SavedCardTile extends StatelessWidget {
  final String brand;
  final String last4;
  final String pmId;
  final bool isSelected;
  final VoidCallback onTap;

  const _SavedCardTile({
    required this.brand,
    required this.last4,
    required this.pmId,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      child: Container(
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
        decoration: BoxDecoration(
          border: Border.all(
            color: isSelected ? Theme.of(context).primaryColor : Theme.of(context).disabledColor.withValues(alpha: 0.3),
            width: isSelected ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          color: isSelected ? Theme.of(context).primaryColor.withValues(alpha: 0.05) : null,
        ),
        child: Row(
          children: [
            Icon(
              isSelected ? Icons.radio_button_checked : Icons.radio_button_unchecked,
              color: isSelected ? Theme.of(context).primaryColor : Colors.grey,
            ),
            const SizedBox(width: Dimensions.paddingSizeDefault),
            Icon(Icons.credit_card, color: Theme.of(context).primaryColor),
            const SizedBox(width: Dimensions.paddingSizeDefault),
            Expanded(
              child: Text('$brand •••• $last4', style: robotoMedium),
            ),
          ],
        ),
      ),
    );
  }
}

class _NewCardTile extends StatelessWidget {
  final bool isSelected;
  final VoidCallback onTap;

  const _NewCardTile({required this.isSelected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      child: Container(
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
        decoration: BoxDecoration(
          border: Border.all(
            color: isSelected ? Theme.of(context).primaryColor : Theme.of(context).disabledColor.withValues(alpha: 0.3),
            width: isSelected ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          color: isSelected ? Theme.of(context).primaryColor.withValues(alpha: 0.05) : null,
        ),
        child: Row(
          children: [
            Icon(
              isSelected ? Icons.radio_button_checked : Icons.radio_button_unchecked,
              color: isSelected ? Theme.of(context).primaryColor : Colors.grey,
            ),
            const SizedBox(width: Dimensions.paddingSizeDefault),
            const Icon(Icons.add_card, color: Colors.grey),
            const SizedBox(width: Dimensions.paddingSizeDefault),
            Expanded(
              child: Text('usar_novo_cartão'.tr, style: robotoMedium),
            ),
          ],
        ),
      ),
    );
  }
}
