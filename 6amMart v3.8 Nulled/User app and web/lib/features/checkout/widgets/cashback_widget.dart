import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../util/dimensions.dart';
import '../../../util/styles.dart';
import '../../home/controllers/home_controller.dart';

class CashbackWidget extends StatelessWidget {
  final double amount;
  const CashbackWidget({Key? key, required this.amount}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return GetBuilder<HomeController>(
      builder: (homeController) {
        if (homeController.cashBackData == null || homeController.cashBackData!.cashbackAmount == 0) {
          return const SizedBox.shrink();
        }

        final cashBack = homeController.cashBackData!;
        final isPercent = cashBack.cashbackType == 'percent';

        return Container(
          margin: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeSmall),
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          decoration: BoxDecoration(
            color: Colors.green.withOpacity(0.1),
            borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
            border: Border.all(color: Colors.green.withOpacity(0.3)),
          ),
          child: Row(
            children: [
              Icon(Icons.account_balance_wallet, color: Colors.green, size: 24),
              const SizedBox(width: Dimensions.paddingSizeSmall),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'cashback_reward'.tr,
                      style: robotoBold.copyWith(color: Colors.green),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      isPercent
                          ? '${cashBack.cashbackAmount}% ${'cashback_on_this_order'.tr}'
                          : '${PriceConverter.convertPrice(cashBack.cashbackAmount)} ${'cashback_on_this_order'.tr}',
                      style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall),
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
