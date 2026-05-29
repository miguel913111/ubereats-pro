import 'package:get/get.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';

class ShallowRouterHelper {

  static void updateParameter(String key, String value) {
    if(kIsWeb) {
      if (Get.parameters.containsKey(key) && Get.parameters[key] == value) {
        return;
      }
      // Defer navigation until after first frame to ensure Get.context is available
      if (Get.context == null) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          _doNavigate(key, value);
        });
      } else {
        _doNavigate(key, value);
      }
    }
  }

  static void _doNavigate(String key, String value) {
    final newParams = Map<String, String>.from(Get.parameters);
    newParams[key] = value;
    String currentPath = Get.currentRoute.split('?').first;
    String queryString = Uri(queryParameters: newParams).query;
    String newRoute = '$currentPath?$queryString';
    if(Get.currentRoute == newRoute) {
      return;
    }
    Get.offNamed(newRoute, preventDuplicates: true);
  }

}