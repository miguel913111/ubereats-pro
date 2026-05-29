import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../common/widgets/custom_app_bar.dart';
import '../../../helper/auth_helper.dart';
import '../../../util/dimensions.dart';
import '../../../util/styles.dart';
import '../controllers/dine_in_controller.dart';
import 'table_reservation_screen.dart';

class DineInScreen extends StatefulWidget {
  const DineInScreen({Key? key}) : super(key: key);

  @override
  State<DineInScreen> createState() => _DineInScreenState();
}

class _DineInScreenState extends State<DineInScreen> {
  @override
  void initState() {
    super.initState();
    if (AuthHelper.isLoggedIn()) {
      Get.find<DineInController>().getDineInStores();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: 'dine_in'.tr),
      body: GetBuilder<DineInController>(
        builder: (controller) {
          return Column(
            children: [
              Expanded(
                child: controller.dineInStores.isEmpty
                    ? Center(
                        child: Text(
                          'no_dine_in_restaurants'.tr,
                          style: robotoMedium,
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                        itemCount: controller.dineInStores.length,
                        itemBuilder: (context, index) {
                          final store = controller.dineInStores[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
                            child: ListTile(
                              leading: const Icon(Icons.restaurant),
                              title: Text(store.name ?? ''),
                              subtitle: Text(store.address ?? ''),
                              trailing: ElevatedButton(
                                onPressed: () {
                                  controller.getTables(store.id!);
                                  Get.to(() => TableReservationScreen(store: store));
                                },
                                child: Text('reserve'.tr),
                              ),
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
}
