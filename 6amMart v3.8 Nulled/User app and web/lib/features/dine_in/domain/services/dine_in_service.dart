import '../../../store/domain/models/store_model.dart';
import '../models/store_table_model.dart';
import '../models/table_reservation_model.dart';
import '../repositories/dine_in_repository_interface.dart';
import 'dine_in_service_interface.dart';

class DineInService implements DineInServiceInterface {
  final DineInRepositoryInterface dineInRepository;

  DineInService({required this.dineInRepository});

  @override
  Future<List<Store>> getDineInStores({int? zoneId}) async {
    return await dineInRepository.getDineInStores(zoneId: zoneId);
  }

  @override
  Future<List<StoreTableModel>> getTables(int storeId) async {
    return await dineInRepository.getTables(storeId);
  }

  @override
  Future<dynamic> checkAvailability(int storeId, int storeTableId, String reservationDate, String reservationTime) async {
    return await dineInRepository.checkAvailability(storeId, storeTableId, reservationDate, reservationTime);
  }

  @override
  Future<dynamic> bookTable(int storeId, int storeTableId, String reservationDate, String reservationTime, int numberOfGuests, String? specialRequest) async {
    return await dineInRepository.bookTable(storeId, storeTableId, reservationDate, reservationTime, numberOfGuests, specialRequest);
  }

  @override
  Future<List<TableReservationModel>> getMyReservations() async {
    return await dineInRepository.getMyReservations();
  }

  @override
  Future<dynamic> cancelReservation(int id, String? reason) async {
    return await dineInRepository.cancelReservation(id, reason);
  }
}
