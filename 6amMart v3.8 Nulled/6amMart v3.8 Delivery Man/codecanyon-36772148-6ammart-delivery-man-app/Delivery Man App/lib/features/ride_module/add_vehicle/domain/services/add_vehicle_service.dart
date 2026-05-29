import 'package:image_picker/image_picker.dart';
import 'package:nexofood_delivery/api/api_client.dart';
import 'package:nexofood_delivery/features/ride_module/add_vehicle/domain/models/vehicle_body.dart';

import '../repositories/add_vehicle_repository_interface.dart';
import 'add_vehicle_service_interface.dart';

class AddVehicleService implements AddVehicleServiceInterface {
  final AddVehicleRepositoryInterface addVehicleRepositoryInterface;
  AddVehicleService({required this.addVehicleRepositoryInterface});
}
  