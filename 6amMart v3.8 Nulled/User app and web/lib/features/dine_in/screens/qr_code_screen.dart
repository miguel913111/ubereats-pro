import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../util/dimensions.dart';
import '../../../util/styles.dart';
import '../controllers/dine_in_controller.dart';

class QrCodeScreen extends StatelessWidget {
  final int reservationId;
  const QrCodeScreen({Key? key, required this.reservationId}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('reservation_qr'.tr)),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                boxShadow: [BoxShadow(color: Colors.grey.withOpacity(0.2), spreadRadius: 2, blurRadius: 10)],
              ),
              child: Column(
                children: [
                  Icon(Icons.qr_code, size: 200, color: Theme.of(context).primaryColor),
                  const SizedBox(height: Dimensions.paddingSizeDefault),
                  Text(
                    'show_qr_to_restaurant'.tr,
                    style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeLarge),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: Dimensions.paddingSizeSmall),
                  Text(
                    'reservation_id'.tr + ': #$reservationId',
                    style: robotoRegular.copyWith(color: Theme.of(context).disabledColor),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
