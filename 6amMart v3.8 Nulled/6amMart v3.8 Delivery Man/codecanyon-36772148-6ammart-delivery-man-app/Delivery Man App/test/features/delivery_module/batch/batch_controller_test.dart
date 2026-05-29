import 'package:flutter_test/flutter_test.dart';
import 'package:get/get.dart';
import 'package:nexofood_delivery/features/delivery_module/batch/controllers/batch_controller.dart';
import 'package:nexofood_delivery/features/delivery_module/batch/domain/models/delivery_batch_model.dart';
import 'package:nexofood_delivery/features/delivery_module/batch/domain/services/batch_service_interface.dart';

class MockBatchService implements BatchServiceInterface {
  @override
  Future<DeliveryBatchModel?> getMyBatch() async {
    return DeliveryBatchModel(
      id: 1,
      status: 'pending',
      totalOrders: 2,
      totalDistanceKm: 5.0,
      estimatedDurationMin: 15.0,
    );
  }

  @override
  Future<DeliveryBatchModel?> getBatchDetails(int batchId) async {
    return DeliveryBatchModel(
      id: batchId,
      status: 'active',
      totalOrders: 3,
      orders: [
        BatchOrderModel(orderId: 101, sequence: 1, customerName: 'Alice'),
        BatchOrderModel(orderId: 102, sequence: 2, customerName: 'Bob'),
      ],
    );
  }

  @override
  Future<bool> startBatch(int batchId) async => true;

  @override
  Future<bool> deliverOrder(int batchId, int orderId) async => true;

  @override
  Future<bool> updateSequence(int batchId, List<int> orderIds) async => true;

  @override
  Future<bool> rejectBatch(int batchId) async => true;

  @override
  Future<bool> acceptBatch(int batchId) async => true;

  @override
  Future<bool> completeDeliveryInBatch(int batchId, int orderId) async => true;
}

void main() {
  setUp(() {
    Get.reset();
  });

  test('BatchController initializes with null batch', () {
    final controller = BatchController(batchServiceInterface: MockBatchService());
    expect(controller.myBatch, isNull);
    expect(controller.selectedBatch, isNull);
    expect(controller.isLoading, false);
  });

  test('BatchController getMyBatch loads batch', () async {
    final controller = BatchController(batchServiceInterface: MockBatchService());
    await controller.getMyBatch();
    expect(controller.myBatch, isNotNull);
    expect(controller.myBatch!.id, 1);
    expect(controller.myBatch!.status, 'pending');
  });

  test('BatchController getBatchDetails loads selected batch', () async {
    final controller = BatchController(batchServiceInterface: MockBatchService());
    await controller.getBatchDetails(5);
    expect(controller.selectedBatch, isNotNull);
    expect(controller.selectedBatch!.id, 5);
    expect(controller.selectedBatch!.orders, isNotNull);
    expect(controller.selectedBatch!.orders!.length, 2);
  });
}
