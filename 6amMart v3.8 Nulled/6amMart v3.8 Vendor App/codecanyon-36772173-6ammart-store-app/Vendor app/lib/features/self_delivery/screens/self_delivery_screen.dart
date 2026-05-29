import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../common/widgets/custom_snackbar_widget.dart';
import '../controllers/self_delivery_controller.dart';

class SelfDeliveryScreen extends StatefulWidget {
  const SelfDeliveryScreen({Key? key}) : super(key: key);

  @override
  State<SelfDeliveryScreen> createState() => _SelfDeliveryScreenState();
}

class _SelfDeliveryScreenState extends State<SelfDeliveryScreen> {
  @override
  void initState() {
    super.initState();
    Get.find<SelfDeliveryController>().getDeliveryMen();
  }

  void _showAddDeliveryManDialog() {
    final TextEditingController fNameController = TextEditingController();
    final TextEditingController lNameController = TextEditingController();
    final TextEditingController phoneController = TextEditingController();
    final TextEditingController emailController = TextEditingController();
    final TextEditingController passwordController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text('add_delivery_man'.tr),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: fNameController,
                  decoration: InputDecoration(labelText: 'first_name'.tr),
                ),
                TextField(
                  controller: lNameController,
                  decoration: InputDecoration(labelText: 'last_name'.tr),
                ),
                TextField(
                  controller: phoneController,
                  decoration: InputDecoration(labelText: 'phone'.tr),
                  keyboardType: TextInputType.phone,
                ),
                TextField(
                  controller: emailController,
                  decoration: InputDecoration(labelText: 'email'.tr),
                  keyboardType: TextInputType.emailAddress,
                ),
                TextField(
                  controller: passwordController,
                  decoration: InputDecoration(labelText: 'password'.tr),
                  obscureText: true,
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Get.back(),
              child: Text('cancel'.tr),
            ),
            ElevatedButton(
              onPressed: () async {
                if (fNameController.text.isEmpty || phoneController.text.isEmpty || passwordController.text.isEmpty) {
                  showCustomSnackBar('Please fill required fields');
                  return;
                }
                final controller = Get.find<SelfDeliveryController>();
                Response response = await controller.createDeliveryMan({
                  'f_name': fNameController.text,
                  'l_name': lNameController.text,
                  'phone': phoneController.text,
                  'email': emailController.text,
                  'password': passwordController.text,
                });
                if (response.statusCode == 200) {
                  showCustomSnackBar(response.body['message'] ?? 'Delivery man added', isError: false);
                  Get.back();
                  controller.getDeliveryMen();
                } else {
                  showCustomSnackBar(response.statusText ?? 'Failed to add');
                }
              },
              child: Text('add'.tr),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('self_delivery'.tr)),
      body: GetBuilder<SelfDeliveryController>(
        builder: (controller) {
          return Column(
            children: [
              Expanded(
                child: controller.deliveryMen.isEmpty
                    ? Center(child: Text('no_delivery_men'.tr))
                    : ListView.builder(
                        itemCount: controller.deliveryMen.length,
                        itemBuilder: (context, index) {
                          final dm = controller.deliveryMen[index];
                          return ListTile(
                            leading: CircleAvatar(
                              child: Text('${dm['f_name']?[0] ?? ''}'),
                            ),
                            title: Text('${dm['f_name']} ${dm['l_name'] ?? ''}'),
                            subtitle: Text(dm['phone'] ?? ''),
                            trailing: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: dm['status'] == 1 ? Colors.green.withOpacity(0.2) : Colors.red.withOpacity(0.2),
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                  child: Text(
                                    dm['status'] == 1 ? 'active'.tr : 'inactive'.tr,
                                    style: TextStyle(
                                      color: dm['status'] == 1 ? Colors.green : Colors.red,
                                      fontSize: 12,
                                    ),
                                  ),
                                ),
                                IconButton(
                                  icon: const Icon(Icons.delete, color: Colors.red),
                                  onPressed: () => controller.deleteDeliveryMan(dm['id']),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
              ),
              Padding(
                padding: const EdgeInsets.all(16),
                child: ElevatedButton.icon(
                  onPressed: _showAddDeliveryManDialog,
                  icon: const Icon(Icons.add),
                  label: Text('add_delivery_man'.tr),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}
