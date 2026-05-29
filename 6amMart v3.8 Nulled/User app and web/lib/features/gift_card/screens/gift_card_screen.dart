import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../common/widgets/custom_app_bar.dart';
import '../../../common/widgets/custom_button.dart';
import '../../../common/widgets/custom_text_field.dart';
import '../../../helper/auth_helper.dart';
import '../../../util/dimensions.dart';
import '../../../util/styles.dart';
import '../controllers/gift_card_controller.dart';

class GiftCardScreen extends StatefulWidget {
  const GiftCardScreen({Key? key}) : super(key: key);

  @override
  State<GiftCardScreen> createState() => _GiftCardScreenState();
}

class _GiftCardScreenState extends State<GiftCardScreen> {
  final TextEditingController _codeController = TextEditingController();

  @override
  void initState() {
    super.initState();
    if (AuthHelper.isLoggedIn()) {
      Get.find<GiftCardController>().getGiftCardList();
    }
  }

  @override
  void dispose() {
    _codeController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: 'gift_cards'.tr),
      body: GetBuilder<GiftCardController>(
        builder: (giftCardController) {
          return Column(
            children: [
              Expanded(
                child: giftCardController.giftCardList.isEmpty
                    ? Center(child: Text('no_gift_card_available'.tr, style: robotoMedium))
                    : ListView.builder(
                        padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                        itemCount: giftCardController.giftCardList.length,
                        itemBuilder: (context, index) {
                          final giftCard = giftCardController.giftCardList[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
                            child: Padding(
                              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(giftCard.title ?? '', style: robotoBold.copyWith(fontSize: Dimensions.fontSizeLarge)),
                                  const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                                  Text(giftCard.description ?? '', style: robotoRegular.copyWith(color: Theme.of(context).disabledColor)),
                                  const SizedBox(height: Dimensions.paddingSizeSmall),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text('${giftCard.amount} ${"currency".tr}', style: robotoBold.copyWith(color: Theme.of(context).primaryColor)),
                                      CustomButton(
                                        buttonText: 'redeem'.tr,
                                        width: 100,
                                        height: 40,
                                        onPressed: () => giftCardController.redeemGiftCard(giftCard.code ?? ''),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
              ),
              Padding(
                padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                child: Column(
                  children: [
                    CustomTextField(
                      controller: _codeController,
                      hintText: 'enter_gift_card_code'.tr,
                      titleText: 'gift_card_code'.tr,
                      showTitle: true,
                    ),
                    const SizedBox(height: Dimensions.paddingSizeSmall),
                    CustomButton(
                      buttonText: 'apply'.tr,
                      isLoading: giftCardController.isLoading,
                      onPressed: () {
                        if (_codeController.text.trim().isEmpty) {
                          return;
                        }
                        giftCardController.applyGiftCard(_codeController.text.trim());
                      },
                    ),
                  ],
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}
