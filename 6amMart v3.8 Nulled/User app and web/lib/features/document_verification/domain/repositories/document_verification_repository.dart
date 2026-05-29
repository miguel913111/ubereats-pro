import 'package:shared_preferences/shared_preferences.dart';
import '../../../../api/api_client.dart';
import '../models/document_verification_model.dart';
import 'document_verification_repository_interface.dart';

class DocumentVerificationRepository implements DocumentVerificationRepositoryInterface {
  final ApiClient apiClient;
  final SharedPreferences sharedPreferences;

  DocumentVerificationRepository({required this.apiClient, required this.sharedPreferences});

  @override
  Future<dynamic> store(Map<String, dynamic> data) async {
    return await apiClient.postData('/api/v1/customer/document-verification/store', data);
  }

  @override
  Future<List<DocumentVerificationModel>> getMyDocuments(String verifiableType, int verifiableId) async {
    final response = await apiClient.getData('/api/v1/customer/document-verification/my-documents?verifiable_type=$verifiableType&verifiable_id=$verifiableId');
    if (response.statusCode == 200) {
      List<dynamic> data = response.body;
      return data.map((json) => DocumentVerificationModel.fromJson(json)).toList();
    }
    return [];
  }

  @override
  Future<dynamic> checkStatus(String verifiableType, int verifiableId) async {
    return await apiClient.getData('/api/v1/customer/document-verification/check-status?verifiable_type=$verifiableType&verifiable_id=$verifiableId');
  }
}
