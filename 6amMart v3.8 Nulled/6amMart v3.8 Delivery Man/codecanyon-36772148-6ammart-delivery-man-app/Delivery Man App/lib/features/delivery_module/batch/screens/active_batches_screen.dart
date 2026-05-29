import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:nexofood_delivery/common/widgets/custom_app_bar_widget.dart';
import 'package:nexofood_delivery/common/widgets/custom_button_widget.dart';
import 'package:nexofood_delivery/features/delivery_module/batch/controllers/batch_controller.dart';
import 'package:nexofood_delivery/features/delivery_module/batch/domain/models/delivery_batch_model.dart';
import 'package:nexofood_delivery/helper/route_helper.dart';
import 'package:nexofood_delivery/util/dimensions.dart';
import 'package:nexofood_delivery/util/styles.dart';

class ActiveBatchesScreen extends StatefulWidget {
  const ActiveBatchesScreen({super.key});

  @override
  State<ActiveBatchesScreen> createState() => _ActiveBatchesScreenState();
}

class _ActiveBatchesScreenState extends State<ActiveBatchesScreen> {
  @override
  void initState() {
    super.initState();
    Get.find<BatchController>().getMyBatch();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).cardColor,
      appBar: CustomAppBarWidget(title: 'my_batch'.tr, isBackButtonExist: true),
      body: GetBuilder<BatchController>(builder: (batchController) {
        if (batchController.isLoading && batchController.myBatch == null) {
          return const Center(child: CircularProgressIndicator());
        }
        final batch = batchController.myBatch;
        if (batch == null) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.local_shipping_outlined, size: 64, color: Theme.of(context).disabledColor),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                Text('no_active_batch'.tr, style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeLarge, color: Theme.of(context).disabledColor)),
              ],
            ),
          );
        }
        return RefreshIndicator(
          onRefresh: () async => await batchController.getMyBatch(),
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
                if (batch.orders != null && batch.orders!.isNotEmpty)
                  ListView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: batch.orders!.length,
                    itemBuilder: (context, index) {
                      final bo = batch.orders![index];
                      return _BatchOrderTile(
                        batchOrder: bo,
                        index: index,
                        batch: batch,
                      );
                    },
                  )
                else
                  Center(child: Text('no_orders_in_batch'.tr)),
              ],
            ),
          ),
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
    Color statusColor;
    switch (batch.status) {
      case 'active':
        statusColor = Colors.green;
        break;
      case 'pending':
        statusColor = Colors.orange;
        break;
      case 'completed':
        statusColor = Colors.blue;
        break;
      case 'cancelled':
        statusColor = Colors.red;
        break;
      default:
        statusColor = Theme.of(context).primaryColor;
    }

    return Container(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).primaryColor.withValues(alpha: 0.05),
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
      ),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '${'batch'.tr} #${batch.id}',
                style: robotoBold.copyWith(fontSize: Dimensions.fontSizeLarge),
              ),
              Container(
                padding: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeExtraSmall, horizontal: Dimensions.paddingSizeSmall),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                  color: statusColor.withValues(alpha: 0.1),
                ),
                child: Text(
                  batch.status?.toUpperCase() ?? '',
                  style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeExtraSmall, color: statusColor),
                ),
              ),
            ],
          ),
          const SizedBox(height: Dimensions.paddingSizeDefault),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _InfoItem(icon: Icons.shopping_bag_outlined, label: 'orders'.tr, value: '${batch.totalOrders ?? 0}'),
              _InfoItem(icon: Icons.route_outlined, label: 'distance'.tr, value: '${batch.totalDistanceKm?.toStringAsFixed(1) ?? '0.0'} km'),
              _InfoItem(icon: Icons.access_time_outlined, label: 'est_time'.tr, value: '${batch.estimatedDurationMin?.toStringAsFixed(0) ?? '0'} min'),
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
  final DeliveryBatchModel batch;
  const _BatchOrderTile({required this.batchOrder, required this.index, required this.batch});

  @override
  Widget build(BuildContext context) {
    final bool isDelivered = batchOrder.deliveredAt != null;
    final bool isNext = !isDelivered && (index == 0 || batch.orders![index - 1].deliveredAt != null);

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
                  color: isDelivered ? Colors.green : isNext ? Theme.of(context).primaryColor : Colors.grey,
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: isDelivered
                      ? const Icon(Icons.check, size: 16, color: Colors.white)
                      : Text('${index + 1}', style: robotoBold.copyWith(fontSize: Dimensions.fontSizeSmall, color: Colors.white)),
                ),
              ),
              const SizedBox(width: Dimensions.paddingSizeSmall),
              Expanded(
                child: Text(
                  '${'order'.tr} #${batchOrder.orderId}',
                  style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                ),
              ),
              if (isDelivered)
                Container(
                  padding: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeExtraSmall, horizontal: Dimensions.paddingSizeSmall),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                    color: Colors.green.withValues(alpha: 0.1),
                  ),
                  child: Text('delivered'.tr, style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeExtraSmall, color: Colors.green)),
                )
              else if (isNext)
                Container(
                  padding: const EdgeInsets.symmetric(vertical: Dimensions.paddingSizeExtraSmall, horizontal: Dimensions.paddingSizeSmall),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                    color: Theme.of(context).primaryColor.withValues(alpha: 0.1),
                  ),
                  child: Text('next'.tr, style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeExtraSmall, color: Theme.of(context).primaryColor)),
                ),
            ],
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          _DetailRow(icon: Icons.person_outline, text: batchOrder.customerName ?? ''),
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
          _DetailRow(icon: Icons.phone_outlined, text: batchOrder.customerPhone ?? ''),
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
          _DetailRow(icon: Icons.location_on_outlined, text: batchOrder.deliveryAddress ?? ''),
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
          _DetailRow(icon: Icons.payment_outlined, text: '${batchOrder.paymentMethod ?? ''} | ${batchOrder.orderAmount?.toStringAsFixed(2) ?? '0.00'}'),
          if (batchOrder.distanceFromPrevKm != null && batchOrder.distanceFromPrevKm! > 0)
            Padding(
              padding: const EdgeInsets.only(top: Dimensions.paddingSizeExtraSmall),
              child: Text(
                '${batchOrder.distanceFromPrevKm!.toStringAsFixed(1)} km ${'from_previous'.tr}',
                style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeExtraSmall, color: Theme.of(context).primaryColor),
              ),
            ),
          if (isNext && batch.status == 'active') ...[
            const SizedBox(height: Dimensions.paddingSizeSmall),
            CustomButtonWidget(
              buttonText: 'mark_as_delivered'.tr,
              height: 40,
              onPressed: () {
                Get.find<BatchController>().deliverOrder(batch.id!, batchOrder.orderId!);
              },
            ),
          ],
          if (isNext && batch.status == 'pending') ...[
            const SizedBox(height: Dimensions.paddingSizeSmall),
            CustomButtonWidget(
              buttonText: 'start_batch'.tr,
              height: 40,
              onPressed: () {
                Get.find<BatchController>().startBatch(batch.id!);
              },
            ),
          ],
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
