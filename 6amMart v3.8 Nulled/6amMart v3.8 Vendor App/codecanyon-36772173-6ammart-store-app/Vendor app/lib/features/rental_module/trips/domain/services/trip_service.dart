import 'package:nexofood_vendor/features/rental_module/trips/domain/repositories/trip_repository_interface.dart';
import 'package:nexofood_vendor/features/rental_module/trips/domain/services/trip_service_interface.dart';

class TripService implements TripServiceInterface{
  final TripRepositoryInterface tripRepositoryInterface;
  TripService({required this.tripRepositoryInterface});

}