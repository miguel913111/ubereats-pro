import '../../../store/domain/models/store_model.dart';
import '../models/store_table_model.dart';
import '../models/table_reservation_model.dart';

abstract class DineInRepositoryInterface {
  Future<List<Store>> getDineInStores({int? zoneId});
  Future<List<StoreTableModel>> getTables(int storeId);
  Future<dynamic> checkAvailability(int storeId, int storeTableId, String reservationDate, String reservationTime);
  Future<dynamic> bookTable(int storeId, int storeTableId, String reservationDate, String reservationTime, int numberOfGuests, String? specialRequest);
  Future<List<TableReservationModel>> getMyReservations();
  Future<dynamic> cancelReservation(int id, String? reason);
}
