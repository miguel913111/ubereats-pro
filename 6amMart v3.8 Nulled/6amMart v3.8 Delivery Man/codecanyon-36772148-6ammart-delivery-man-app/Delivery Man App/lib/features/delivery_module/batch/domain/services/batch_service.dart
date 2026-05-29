import 'package:nexofood_delivery/features/delivery_module/batch/domain/models/delivery_batch_model.dart';
import 'package:nexofood_delivery/features/delivery_module/batch/domain/repositories/batch_repository_interface.dart';
import 'package:nexofood_delivery/features/delivery_module/batch/domain/services/batch_service_interface.dart';

class BatchService implements BatchServiceInterface {
  final BatchRepositoryInterface batchRepositoryInterface;

  BatchService({required this.batchRepositoryInterface});

  @override
  Future<DeliveryBatchModel?> getMyBatch() async {
    return await batchRepositoryInterface.getMyBatch();
  }

  @override
  Future<DeliveryBatchModel?> getBatchDetails(int batchId) async {
    return await batchRepositoryInterface.getBatchDetails(batchId);
  }

  @override
  Future<bool> startBatch(int batchId) async {
    return await batchRepositoryInterface.startBatch(batchId);
  }

  @override
  Future<bool> deliverOrder(int batchId, int orderId) async {
    return await batchRepositoryInterface.deliverOrder(batchId, orderId);
  }

  @override
  Future<bool> updateSequence(int batchId, List<int> orderIds) async {
    return await batchRepositoryInterface.updateSequence(batchId, orderIds);
  }

  @override
  Future<bool> rejectBatch(int batchId) async {
    return await batchRepositoryInterface.rejectBatch(batchId);
  }

  @override
  Future<bool> acceptBatch(int batchId) async {
    return await batchRepositoryInterface.acceptBatch(batchId);
  }

  @override
  Future<bool> completeDeliveryInBatch(int batchId, int orderId) async {
    return await batchRepositoryInterface.completeDeliveryInBatch(batchId, orderId);
  }
}
