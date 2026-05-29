import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:nexofood_delivery/features/delivery_batch/controllers/delivery_batch_controller.dart';

class DeliveryBatchScreen extends StatelessWidget {
  const DeliveryBatchScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('my_delivery_batch'.tr)),
      body: GetBuilder<DeliveryBatchController>(
        builder: (controller) {
          if (controller.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          final batch = controller.batch;
          if (batch == null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.local_shipping, size: 64, color: Colors.grey),
                  const SizedBox(height: 16),
                  Text('no_active_batch'.tr, style: const TextStyle(fontSize: 18, color: Colors.grey)),
                ],
              ),
            );
          }

          return Column(
            children: [
              // Batch Header
              Card(
                margin: const EdgeInsets.all(12),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('batch_id'.tr + ' #${batch['id']}',
                              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                          Chip(
                            label: Text(batch['status'].toString().toUpperCase()),
                            backgroundColor: batch['status'] == 'active'
                                ? Colors.blue.shade100
                                : Colors.orange.shade100,
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          _buildInfoChip(Icons.local_shipping, '${batch['total_orders']} ${'orders'.tr}'),
                          const SizedBox(width: 8),
                          _buildInfoChip(Icons.route, '${batch['total_distance_km']} km'),
                          const SizedBox(width: 8),
                          _buildInfoChip(Icons.timer, '${batch['estimated_duration_min']} min'),
                        ],
                      ),
                      if (batch['status'] == 'pending')
                        Padding(
                          padding: const EdgeInsets.only(top: 12),
                          child: SizedBox(
                            width: double.infinity,
                            child: ElevatedButton.icon(
                              onPressed: () => _startBatch(controller, batch['id']),
                              icon: const Icon(Icons.play_arrow),
                              label: Text('start_batch'.tr),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),

              // Orders List
              Expanded(
                child: ListView.builder(
                  itemCount: controller.orders.length,
                  itemBuilder: (context, index) {
                    final order = controller.orders[index];
                    final isDelivered = order['delivered_at'] != null;
                    final canDeliver = batch['status'] == 'active' && !isDelivered;

                    return Card(
                      margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: isDelivered ? Colors.green : Colors.blue,
                          child: Text('${order['sequence']}',
                              style: const TextStyle(color: Colors.white)),
                        ),
                        title: Text('${order['customer_name'] ?? '-'}'),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('${order['delivery_address'] ?? '-'}',
                                maxLines: 2, overflow: TextOverflow.ellipsis),
                            const SizedBox(height: 4),
                            Text('${order['distance_from_prev_km']} km | ${order['estimated_time_min']} min',
                                style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
                          ],
                        ),
                        trailing: canDeliver
                            ? ElevatedButton(
                                onPressed: () => _deliverOrder(controller, batch['id'], order['order_id']),
                                child: Text('deliver'.tr),
                              )
                            : isDelivered
                                ? const Icon(Icons.check_circle, color: Colors.green)
                                : const SizedBox(),
                      ),
                    );
                  },
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildInfoChip(IconData icon, String label) {
    return Chip(
      avatar: Icon(icon, size: 16),
      label: Text(label, style: const TextStyle(fontSize: 12)),
    );
  }

  void _startBatch(DeliveryBatchController controller, int batchId) async {
    Response response = await controller.startBatch(batchId);
    if (response.statusCode == 200) {
      Get.snackbar('success'.tr, 'batch_started'.tr, snackPosition: SnackPosition.BOTTOM);
      controller.getMyBatch();
    } else {
      Get.snackbar('error'.tr, response.body['errors']?[0]?['message'] ?? 'failed'.tr,
          snackPosition: SnackPosition.BOTTOM);
    }
  }

  void _deliverOrder(DeliveryBatchController controller, int batchId, int orderId) async {
    Response response = await controller.deliverOrder(batchId, orderId);
    if (response.statusCode == 200) {
      Get.snackbar('success'.tr, 'order_delivered'.tr, snackPosition: SnackPosition.BOTTOM);
      controller.getMyBatch();
    } else {
      Get.snackbar('error'.tr, response.body['errors']?[0]?['message'] ?? 'failed'.tr,
          snackPosition: SnackPosition.BOTTOM);
    }
  }
}
