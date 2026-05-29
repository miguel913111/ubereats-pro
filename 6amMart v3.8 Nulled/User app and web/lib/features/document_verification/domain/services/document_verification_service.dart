import '../models/document_verification_model.dart';
import '../repositories/document_verification_repository_interface.dart';
import 'document_verification_service_interface.dart';

class DocumentVerificationService implements DocumentVerificationServiceInterface {
  final DocumentVerificationRepositoryInterface documentVerificationRepository;

  DocumentVerificationService({required this.documentVerificationRepository});

  @override
  Future<dynamic> store(Map<String, dynamic> data) async {
    return await documentVerificationRepository.store(data);
  }

  @override
  Future<List<DocumentVerificationModel>> getMyDocuments(String verifiableType, int verifiableId) async {
    return await documentVerificationRepository.getMyDocuments(verifiableType, verifiableId);
  }

  @override
  Future<dynamic> checkStatus(String verifiableType, int verifiableId) async {
    return await documentVerificationRepository.checkStatus(verifiableType, verifiableId);
  }
}
