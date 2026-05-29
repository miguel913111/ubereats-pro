import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:nexofood_vendor/features/dine_in/controllers/dine_in_controller.dart';
import 'package:nexofood_vendor/features/profile/controllers/profile_controller.dart';

class DineInManagementScreen extends StatefulWidget {
  const DineInManagementScreen({Key? key}) : super(key: key);

  @override
  State<DineInManagementScreen> createState() => _DineInManagementScreenState();
}

class _DineInManagementScreenState extends State<DineInManagementScreen> {
  @override
  void initState() {
    super.initState();
    final storeId = Get.find<ProfileController>().profileModel?.stores?[0].id;
    if (storeId != null) {
      Get.find<DineInController>().getTables(storeId);
      Get.find<DineInController>().getReservations(storeId);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('dine_in_management'.tr),
        actions: [
          IconButton(
            icon: const Icon(Icons.qr_code_scanner),
            onPressed: () {
              Get.toNamed('/qr-scanner');
            },
          ),
        ],
      ),
      body: GetBuilder<DineInController>(
        builder: (controller) {
          return DefaultTabController(
            length: 2,
            child: Column(
              children: [
                TabBar(
                  tabs: [
                    Tab(text: 'tables'.tr),
                    Tab(text: 'reservations'.tr),
                  ],
                ),
                Expanded(
                  child: TabBarView(
                    children: [
                      _buildTablesTab(controller),
                      _buildReservationsTab(controller),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildTablesTab(DineInController controller) {
    return controller.tables.isEmpty
        ? Center(child: Text('no_tables'.tr))
        : ListView.builder(
            itemCount: controller.tables.length,
            itemBuilder: (context, index) {
              final table = controller.tables[index];
              return ListTile(
                title: Text('Table ${table['table_number']}'),
                subtitle: Text('Capacity: ${table['capacity']}'),
                trailing: Chip(
                  label: Text(table['status']),
                  backgroundColor: table['status'] == 'available' ? Colors.green.withOpacity(0.2) : Colors.orange.withOpacity(0.2),
                ),
              );
            },
          );
  }

  Widget _buildReservationsTab(DineInController controller) {
    return controller.reservations.isEmpty
        ? Center(child: Text('no_reservations'.tr))
        : ListView.builder(
            itemCount: controller.reservations.length,
            itemBuilder: (context, index) {
              final res = controller.reservations[index];
              return ListTile(
                title: Text('${res['reservation_date']} ${res['reservation_time']}'),
                subtitle: Text('Guests: ${res['number_of_guests']}'),
                trailing: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (res['status'] == 'pending')
                      IconButton(
                        icon: const Icon(Icons.check, color: Colors.green),
                        onPressed: () => controller.confirmReservation(res['id']),
                      ),
                    IconButton(
                      icon: const Icon(Icons.cancel, color: Colors.red),
                      onPressed: () => controller.cancelReservation(res['id'], 'Vendor cancelled'),
                    ),
                  ],
                ),
              );
            },
          );
  }
}
