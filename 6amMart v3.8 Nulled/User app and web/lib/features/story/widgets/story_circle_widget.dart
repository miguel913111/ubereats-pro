import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../util/dimensions.dart';
import '../controllers/story_controller.dart';
import '../screens/story_view_screen.dart';

class StoryCircleWidget extends StatefulWidget {
  const StoryCircleWidget({Key? key}) : super(key: key);

  @override
  State<StoryCircleWidget> createState() => _StoryCircleWidgetState();
}

class _StoryCircleWidgetState extends State<StoryCircleWidget> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Get.find<StoryController>().getStories();
    });
  }

  @override
  Widget build(BuildContext context) {
    return GetBuilder<StoryController>(
      builder: (controller) {
        if (controller.stories.isEmpty) {
          return const SizedBox.shrink();
        }
        return SizedBox(
          height: 100,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeSmall),
            itemCount: controller.stories.length,
            itemBuilder: (context, index) {
              final story = controller.stories[index];
              return GestureDetector(
                onTap: () {
                  controller.setCurrentStory(index);
                  Get.to(() => const StoryViewScreen());
                },
                child: Container(
                  width: 80,
                  margin: const EdgeInsets.only(right: Dimensions.paddingSizeSmall),
                  child: Column(
                    children: [
                      Container(
                        width: 70,
                        height: 70,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          gradient: const LinearGradient(
                            colors: [Colors.purple, Colors.orange, Colors.red],
                          ),
                          border: Border.all(color: Colors.transparent, width: 3),
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(3),
                          child: ClipOval(
                            child: story.image != null
                                ? Image.network(story.image!, fit: BoxFit.cover)
                                : Container(color: Colors.grey),
                          ),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        story.title ?? 'Story',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(fontSize: 12),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        );
      },
    );
  }
}
