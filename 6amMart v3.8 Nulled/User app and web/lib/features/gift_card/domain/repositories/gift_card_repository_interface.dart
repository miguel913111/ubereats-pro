import '../models/gift_card_model.dart';
import '../models/gift_card_usage_model.dart';

abstract class GiftCardRepositoryInterface {
  Future<List<GiftCardModel>> getList();
  Future<dynamic> apply(String code);
  Future<dynamic> purchase(int giftCardId, String paymentMethod);
  Future<dynamic> redeem(String code);
  Future<List<GiftCardUsageModel>> getMyGiftCards();
}
