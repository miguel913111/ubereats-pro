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
                  ],

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
                    buttonText: controller.isLoading
                        ? 'processando...'.tr
                        : '${'pagar_€'.tr}${widget.orderModel.orderAmount?.toStringAsFixed(2) ?? '0.00'}',
                    isLoading: controller.isLoading,
                    onPressed: controller.isLoading ? null : () => _pay(),
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
    _stripeController.clearError();

    if (_useSavedCard && _selectedPaymentMethodId != null) {
      // Pagar com cartão salvo usando PaymentSheet
      await _payWithPaymentSheet(paymentMethodId: _selectedPaymentMethodId);
    } else {
      // Pagar com novo cartão usando PaymentSheet
      await _payWithPaymentSheet();
    }
  }

  Future<void> _payWithPaymentSheet({String? paymentMethodId}) async {
    try {
      debugPrint('🟡 STEP 1: Criando PaymentIntent... orderId=${widget.orderModel.id}');
      
      // 1. Criar PaymentIntent no backend
      final result = await _stripeController.createPaymentIntent(
        orderId: widget.orderModel.id!,
        paymentMethodId: paymentMethodId,
      );

      debugPrint('🟡 STEP 2: Resultado do backend: $result');

      if (result == null || result['client_secret'] == null) {
        debugPrint('🔴 STEP 2 FAILED: result=null ou sem client_secret');
        return; // erro já setado no controller
      }

      final clientSecret = result['client_secret']!;
      final customerId = result['customer_id'];
      
      debugPrint('🟡 STEP 3: Init PaymentSheet... clientSecret=${clientSecret.substring(0, 20)}...');

      // 2. Inicializar PaymentSheet
      await Stripe.instance.initPaymentSheet(
        paymentSheetParameters: SetupPaymentSheetParameters(
          paymentIntentClientSecret: clientSecret,
          merchantDisplayName: 'Nexo Food',
          customerId: customerId,
          style: ThemeMode.system,
          billingDetails: const BillingDetails(),
        ),
      );

      debugPrint('🟡 STEP 4: Present PaymentSheet...');

      // 3. Apresentar PaymentSheet
      await Stripe.instance.presentPaymentSheet();

      debugPrint('🟢 STEP 5: Pagamento confirmado!');

      // 4. Pagamento confirmado!
      _onPaymentSuccess();

    } on StripeException catch (e) {
      debugPrint('🔴 StripeException: code=${e.error.code}, message=${e.error.localizedMessage}');
      if (e.error.code == FailureCode.Canceled) {
        return;
      }
      _stripeController.setError('Erro no pagamento: ${e.error.localizedMessage}');
    } catch (e, stack) {
      debugPrint('🔴 Erro inesperado: $e');
      debugPrint('🔴 Stack: $stack');
      _stripeController.setError('Erro inesperado: $e');
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
