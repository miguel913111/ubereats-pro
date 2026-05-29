import 'dart:async';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/story_controller.dart';

class StoryViewScreen extends StatefulWidget {
  const StoryViewScreen({Key? key}) : super(key: key);

  @override
  State<StoryViewScreen> createState() => _StoryViewScreenState();
}

class _StoryViewScreenState extends State<StoryViewScreen> {
  Timer? _timer;
  double _progress = 0;

  @override
  void initState() {
    super.initState();
    _startProgress();
  }

  void _startProgress() {
    final controller = Get.find<StoryController>();
    final duration = controller.currentStory?.duration ?? 5;
    _progress = 0;
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(milliseconds: 50), (timer) {
      setState(() {
        _progress += 0.05 / duration;
      });
      if (_progress >= 1) {
        timer.cancel();
        controller.nextStory();
        if (controller.currentIndex == controller.stories.length - 1 && _progress >= 1) {
          Get.back();
        } else {
          _startProgress();
        }
      }
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: GetBuilder<StoryController>(
        builder: (controller) {
          final story = controller.currentStory;
          if (story == null) return const SizedBox.shrink();

          return GestureDetector(
            onTapDown: (details) {
              final width = MediaQuery.of(context).size.width;
              if (details.globalPosition.dx < width / 2) {
                controller.previousStory();
              } else {
                controller.nextStory();
              }
              _startProgress();
            },
            child: Stack(
              fit: StackFit.expand,
              children: [
                story.image != null
                    ? Image.network(story.image!, fit: BoxFit.cover)
                    : Container(color: Colors.grey),
                Positioned(
                  top: 40,
                  left: 10,
                  right: 10,
                  child: Column(
                    children: [
                      LinearProgressIndicator(
                        value: _progress,
                        backgroundColor: Colors.white.withOpacity(0.3),
                        valueColor: const AlwaysStoppedAnimation(Colors.white),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          CircleAvatar(
                            radius: 20,
                            backgroundColor: Colors.grey,
                          ),
                          const SizedBox(width: 10),
                          Text(
                            story.title ?? 'Restaurant',
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                Positioned(
                  top: 40,
                  right: 10,
                  child: IconButton(
                    icon: const Icon(Icons.close, color: Colors.white),
                    onPressed: () => Get.back(),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
