import 'package:get/get.dart';
import 'package:nexofood_vendor/features/rental_module/provider/domain/services/provider_service_interface.dart';

class ProviderController extends GetxController implements GetxService {
  final ProviderServiceInterface providerServiceInterface;
  ProviderController({required this.providerServiceInterface});

}