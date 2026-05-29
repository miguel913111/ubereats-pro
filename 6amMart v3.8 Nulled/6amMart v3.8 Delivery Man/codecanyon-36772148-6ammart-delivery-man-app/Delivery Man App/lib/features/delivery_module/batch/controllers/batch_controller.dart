import 'package:flutter/material.dart';
import 'package:nexofood_delivery/common/widgets/custom_snackbar_widget.dart';
import 'package:nexofood_delivery/features/delivery_module/batch/domain/models/delivery_batch_model.dart';
import 'package:nexofood_delivery/features/delivery_module/batch/domain/services/batch_service_interface.dart';
import 'package:get/get.dart';

class BatchController extends GetxController implements GetxService {
  final BatchServiceInterface batchServiceInterface;
  BatchController({required this.batchServiceInterface});

  DeliveryBatchModel? _myBatch;
  DeliveryBatchModel? get myBatch => _myBatch;

  DeliveryBatchModel? _selectedBatch;
  DeliveryBatchModel? get selectedBatch => _selectedBatch;

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  bool _isActionLoading = false;
  bool get isActionLoading => _isActionLoading;

  Future<void> getMyBatch() async {
    _isLoading = true;
    update();
    DeliveryBatchModel? batch = await batchServiceInterface.getMyBatch();
    _myBatch = batch;
    _isLoading = false;
    update();
  }

  Future<void> getBatchDetails(int batchId) async {
    _isLoading = true;
    update();
    DeliveryBatchModel? batch = await batchServiceInterface.getBatchDetails(batchId);
    _selectedBatch = batch;
    _isLoading = false;
    update();
  }

  Future<void> startBatch(int batchId) async {
    _isActionLoading = true;
    update();
    bool success = await batchServiceInterface.startBatch(batchId);
    if (success) {
      showCustomSnackBar('batch_started_successfully'.tr, isError: false);
      await getMyBatch();
    } else {
      showCustomSnackBar('failed_to_start_batch'.tr);
    }
    _isActionLoading = false;
    update();
  }

  Future<void> deliverOrder(int batchId, int orderId) async {
    _isActionLoading = true;
    update();
    bool success = await batchServiceInterface.deliverOrder(batchId, orderId);
    if (success) {
      showCustomSnackBar('delivery_completed_successfully'.tr, isError: false);
      await getMyBatch();
    } else {
      showCustomSnackBar('failed_to_complete_delivery'.tr);
    }
    _isActionLoading = false;
    update();
  }

  Future<void> updateSequence(int batchId, List<int> orderIds) async {
    _isActionLoading = true;
    update();
    bool success = await batchServiceInterface.updateSequence(batchId, orderIds);
    if (!success) {
      showCustomSnackBar('failed_to_update_sequence'.tr);
    }
    _isActionLoading = false;
    update();
  }

  Future<void> rejectBatch(int batchId) async {
    _isActionLoading = true;
    update();
    bool success = await batchServiceInterface.rejectBatch(batchId);
    if (success) {
      showCustomSnackBar('batch_rejected'.tr, isError: false);
      _selectedBatch = null;
    } else {
      showCustomSnackBar('failed_to_reject_batch'.tr);
    }
    _isActionLoading = false;
    update();
  }

  Future<void> acceptBatch(int batchId) async {
    _isActionLoading = true;
    update();
    bool success = await batchServiceInterface.acceptBatch(batchId);
    if (success) {
      showCustomSnackBar('batch_accepted'.tr, isError: false);
      await getBatchDetails(batchId);
    } else {
      showCustomSnackBar('failed_to_accept_batch'.tr);
    }
    _isActionLoading = false;
    update();
  }

  Future<void> completeDeliveryInBatch(int batchId, int orderId) async {
    _isActionLoading = true;
    update();
    bool success = await batchServiceInterface.completeDeliveryInBatch(batchId, orderId);
    if (success) {
      showCustomSnackBar('delivery_completed_successfully'.tr, isError: false);
      await getBatchDetails(batchId);
    } else {
      showCustomSnackBar('failed_to_complete_delivery'.tr);
    }
    _isActionLoading = false;
    update();
  }
}
