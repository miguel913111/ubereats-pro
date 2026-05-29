import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:nexofood_delivery/common/widgets/custom_app_bar_widget.dart';
import 'package:nexofood_delivery/common/widgets/custom_button_widget.dart';
import 'package:nexofood_delivery/features/delivery_module/batch/controllers/batch_controller.dart';
import 'package:nexofood_delivery/features/delivery_module/batch/domain/models/delivery_batch_model.dart';
import 'package:nexofood_delivery/helper/route_helper.dart';
import 'package:nexofood_delivery/util/dimensions.dart';
import 'package:nexofood_delivery/util/styles.dart';

class BatchDetailsScreen extends StatefulWidget {
  final int batchId;
  const BatchDetailsScreen({super.key, required this.batchId});

  @override
  State<BatchDetailsScreen> createState() => _BatchDetailsScreenState();
}

class _BatchDetailsScreenState extends State<BatchDetailsScreen> {
  @override
  void initState() {
    super.initState();
    Get.find<BatchController>().getBatchDetails(widget.batchId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).cardColor,
      appBar: CustomAppBarWidget(
        title: '${'batch'.tr} #${widget.batchId}',
        isBackButtonExist: true,
      ),
      body: GetBuilder<BatchController>(builder: (batchController) {
        if (batchController.isLoading && batchController.selectedBatch == null) {
          return const Center(child: CircularProgressIndicator());
        }
        final batch = batchController.selectedBatch;
        if (batch == null) {
          return Center(child: Text('batch_not_found'.tr));
        }

        return Column(
          children: [
            Expanded(
              child: RefreshIndicator(
                onRefresh: () async => await batchController.getBatchDetails(widget.batchId),
                child: SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _BatchInfoHeader(batch: batch),
                      const SizedBox(height: Dimensions.paddingSizeDefault),
                      Text('delivery_sequence'.tr, style: robotoBold.copyWith(fontSize: Dimensions.fontSizeLarge)),
                      const SizedBox(height: Dimensions.paddingSizeSmall),
                      if (batch.orders != null)
                        ReorderableListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: batch.orders!.length,
                          onReorder: (oldIndex, newIndex) {
                            if (batch.status == 'active' || batch.status == 'pending') {
                              if (newIndex > oldIndex) newIndex--;
                              final item = batch.orders!.removeAt(oldIndex);
                              batch.orders!.insert(newIndex, item);
                              final orderIds = batch.orders!.map((o) => o.orderId!).toList();
                              batchController.updateSequence(batch.id!, orderIds);
                            }
                          },
                          itemBuilder: (context, index) {
                            final bo = batch.orders![index];
                            return _BatchOrderTile(
                              key: ValueKey(bo.orderId),
                              batchOrder: bo,
                              index: index,
                              isLast: index == batch.orders!.length - 1,
                            );
                          },
                        )
                      else
                        Center(child: Text('no_orders_in_batch'.tr)),
                    ],
                  ),
                ),
              ),
            ),
            _BatchActions(batch: batch, controller: batchController),
          ],
        );
      }),
    );
  }
}

class _BatchInfoHeader extends StatelessWidget {
  final DeliveryBatchModel batch;
  const _BatchInfoHeader({required this.batch});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).primaryColor.withValues(alpha: 0.05),
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
      ),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _InfoItem(icon: Icons.shopping_bag_outlined, label: 'orders'.tr, value: '${batch.orders?.length ?? 0}'),
              _InfoItem(icon: Icons.route_outlined, label: 'distance'.tr, value: '${batch.totalDistance?.toStringAsFixed(1) ?? '0.0'} km'),
              _InfoItem(icon: Icons.access_time_outlined, label: 'est_time'.tr, value: '${batch.totalEstimatedTime ?? 0} min'),
              _InfoItem(icon: Icons.attach_money_outlined, label: 'earnings'.tr, value: '${batch.earnings?.toStringAsFixed(2) ?? '0.00'}'),
            ],
          ),
        ],
      ),
    );
  }
}

class _InfoItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  const _InfoItem({required this.icon, required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, size: 24, color: Theme.of(context).primaryColor),
        const SizedBox(height: Dimensions.paddingSizeExtraSmall),
        Text(value, style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault)),
        Text(label, style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeExtraSmall, color: Theme.of(context).disabledColor)),
      ],
    );
  }
}

class _BatchOrderTile extends StatelessWidget {
  final BatchOrderModel batchOrder;
  final int index;
  final bool isLast;
  const _BatchOrderTile({super.key, required this.batchOrder, required this.index, required this.isLast});

  @override
  Widget build(BuildContext context) {
    final order = batchOrder.order;
    return Container(
      margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        boxShadow: Get.isDarkMode ? null : [BoxShadow(color: Colors.grey[200]!, spreadRadius: 1, blurRadius: 5)],
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 28,
                height: 28,
                decoration: BoxDecoration(
                  color: Theme.of(context).primaryColor,
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text('${index + 1}', style: robotoBold.copyWith(fontSize: Dimensions.fontSizeSmall, color: Colors.white)),
                ),
              ),
              const SizedBox(width: Dimensions.paddingSizeSmall),
              Expanded(
                child: Text(
                  '${'order'.tr} #${order?.id ?? batchOrder.orderId}',
                  style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                ),
              ),
              if (batchOrder.actualArrival != null)
                Icon(Icons.check_circle, size: 20, color: Colors.green)
              else if (!isLast)
                Icon(Icons.drag_handle, size: 20, color: Theme.of(context).disabledColor),
            ],
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          if (order != null) ...[
            _DetailRow(icon: Icons.store_outlined, text: order.storeName ?? ''),
            const SizedBox(height: Dimensions.paddingSizeExtraSmall),
            _DetailRow(icon: Icons.location_on_outlined, text: order.deliveryAddress ?? ''),
            const SizedBox(height: Dimensions.paddingSizeExtraSmall),
            _DetailRow(icon: Icons.phone_outlined, text: order.customerPhone ?? ''),
            const SizedBox(height: Dimensions.paddingSizeExtraSmall),
            _DetailRow(icon: Icons.payment_outlined, text: '${order.paymentMethod ?? ''} | ${order.paymentStatus ?? ''}'),
          ],
          if (batchOrder.distanceFromPrev != null && batchOrder.distanceFromPrev! > 0)
            Padding(
              padding: const EdgeInsets.only(top: Dimensions.paddingSizeExtraSmall),
              child: Text(
                '${batchOrder.distanceFromPrev!.toStringAsFixed(1)} km ${'from_previous'.tr}',
                style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeExtraSmall, color: Theme.of(context).primaryColor),
              ),
            ),
        ],
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  final IconData icon;
  final String text;
  const _DetailRow({required this.icon, required this.text});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 14, color: Theme.of(context).disabledColor),
        const SizedBox(width: Dimensions.paddingSizeExtraSmall),
        Expanded(
          child: Text(
            text,
            style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }
}

class _BatchActions extends StatelessWidget {
  final DeliveryBatchModel batch;
  final BatchController controller;
  const _BatchActions({required this.batch, required this.controller});

  @override
  Widget build(BuildContext context) {
    if (batch.status == 'pending') {
      return Container(
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        decoration: BoxDecoration(
          color: Theme.of(context).cardColor,
          boxShadow: [BoxShadow(color: Colors.grey[200]!, blurRadius: 5, spreadRadius: 1)],
        ),
        child: SafeArea(
          child: Row(
            children: [
              Expanded(
                child: CustomButtonWidget(
                  buttonText: 'reject'.tr,
                  transparent: true,
                  backgroundColor: Colors.red,
                  onPressed: controller.isActionLoading
                      ? null
                      : () => controller.rejectBatch(batch.id!),
                ),
              ),
              const SizedBox(width: Dimensions.paddingSizeSmall),
              Expanded(
                child: CustomButtonWidget(
                  buttonText: 'accept'.tr,
                  onPressed: controller.isActionLoading
                      ? null
                      : () => controller.acceptBatch(batch.id!),
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (batch.status == 'active' && batch.orders != null) {
      final nextOrder = batch.orders!.firstWhereOrNull((o) => o.actualArrival == null);
      if (nextOrder != null) {
        return Container(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          decoration: BoxDecoration(
            color: Theme.of(context).cardColor,
            boxShadow: [BoxShadow(color: Colors.grey[200]!, blurRadius: 5, spreadRadius: 1)],
          ),
          child: SafeArea(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                CustomButtonWidget(
                  buttonText: '${'complete_delivery'.tr} #${nextOrder.orderId}',
                  onPressed: controller.isActionLoading
                      ? null
                      : () => controller.completeDeliveryInBatch(batch.id!, nextOrder.orderId!),
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                CustomButtonWidget(
                  buttonText: 'view_order_details'.tr,
                  transparent: true,
                  onPressed: () {
                    Get.toNamed(RouteHelper.getOrderDetailsRoute(nextOrder.orderId));
                  },
                ),
              ],
            ),
          ),
        );
      }
    }

    return const SizedBox.shrink();
  }
}
