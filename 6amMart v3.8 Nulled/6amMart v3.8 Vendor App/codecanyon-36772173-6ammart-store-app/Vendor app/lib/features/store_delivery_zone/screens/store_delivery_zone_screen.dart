import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:nexofood_vendor/features/store_delivery_zone/controllers/store_delivery_zone_controller.dart';
import 'package:nexofood_vendor/features/profile/controllers/profile_controller.dart';
import 'map_zone_screen.dart';

class StoreDeliveryZoneScreen extends StatefulWidget {
  const StoreDeliveryZoneScreen({Key? key}) : super(key: key);

  @override
  State<StoreDeliveryZoneScreen> createState() => _StoreDeliveryZoneScreenState();
}

class _StoreDeliveryZoneScreenState extends State<StoreDeliveryZoneScreen> {
  @override
  void initState() {
    super.initState();
    final storeId = Get.find<ProfileController>().profileModel?.stores?[0].id;
    if (storeId != null) {
      Get.find<StoreDeliveryZoneController>().getZones(storeId);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('delivery_zones'.tr)),
      body: GetBuilder<StoreDeliveryZoneController>(
        builder: (controller) {
          return controller.isLoading
              ? const Center(child: CircularProgressIndicator())
              : Column(
                  children: [
                    Expanded(
                      child: controller.zones.isEmpty
                          ? Center(child: Text('no_delivery_zones'.tr))
                          : ListView.builder(
                              itemCount: controller.zones.length,
                              itemBuilder: (context, index) {
                                final zone = controller.zones[index];
                                return ListTile(
                                  title: Text(zone['name'] ?? 'Zone ${index + 1}'),
                                  subtitle: Text('Delivery: ${zone['delivery_charge']}'),
                                  trailing: Switch(
                                    value: zone['status'] == 1,
                                    onChanged: (value) {},
                                  ),
                                );
                              },
                            ),
                    ),
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: ElevatedButton(
                        onPressed: () async {
                          final result = await Get.to(() => const MapZoneScreen());
                          if (result == true) {
                            final storeId = Get.find<ProfileController>().profileModel?.stores?[0].id;
                            if (storeId != null) {
                              Get.find<StoreDeliveryZoneController>().getZones(storeId);
                            }
                          }
                        },
                        child: Text('add_delivery_zone'.tr),
                      ),
                    ),
                  ],
                );
        },
      ),
    );
  }
}
