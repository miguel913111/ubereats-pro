import '../models/gift_card_model.dart';
import '../models/gift_card_usage_model.dart';
import '../repositories/gift_card_repository_interface.dart';
import 'gift_card_service_interface.dart';

class GiftCardService implements GiftCardServiceInterface {
  final GiftCardRepositoryInterface giftCardRepository;

  GiftCardService({required this.giftCardRepository});

  @override
  Future<List<GiftCardModel>> getList() async {
    return await giftCardRepository.getList();
  }

  @override
  Future<dynamic> apply(String code) async {
    return await giftCardRepository.apply(code);
  }

  @override
  Future<dynamic> purchase(int giftCardId, String paymentMethod) async {
    return await giftCardRepository.purchase(giftCardId, paymentMethod);
  }

  @override
  Future<dynamic> redeem(String code) async {
    return await giftCardRepository.redeem(code);
  }

  @override
  Future<List<GiftCardUsageModel>> getMyGiftCards() async {
    return await giftCardRepository.getMyGiftCards();
  }
}
