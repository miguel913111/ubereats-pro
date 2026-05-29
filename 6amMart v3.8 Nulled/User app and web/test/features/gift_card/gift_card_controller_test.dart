import 'package:flutter_test/flutter_test.dart';
import 'package:get/get.dart';
import 'package:nexofood_user/features/gift_card/controllers/gift_card_controller.dart';
import 'package:nexofood_user/features/gift_card/domain/models/gift_card_model.dart';
import 'package:nexofood_user/features/gift_card/domain/models/gift_card_usage_model.dart';
import 'package:nexofood_user/features/gift_card/domain/services/gift_card_service_interface.dart';

class MockGiftCardService implements GiftCardServiceInterface {
  @override
  Future<List<GiftCardModel>> getList() async {
    return [
      GiftCardModel(id: 1, code: 'GIFT2025', amount: 10.0),
    ];
  }

  @override
  Future<Response> apply(String code) async {
    if (code == 'GIFT2025') {
      return Response(statusCode: 200, body: {'gift_card': {'id': 1, 'code': 'GIFT2025', 'amount': 10.0}});
    }
    return Response(statusCode: 400, body: {'message': 'Invalid code'});
  }

  @override
  Future<dynamic> purchase(int giftCardId, String paymentMethod) async {
    return {'success': true};
  }

  @override
  Future<dynamic> redeem(String code) async {
    return {'success': true};
  }

  @override
  Future<List<GiftCardUsageModel>> getMyGiftCards() async {
    return [];
  }
}

void main() {
  setUp(() {
    Get.reset();
  });

  test('GiftCardController initializes empty', () {
    final controller = GiftCardController(giftCardService: MockGiftCardService());
    expect(controller.giftCardList, isEmpty);
    expect(controller.isLoading, false);
    expect(controller.appliedGiftCard, isNull);
  });

  test('GiftCardController getGiftCardList populates list', () async {
    final controller = GiftCardController(giftCardService: MockGiftCardService());
    await controller.getGiftCardList();
    expect(controller.giftCardList, isNotEmpty);
    expect(controller.giftCardList.first.code, 'GIFT2025');
    expect(controller.giftCardList.first.amount, 10.0);
  });

  test('GiftCardController apply valid code sets appliedGiftCard', () async {
    final controller = GiftCardController(giftCardService: MockGiftCardService());
    await controller.applyGiftCard('GIFT2025');
    expect(controller.appliedGiftCard, isNotNull);
    expect(controller.appliedGiftCard!.amount, 10.0);
  });

  test('GiftCardController apply invalid code leaves appliedGiftCard null', () async {
    final controller = GiftCardController(giftCardService: MockGiftCardService());
    await controller.applyGiftCard('INVALID');
    expect(controller.appliedGiftCard, isNull);
  });
}
