import 'package:shared_preferences/shared_preferences.dart';
import '../../../../api/api_client.dart';
import '../models/gift_card_model.dart';
import '../models/gift_card_usage_model.dart';
import 'gift_card_repository_interface.dart';

class GiftCardRepository implements GiftCardRepositoryInterface {
  final ApiClient apiClient;
  final SharedPreferences sharedPreferences;

  GiftCardRepository({required this.apiClient, required this.sharedPreferences});

  @override
  Future<List<GiftCardModel>> getList() async {
    final response = await apiClient.getData('/api/v1/customer/gift-card/list');
    if (response.statusCode == 200) {
      List<dynamic> data = response.body;
      return data.map((json) => GiftCardModel.fromJson(json)).toList();
    }
    return [];
  }

  @override
  Future<dynamic> apply(String code) async {
    return await apiClient.postData('/api/v1/customer/gift-card/apply', {'code': code});
  }

  @override
  Future<dynamic> purchase(int giftCardId, String paymentMethod) async {
    return await apiClient.postData('/api/v1/customer/gift-card/purchase', {
      'gift_card_id': giftCardId,
      'payment_method': paymentMethod,
    });
  }

  @override
  Future<dynamic> redeem(String code) async {
    return await apiClient.postData('/api/v1/customer/gift-card/redeem', {'code': code});
  }

  @override
  Future<List<GiftCardUsageModel>> getMyGiftCards() async {
    final response = await apiClient.getData('/api/v1/customer/gift-card/my-gift-cards');
    if (response.statusCode == 200) {
      List<dynamic> data = response.body;
      return data.map((json) => GiftCardUsageModel.fromJson(json)).toList();
    }
    return [];
  }
}
