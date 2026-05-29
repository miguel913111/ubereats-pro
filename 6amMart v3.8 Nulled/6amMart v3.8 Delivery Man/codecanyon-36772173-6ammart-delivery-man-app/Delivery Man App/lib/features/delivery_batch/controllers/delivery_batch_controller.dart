import 'package:get/get.dart';
import 'package:nexofood_delivery/api/api_client.dart';

class DeliveryBatchController extends GetxController implements GetxService {
  final ApiClient apiClient;

  DeliveryBatchController({required this.apiClient});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  Map<String, dynamic>? _batch;
  Map<String, dynamic>? get batch => _batch;

  List<dynamic> _orders = [];
  List<dynamic> get orders => _orders;

  List<dynamic> _routeSegments = [];
  List<dynamic> get routeSegments => _routeSegments;

  Future<void> getMyBatch() async {
    _isLoading = true;
    update();
    Response response = await apiClient.getData('/api/v1/delivery-man/my-batch');
    if (response.statusCode == 200) {
      _batch = response.body['batch'];
      _orders = _batch?['orders'] ?? [];
      _routeSegments = _batch?['route_segments'] ?? [];
    }
    _isLoading = false;
    update();
  }

  Future<Response> startBatch(int batchId) async {
    _isLoading = true;
    update();
    Response response = await apiClient.postData('/api/v1/delivery-man/batch/start', {'batch_id': batchId});
    _isLoading = false;
    update();
    return response;
  }

  Future<Response> deliverOrder(int batchId, int orderId) async {
    _isLoading = true;
    update();
    Response response = await apiClient.postData('/api/v1/delivery-man/batch/deliver-order', {
      'batch_id': batchId,
      'order_id': orderId,
    });
    _isLoading = false;
    update();
    return response;
  }
}
