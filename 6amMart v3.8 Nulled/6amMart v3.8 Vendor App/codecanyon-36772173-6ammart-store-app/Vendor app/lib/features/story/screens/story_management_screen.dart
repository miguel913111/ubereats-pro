import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:image_picker/image_picker.dart';
import '../../../common/widgets/custom_snackbar_widget.dart';
import '../controllers/story_controller.dart';

class StoryManagementScreen extends StatefulWidget {
  const StoryManagementScreen({Key? key}) : super(key: key);

  @override
  State<StoryManagementScreen> createState() => _StoryManagementScreenState();
}

class _StoryManagementScreenState extends State<StoryManagementScreen> {
  @override
  void initState() {
    super.initState();
    Get.find<StoryController>().getStories();
  }

  void _showAddStoryDialog() {
    final TextEditingController titleController = TextEditingController();
    String? base64Image;

    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              title: Text('add_story'.tr),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    TextField(
                      controller: titleController,
                      decoration: InputDecoration(labelText: 'title'.tr),
                    ),
                    const SizedBox(height: 16),
                    if (base64Image != null)
                      Image.memory(
                        base64Decode(base64Image!),
                        height: 150,
                        fit: BoxFit.cover,
                      ),
                    const SizedBox(height: 8),
                    ElevatedButton.icon(
                      onPressed: () async {
                        final picker = ImagePicker();
                        final picked = await picker.pickImage(source: ImageSource.gallery, imageQuality: 70);
                        if (picked != null) {
                          final bytes = await File(picked.path).readAsBytes();
                          setState(() {
                            base64Image = base64Encode(bytes);
                          });
                        }
                      },
                      icon: const Icon(Icons.image),
                      label: Text(base64Image == null ? 'select_image'.tr : 'change_image'.tr),
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
                  onPressed: base64Image == null
                      ? null
                      : () async {
                          final controller = Get.find<StoryController>();
                          Response response = await controller.createStory({
                            'title': titleController.text,
                            'image': base64Image,
                            'type': 'image',
                            'duration': '5',
                          });
                          if (response.statusCode == 200) {
                            showCustomSnackBar(response.body['message'] ?? 'Story added', isError: false);
                            Get.back();
                            controller.getStories();
                          } else {
                            showCustomSnackBar(response.statusText ?? 'Failed to add story');
                          }
                        },
                  child: Text('add'.tr),
                ),
              ],
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('stories'.tr)),
      body: GetBuilder<StoryController>(
        builder: (controller) {
          return Column(
            children: [
              Expanded(
                child: controller.stories.isEmpty
                    ? Center(child: Text('no_stories'.tr))
                    : ListView.builder(
                        itemCount: controller.stories.length,
                        itemBuilder: (context, index) {
                          final story = controller.stories[index];
                          return ListTile(
                            leading: story['image'] != null
                                ? Image.network(story['image'], width: 50, height: 50, fit: BoxFit.cover)
                                : const Icon(Icons.image),
                            title: Text(story['title'] ?? 'Story ${index + 1}'),
                            subtitle: Text('Expires: ${story['expires_at']}'),
                            trailing: IconButton(
                              icon: const Icon(Icons.delete, color: Colors.red),
                              onPressed: () => controller.deleteStory(story['id']),
                            ),
                          );
                        },
                      ),
              ),
              Padding(
                padding: const EdgeInsets.all(16),
                child: ElevatedButton.icon(
                  onPressed: _showAddStoryDialog,
                  icon: const Icon(Icons.add),
                  label: Text('add_story'.tr),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}
