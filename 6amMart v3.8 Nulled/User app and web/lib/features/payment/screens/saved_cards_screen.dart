import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:nexofood_user/common/widgets/custom_app_bar.dart';

import 'package:nexofood_user/features/payment/controllers/stripe_payment_controller.dart';
import 'package:nexofood_user/util/dimensions.dart';
import 'package:nexofood_user/util/styles.dart';

class SavedCardsScreen extends StatefulWidget {
  const SavedCardsScreen({super.key});

  @override
  State<SavedCardsScreen> createState() => _SavedCardsScreenState();
}

class _SavedCardsScreenState extends State<SavedCardsScreen> {
  final StripePaymentController _stripeController = Get.find<StripePaymentController>();

  @override
  void initState() {
    super.initState();
    _stripeController.getSavedCards();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: 'meus_cartões'.tr),
      body: GetBuilder<StripePaymentController>(
        builder: (controller) {
          if (controller.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          if (controller.savedCards.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.credit_card_off, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: Dimensions.paddingSizeDefault),
                  Text('nenhum_cartão_salvo'.tr, style: robotoMedium.copyWith(color: Colors.grey)),
                ],
              ),
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
            itemCount: controller.savedCards.length,
            itemBuilder: (context, index) {
              final card = controller.savedCards[index];
              final brand = card['card']?['brand'] ?? 'Cartão';
              final last4 = card['card']?['last4'] ?? '****';
              final expMonth = card['card']?['exp_month']?.toString() ?? '**';
              final expYear = card['card']?['exp_year']?.toString() ?? '****';
              final pmId = card['id'];

              return Card(
                margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeDefault),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                ),
                child: ListTile(
                  contentPadding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                  leading: Container(
                    padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                    decoration: BoxDecoration(
                      color: Theme.of(context).primaryColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                    ),
                    child: Icon(Icons.credit_card, color: Theme.of(context).primaryColor),
                  ),
                  title: Text('$brand •••• $last4', style: robotoBold),
                  subtitle: Text('Expira $expMonth/$expYear', style: robotoRegular.copyWith(color: Colors.grey)),
                  trailing: IconButton(
                    icon: const Icon(Icons.delete_outline, color: Colors.red),
                    onPressed: () => _confirmDelete(pmId),
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }

  void _confirmDelete(String paymentMethodId) {
    Get.dialog(
      AlertDialog(
        title: Text('remover_cartão'.tr, style: robotoBold),
        content: Text('tem_certeza_remover_cartão'.tr, style: robotoRegular),
        actions: [
          TextButton(
            onPressed: () => Get.back(),
            child: Text('cancelar'.tr),
          ),
          TextButton(
            onPressed: () async {
              Get.back();
              final success = await _stripeController.removeCard(paymentMethodId);
              if (success) {
                Get.snackbar('sucesso'.tr, 'cartão_removido'.tr, snackPosition: SnackPosition.BOTTOM);
              } else {
                Get.snackbar('erro'.tr, _stripeController.errorMessage ?? 'Erro ao remover', snackPosition: SnackPosition.BOTTOM);
              }
            },
            child: Text('remover'.tr, style: const TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
  }
}
