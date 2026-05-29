import 'package:get/get.dart';
import '../../../../common/widgets/custom_snackbar.dart';
import '../domain/models/document_verification_model.dart';
import '../domain/services/document_verification_service_interface.dart';

class DocumentVerificationController extends GetxController implements GetxService {
  final DocumentVerificationServiceInterface documentVerificationService;

  DocumentVerificationController({required this.documentVerificationService});

  List<DocumentVerificationModel> _documentList = [];
  List<DocumentVerificationModel> get documentList => _documentList;

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  int _pendingCount = 0;
  int get pendingCount => _pendingCount;

  int _approvedCount = 0;
  int get approvedCount => _approvedCount;

  int _rejectedCount = 0;
  int get rejectedCount => _rejectedCount;

  bool _isVerified = false;
  bool get isVerified => _isVerified;

  Future<void> getMyDocuments(String verifiableType, int verifiableId) async {
    _isLoading = true;
    update();
    _documentList = await documentVerificationService.getMyDocuments(verifiableType, verifiableId);
    _isLoading = false;
    update();
  }

  Future<void> checkStatus(String verifiableType, int verifiableId) async {
    Response response = await documentVerificationService.checkStatus(verifiableType, verifiableId);
    if (response.statusCode == 200) {
      _pendingCount = response.body['pending'] ?? 0;
      _approvedCount = response.body['approved'] ?? 0;
      _rejectedCount = response.body['rejected'] ?? 0;
      _isVerified = response.body['is_verified'] ?? false;
      update();
    }
  }

  Future<void> submitDocument(Map<String, dynamic> data) async {
    _isLoading = true;
    update();
    Response response = await documentVerificationService.store(data);
    if (response.statusCode == 200) {
      showCustomSnackBar(response.body['message'], isError: false);
    } else {
      showCustomSnackBar(response.statusText);
    }
    _isLoading = false;
    update();
  }
}
