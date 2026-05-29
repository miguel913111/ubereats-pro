import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../../common/widgets/custom_snackbar_widget.dart';
import '../controllers/dine_in_controller.dart';

class QrScannerScreen extends StatefulWidget {
  const QrScannerScreen({Key? key}) : super(key: key);

  @override
  State<QrScannerScreen> createState() => _QrScannerScreenState();
}

class _QrScannerScreenState extends State<QrScannerScreen> {
  bool _isProcessing = false;

  void _onDetect(BarcodeCapture capture) async {
    if (_isProcessing) return;
    final barcode = capture.barcodes.first;
    final String? code = barcode.rawValue;
    if (code == null || code.isEmpty) return;

    _isProcessing = true;
    try {
      final dineInController = Get.find<DineInController>();
      Response response = await dineInController.checkInReservation(code);
      if (response.statusCode == 200) {
        showCustomSnackBar(response.body['message'] ?? 'Check-in successful', isError: false);
        Get.back(result: true);
      } else {
        showCustomSnackBar(response.statusText ?? 'Invalid QR code');
      }
    } catch (e) {
      showCustomSnackBar('Error processing QR code: $e');
    } finally {
      _isProcessing = false;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('scan_qr_code'.tr)),
      body: MobileScanner(
        onDetect: _onDetect,
      ),
    );
  }
}
