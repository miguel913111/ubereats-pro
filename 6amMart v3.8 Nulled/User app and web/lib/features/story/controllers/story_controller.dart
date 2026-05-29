import 'package:get/get.dart';
import '../../../../api/api_client.dart';
import '../../../../helper/auth_helper.dart';
import '../domain/models/story_model.dart';

class StoryController extends GetxController implements GetxService {
  final ApiClient apiClient;

  StoryController({required this.apiClient});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<StoryModel> _stories = [];
  List<StoryModel> get stories => _stories;

  StoryModel? _currentStory;
  StoryModel? get currentStory => _currentStory;

  int _currentIndex = 0;
  int get currentIndex => _currentIndex;

  Future<void> getStories({int? storeId}) async {
    if (!AuthHelper.isLoggedIn()) {
      return;
    }
    _isLoading = true;
    update();
    String url = '/api/v1/customer/stories/list';
    if (storeId != null) {
      url += '?store_id=$storeId';
    }
    Response response = await apiClient.getData(url);
    if (response.statusCode == 200) {
      List<dynamic> data = response.body;
      _stories = data.map((json) => StoryModel.fromJson(json)).toList();
    }
    _isLoading = false;
    update();
  }

  void setCurrentStory(int index) {
    _currentIndex = index;
    _currentStory = _stories[index];
    update();
  }

  void nextStory() {
    if (_currentIndex < _stories.length - 1) {
      _currentIndex++;
      _currentStory = _stories[_currentIndex];
      update();
    }
  }

  void previousStory() {
    if (_currentIndex > 0) {
      _currentIndex--;
      _currentStory = _stories[_currentIndex];
      update();
    }
  }
}
