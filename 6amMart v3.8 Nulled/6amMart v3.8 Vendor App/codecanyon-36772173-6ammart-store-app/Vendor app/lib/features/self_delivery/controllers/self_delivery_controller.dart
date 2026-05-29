import 'package:get/get.dart';
import 'package:nexofood_vendor/api/api_client.dart';

class SelfDeliveryController extends GetxController implements GetxService {
  final ApiClient apiClient;

  SelfDeliveryController({required this.apiClient});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<dynamic> _deliveryMen = [];
  List<dynamic> get deliveryMen => _deliveryMen;

  Future<void> getDeliveryMen() async {
    _isLoading = true;
    update();
    Response response = await apiClient.getData('/api/v1/vendor/self-delivery/delivery-men');
    if (response.statusCode == 200) {
      _deliveryMen = response.body;
    }
    _isLoading = false;
    update();
  }

  Future<Response> createDeliveryMan(Map<String, dynamic> data) async {
    _isLoading = true;
    update();
    Response response = await apiClient.postData('/api/v1/vendor/self-delivery/delivery-men/store', data);
    _isLoading = false;
    update();
    return response;
  }

  Future<Response> assignOrder(int orderId, int deliveryManId) async {
    return await apiClient.postData('/api/v1/vendor/self-delivery/assign-order', {
      'order_id': orderId,
      'store_delivery_man_id': deliveryManId,
    });
  }

  Future<Response> deleteDeliveryMan(int id) async {
    return await apiClient.deleteData('/api/v1/vendor/self-delivery/delivery-men/delete/$id');
  }
}
