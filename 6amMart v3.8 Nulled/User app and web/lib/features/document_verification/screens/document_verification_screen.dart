import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../common/widgets/custom_app_bar.dart';
import '../../../common/widgets/custom_button.dart';
import '../../../util/dimensions.dart';
import '../../../util/styles.dart';
import '../../../features/profile/controllers/profile_controller.dart';
import '../controllers/document_verification_controller.dart';

class DocumentVerificationScreen extends StatefulWidget {
  const DocumentVerificationScreen({Key? key}) : super(key: key);

  @override
  State<DocumentVerificationScreen> createState() => _DocumentVerificationScreenState();
}

class _DocumentVerificationScreenState extends State<DocumentVerificationScreen> {
  @override
  void initState() {
    super.initState();
    final userId = Get.find<ProfileController>().userInfoModel?.id;
    if (userId != null) {
      Get.find<DocumentVerificationController>().checkStatus('user', userId);
      Get.find<DocumentVerificationController>().getMyDocuments('user', userId);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: 'document_verification'.tr),
      body: GetBuilder<DocumentVerificationController>(
        builder: (controller) {
          return Column(
            children: [
              Container(
                padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                margin: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                decoration: BoxDecoration(
                  color: Theme.of(context).cardColor,
                  borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                  boxShadow: [BoxShadow(color: Colors.grey.withOpacity(0.1), spreadRadius: 1, blurRadius: 5)],
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _buildStatusItem(context, 'pending'.tr, controller.pendingCount.toString(), Colors.orange),
                    _buildStatusItem(context, 'approved'.tr, controller.approvedCount.toString(), Colors.green),
                    _buildStatusItem(context, 'rejected'.tr, controller.rejectedCount.toString(), Colors.red),
                  ],
                ),
              ),
              Expanded(
                child: controller.documentList.isEmpty
                    ? Center(child: Text('no_documents_found'.tr, style: robotoMedium))
                    : ListView.builder(
                        padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                        itemCount: controller.documentList.length,
                        itemBuilder: (context, index) {
                          final doc = controller.documentList[index];
                          return Card(
                            child: ListTile(
                              title: Text(doc.documentType ?? ''),
                              subtitle: Text(doc.status ?? ''),
                              trailing: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(
                                  color: doc.status == 'approved'
                                      ? Colors.green.withOpacity(0.1)
                                      : doc.status == 'rejected'
                                          ? Colors.red.withOpacity(0.1)
                                          : Colors.orange.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: Text(
                                  (doc.status ?? '').tr,
                                  style: TextStyle(
                                    color: doc.status == 'approved'
                                        ? Colors.green
                                        : doc.status == 'rejected'
                                            ? Colors.red
                                            : Colors.orange,
                                  ),
                                ),
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

  Widget _buildStatusItem(BuildContext context, String label, String value, Color color) {
    return Column(
      children: [
        Text(value, style: robotoBold.copyWith(fontSize: Dimensions.fontSizeExtraLarge, color: color)),
        const SizedBox(height: 4),
        Text(label, style: robotoRegular.copyWith(color: Theme.of(context).disabledColor)),
      ],
    );
  }
}
