import 'package:get/get.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:nexofood_delivery/api/api_client.dart';
import 'package:nexofood_delivery/features/delivery_module/batch/domain/models/delivery_batch_model.dart';
import 'package:nexofood_delivery/features/delivery_module/batch/domain/repositories/batch_repository_interface.dart';
import 'package:nexofood_delivery/util/app_constants.dart';

class BatchRepository implements BatchRepositoryInterface {
  final ApiClient apiClient;
  final SharedPreferences sharedPreferences;

  BatchRepository({required this.apiClient, required this.sharedPreferences});

  String _getUserToken() {
    return sharedPreferences.getString(AppConstants.token) ?? "";
  }

  @override
  Future<DeliveryBatchModel?> getMyBatch() async {
    DeliveryBatchModel? batch;
    Response response = await apiClient.getData(
      '${AppConstants.myBatchUri}?token=${_getUserToken()}',
    );
    if (response.statusCode == 200 && response.body != null && response.body['batch'] != null) {
      batch = DeliveryBatchModel.fromJson(response.body['batch']);
    }
    return batch;
  }

  @override
  Future<DeliveryBatchModel?> getBatchDetails(int batchId) async {
    DeliveryBatchModel? batch;
    Response response = await apiClient.getData(
      '${AppConstants.batchDetailsUri}?token=${_getUserToken()}&batch_id=$batchId',
    );
    if (response.statusCode == 200 && response.body != null && response.body['batch'] != null) {
      batch = DeliveryBatchModel.fromJson(response.body['batch']);
    }
    return batch;
  }

  @override
  Future<bool> startBatch(int batchId) async {
    Response response = await apiClient.postData(
      AppConstants.startBatchUri,
      {
        'token': _getUserToken(),
        'batch_id': batchId,
      },
      handleError: false,
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> deliverOrder(int batchId, int orderId) async {
    Response response = await apiClient.postData(
      AppConstants.deliverOrderUri,
      {
        'token': _getUserToken(),
        'batch_id': batchId,
        'order_id': orderId,
      },
      handleError: false,
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> updateSequence(int batchId, List<int> orderIds) async {
    Response response = await apiClient.postData(
      AppConstants.updateSequenceUri,
      {
        'token': _getUserToken(),
        'batch_id': batchId,
        'order_ids': orderIds,
      },
      handleError: false,
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> rejectBatch(int batchId) async {
    Response response = await apiClient.postData(
      AppConstants.rejectBatchUri,
      {
        'token': _getUserToken(),
        'batch_id': batchId,
      },
      handleError: false,
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> acceptBatch(int batchId) async {
    Response response = await apiClient.postData(
      AppConstants.acceptBatchUri,
      {
        'token': _getUserToken(),
        'batch_id': batchId,
      },
      handleError: false,
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> completeDeliveryInBatch(int batchId, int orderId) async {
    Response response = await apiClient.postData(
      AppConstants.completeDeliveryUri,
      {
        'token': _getUserToken(),
        'batch_id': batchId,
        'order_id': orderId,
      },
      handleError: false,
    );
    return response.statusCode == 200;
  }
}
