import 'package:get/get_connect/http/src/response/response.dart';
import 'package:nexofood_user/common/models/latlng_compat.dart';
import 'package:nexofood_user/features/location/domain/models/zone_response_model.dart';
import 'package:nexofood_user/interfaces/repository_interface.dart';

abstract class LocationRepositoryInterface<T> implements RepositoryInterface {
  Future<String> getAddressFromGeocode(LatLng latLng);
  Future<ZoneResponseModel> getZone(String? lat, String? lng, {bool handleError = false});
  Future<Response> searchLocation(String text);
  String? getUserAddress();
}