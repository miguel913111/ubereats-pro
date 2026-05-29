import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:get/get.dart';
import 'dart:ui' as ui;

class MarkerHelper {
  /// Returns a Widget (Image) for use as a marker icon in flutter_map.
  static Future<Widget> convertAssetToBitmapDescriptor({
    required final String imagePath,
    final int? width,
    final int? height,
  }) async {
    try {
      if (GetPlatform.isWeb) {
        return Image.asset(imagePath, width: width?.toDouble() ?? 50, height: height?.toDouble() ?? 50);
      }
      final ByteData byteDataFromImage = await rootBundle.load(imagePath).timeout(const Duration(seconds: 8));
      final ui.Codec codec = await ui
          .instantiateImageCodec(byteDataFromImage.buffer.asUint8List(), targetHeight: height, targetWidth: width)
          .timeout(const Duration(seconds: 8));
      final ui.FrameInfo frameInfo = await codec.getNextFrame().timeout(const Duration(seconds: 8));
      final ByteData? byteDataFromFrame =
          await frameInfo.image.toByteData(format: ui.ImageByteFormat.png).timeout(const Duration(seconds: 8));
      if (byteDataFromFrame != null) {
        return Image.memory(byteDataFromFrame.buffer.asUint8List(), width: width?.toDouble(), height: height?.toDouble());
      }
    } catch (_) {}
    return Icon(Icons.location_on, size: (width ?? 50).toDouble(), color: Colors.red);
  }
}
