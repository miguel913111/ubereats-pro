import 'package:get/get.dart';
import 'package:nexofood_vendor/api/api_client.dart';

class StoryController extends GetxController implements GetxService {
  final ApiClient apiClient;

  StoryController({required this.apiClient});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<dynamic> _stories = [];
  List<dynamic> get stories => _stories;

  Future<void> getStories() async {
    _isLoading = true;
    update();
    Response response = await apiClient.getData('/api/v1/vendor/stories/list');
    if (response.statusCode == 200) {
      _stories = response.body;
    }
    _isLoading = false;
    update();
  }

  Future<Response> createStory(Map<String, dynamic> data) async {
    _isLoading = true;
    update();
    Response response = await apiClient.postData('/api/v1/vendor/stories/store', data);
    _isLoading = false;
    update();
    return response;
  }

  Future<Response> deleteStory(int id) async {
    return await apiClient.deleteData('/api/v1/vendor/stories/delete/$id');
  }
}
