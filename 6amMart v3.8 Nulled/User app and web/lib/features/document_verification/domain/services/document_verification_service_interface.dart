import '../models/document_verification_model.dart';

abstract class DocumentVerificationServiceInterface {
  Future<dynamic> store(Map<String, dynamic> data);
  Future<List<DocumentVerificationModel>> getMyDocuments(String verifiableType, int verifiableId);
  Future<dynamic> checkStatus(String verifiableType, int verifiableId);
}
