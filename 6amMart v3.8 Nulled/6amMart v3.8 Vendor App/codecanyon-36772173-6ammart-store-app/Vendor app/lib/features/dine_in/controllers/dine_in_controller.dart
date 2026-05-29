import 'package:get/get.dart';
import 'package:nexofood_vendor/api/api_client.dart';

class DineInController extends GetxController implements GetxService {
  final ApiClient apiClient;

  DineInController({required this.apiClient});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<dynamic> _tables = [];
  List<dynamic> get tables => _tables;

  List<dynamic> _reservations = [];
  List<dynamic> get reservations => _reservations;

  Future<void> getTables(int storeId) async {
    _isLoading = true;
    update();
    Response response = await apiClient.getData('/api/v1/customer/dine-in/tables?store_id=$storeId');
    if (response.statusCode == 200) {
      _tables = response.body;
    }
    _isLoading = false;
    update();
  }

  Future<void> getReservations(int storeId) async {
    _isLoading = true;
    update();
    Response response = await apiClient.getData('/api/v1/vendor/dine-in/reservations?store_id=$storeId');
    if (response.statusCode == 200) {
      _reservations = response.body;
    }
    _isLoading = false;
    update();
  }

  Future<Response> confirmReservation(int id) async {
    return await apiClient.postData('/api/v1/vendor/dine-in/confirm/$id', {});
  }

  Future<Response> cancelReservation(int id, String reason) async {
    return await apiClient.postData('/api/v1/vendor/dine-in/cancel/$id', {'reason': reason});
  }

  Future<Response> checkInReservation(String qrCode) async {
    return await apiClient.postData('/api/v1/customer/dine-in/check-in', {'qr_code': qrCode});
  }
}
