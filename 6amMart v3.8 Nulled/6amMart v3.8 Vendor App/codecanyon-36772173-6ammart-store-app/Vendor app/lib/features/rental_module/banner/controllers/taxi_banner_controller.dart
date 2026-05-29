import 'package:get/get.dart';
import 'package:nexofood_vendor/features/rental_module/banner/domain/services/taxi_banner_service_interface.dart';

class TaxiBannerController extends GetxController implements GetxService {
  final TaxiBannerServiceInterface taxiBannerServiceInterface;
  TaxiBannerController({required this.taxiBannerServiceInterface});

}