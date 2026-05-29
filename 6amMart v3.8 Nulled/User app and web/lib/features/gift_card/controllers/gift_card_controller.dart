import 'package:get/get.dart';
import '../../../../common/widgets/custom_snackbar.dart';
import '../domain/models/gift_card_model.dart';
import '../domain/models/gift_card_usage_model.dart';
import '../domain/services/gift_card_service_interface.dart';

class GiftCardController extends GetxController implements GetxService {
  final GiftCardServiceInterface giftCardService;

  GiftCardController({required this.giftCardService});

  List<GiftCardModel> _giftCardList = [];
  List<GiftCardModel> get giftCardList => _giftCardList;

  List<GiftCardUsageModel> _myGiftCards = [];
  List<GiftCardUsageModel> get myGiftCards => _myGiftCards;

  GiftCardModel? _appliedGiftCard;
  GiftCardModel? get appliedGiftCard => _appliedGiftCard;

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  Future<void> getGiftCardList() async {
    _isLoading = true;
    update();
    _giftCardList = await giftCardService.getList();
    _isLoading = false;
    update();
  }

  Future<void> applyGiftCard(String code) async {
    _isLoading = true;
    update();
    Response response = await giftCardService.apply(code);
    if (response.statusCode == 200) {
      _appliedGiftCard = GiftCardModel.fromJson(response.body['gift_card']);
      showCustomSnackBar(response.body['message'], isError: false);
    } else {
      showCustomSnackBar(response.statusText);
    }
    _isLoading = false;
    update();
  }

  Future<void> redeemGiftCard(String code) async {
    _isLoading = true;
    update();
    Response response = await giftCardService.redeem(code);
    if (response.statusCode == 200) {
      showCustomSnackBar(response.body['message'], isError: false);
    } else {
      showCustomSnackBar(response.statusText);
    }
    _isLoading = false;
    update();
  }

  Future<void> getMyGiftCards() async {
    _isLoading = true;
    update();
    _myGiftCards = await giftCardService.getMyGiftCards();
    _isLoading = false;
    update();
  }
}
