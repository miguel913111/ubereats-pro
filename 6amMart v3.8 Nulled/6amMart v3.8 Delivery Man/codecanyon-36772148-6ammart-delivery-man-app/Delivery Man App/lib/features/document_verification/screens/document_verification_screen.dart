import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:image_picker/image_picker.dart';
import '../../../common/widgets/custom_button_widget.dart';
import '../../profile/controllers/profile_controller.dart';
import '../controllers/document_verification_controller.dart';

class DocumentVerificationScreen extends StatefulWidget {
  const DocumentVerificationScreen({Key? key}) : super(key: key);

  @override
  State<DocumentVerificationScreen> createState() => _DocumentVerificationScreenState();
}

class _DocumentVerificationScreenState extends State<DocumentVerificationScreen> {
  final TextEditingController _documentTypeController = TextEditingController();
  final TextEditingController _documentNumberController = TextEditingController();
  final TextEditingController _notesController = TextEditingController();
  final List<String> _base64Images = [];

  @override
  void dispose() {
    _documentTypeController.dispose();
    _documentNumberController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: ImageSource.gallery, imageQuality: 70);
    if (picked != null) {
      final bytes = await File(picked.path).readAsBytes();
      setState(() {
        _base64Images.add(base64Encode(bytes));
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('document_verification'.tr)),
      body: GetBuilder<DocumentVerificationController>(
        builder: (controller) {
          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                TextField(
                  controller: _documentTypeController,
                  decoration: InputDecoration(
                    labelText: 'document_type'.tr,
                    hintText: 'e.g. ID, License, Passport',
                    border: const OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _documentNumberController,
                  decoration: InputDecoration(
                    labelText: 'document_number'.tr,
                    border: const OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _notesController,
                  decoration: InputDecoration(
                    labelText: 'notes'.tr,
                    border: const OutlineInputBorder(),
                  ),
                  maxLines: 3,
                ),
                const SizedBox(height: 16),
                Text('document_images'.tr, style: const TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    ..._base64Images.map((b64) => Stack(
                      children: [
                        Image.memory(
                          base64Decode(b64),
                          width: 100,
                          height: 100,
                          fit: BoxFit.cover,
                        ),
                        Positioned(
                          top: 0,
                          right: 0,
                          child: GestureDetector(
                            onTap: () => setState(() => _base64Images.remove(b64)),
                            child: Container(
                              color: Colors.red,
                              child: const Icon(Icons.close, color: Colors.white, size: 18),
                            ),
                          ),
                        ),
                      ],
                    )),
                    InkWell(
                      onTap: _pickImage,
                      child: Container(
                        width: 100,
                        height: 100,
                        decoration: BoxDecoration(
                          border: Border.all(color: Colors.grey),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(Icons.add_a_photo),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 24),
                CustomButtonWidget(
                  buttonText: 'submit'.tr,
                  isLoading: controller.isLoading,
                  onPressed: _base64Images.isEmpty || _documentTypeController.text.isEmpty
                      ? null
                      : () async {
                          final profileController = Get.find<ProfileController>();
                          final dmId = profileController.profileModel?.id;
                          if (dmId == null) {
                            Get.snackbar('Error', 'Delivery man info not found');
                            return;
                          }
                          Response response = await controller.submitDocument({
                            'document_type': _documentTypeController.text,
                            'document_number': _documentNumberController.text,
                            'document_images': _base64Images,
                            'notes': _notesController.text,
                            'verifiable_type': 'App\\Models\\DeliveryMan',
                            'verifiable_id': dmId.toString(),
                          });
                          if (response.statusCode == 200) {
                            Get.snackbar('Success', response.body['message'] ?? 'Document submitted', backgroundColor: Colors.green, colorText: Colors.white);
                            setState(() {
                              _base64Images.clear();
                              _documentTypeController.clear();
                              _documentNumberController.clear();
                              _notesController.clear();
                            });
                          } else {
                            Get.snackbar('Error', response.statusText ?? 'Failed to submit');
                          }
                        },
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
