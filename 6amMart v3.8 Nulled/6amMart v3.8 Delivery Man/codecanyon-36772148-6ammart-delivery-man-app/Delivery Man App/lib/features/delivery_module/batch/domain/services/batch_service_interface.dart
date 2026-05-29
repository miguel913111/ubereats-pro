import 'package:nexofood_delivery/features/delivery_module/batch/domain/models/delivery_batch_model.dart';

abstract class BatchServiceInterface {
  Future<DeliveryBatchModel?> getMyBatch();
  Future<DeliveryBatchModel?> getBatchDetails(int batchId);
  Future<bool> startBatch(int batchId);
  Future<bool> deliverOrder(int batchId, int orderId);
  Future<bool> updateSequence(int batchId, List<int> orderIds);
  Future<bool> rejectBatch(int batchId);
  Future<bool> acceptBatch(int batchId);
  Future<bool> completeDeliveryInBatch(int batchId, int orderId);
}
