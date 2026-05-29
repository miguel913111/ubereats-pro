import 'package:get/get.dart';
import 'package:nexofood_vendor/api/api_client.dart';

class DocumentVerificationController extends GetxController implements GetxService {
  final ApiClient apiClient;

  DocumentVerificationController({required this.apiClient});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  Future<Response> submitDocument(Map<String, dynamic> data) async {
    _isLoading = true;
    update();
    Response response = await apiClient.postData('/api/v1/customer/document-verification/store', data);
    _isLoading = false;
    update();
    return response;
  }

  Future<Response> getMyDocuments(int storeId) async {
    return await apiClient.getData('/api/v1/customer/document-verification/my-documents?verifiable_type=App\\Models\\Store&verifiable_id=$storeId');
  }

  Future<Response> checkStatus(int storeId) async {
    return await apiClient.getData('/api/v1/customer/document-verification/check-status?verifiable_type=App\\Models\\Store&verifiable_id=$storeId');
  }
}
