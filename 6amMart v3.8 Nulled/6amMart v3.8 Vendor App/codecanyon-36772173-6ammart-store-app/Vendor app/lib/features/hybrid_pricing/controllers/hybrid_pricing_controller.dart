import 'package:get/get.dart';
import 'package:nexofood_vendor/api/api_client.dart';

class HybridPricingController extends GetxController implements GetxService {
  final ApiClient apiClient;

  HybridPricingController({required this.apiClient});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  Map<String, dynamic>? _pricingModels;
  Map<String, dynamic>? get pricingModels => _pricingModels;

  Future<void> getMyPricingModels() async {
    _isLoading = true;
    update();
    Response response = await apiClient.getData('/api/v1/vendor/hybrid-pricing/my-models');
    if (response.statusCode == 200) {
      _pricingModels = response.body;
    }
    _isLoading = false;
    update();
  }

  Future<Response> updatePricingModels(Map<String, dynamic> data) async {
    _isLoading = true;
    update();
    Response response = await apiClient.postData('/api/v1/vendor/hybrid-pricing/update-models', data);
    _isLoading = false;
    update();
    return response;
  }

  Future<Response> updateDriverRates(Map<String, dynamic> data) async {
    _isLoading = true;
    update();
    Response response = await apiClient.postData('/api/v1/vendor/hybrid-pricing/update-driver-rates', data);
    _isLoading = false;
    update();
    return response;
  }

  Future<void> simulateEarnings(double orderAmount, {double distance = 0}) async {
    _isLoading = true;
    update();
    Response response = await apiClient.postData('/api/v1/vendor/hybrid-pricing/simulate', {
      'order_amount': orderAmount,
      'distance': distance,
    });
    if (response.statusCode == 200) {
      _pricingModels?['simulation'] = response.body;
    }
    _isLoading = false;
    update();
  }
}
