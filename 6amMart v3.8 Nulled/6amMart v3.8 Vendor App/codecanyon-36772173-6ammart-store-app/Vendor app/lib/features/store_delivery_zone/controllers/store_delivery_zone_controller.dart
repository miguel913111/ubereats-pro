import 'package:get/get.dart';
import 'package:nexofood_vendor/api/api_client.dart';

class StoreDeliveryZoneController extends GetxController implements GetxService {
  final ApiClient apiClient;

  StoreDeliveryZoneController({required this.apiClient});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<dynamic> _zones = [];
  List<dynamic> get zones => _zones;

  Future<void> getZones(int storeId) async {
    _isLoading = true;
    update();
    Response response = await apiClient.getData('/api/v1/customer/store-delivery-zone/list?store_id=$storeId');
    if (response.statusCode == 200) {
      _zones = response.body;
    }
    _isLoading = false;
    update();
  }

  Future<Response> createZone(Map<String, dynamic> data) async {
    _isLoading = true;
    update();
    Response response = await apiClient.postData('/api/v1/vendor/store-delivery-zone/store', data);
    _isLoading = false;
    update();
    return response;
  }
}
