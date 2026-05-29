import 'package:shared_preferences/shared_preferences.dart';
import '../../../../api/api_client.dart';
import '../../../store/domain/models/store_model.dart';
import '../models/store_table_model.dart';
import '../models/table_reservation_model.dart';
import 'dine_in_repository_interface.dart';

class DineInRepository implements DineInRepositoryInterface {
  final ApiClient apiClient;
  final SharedPreferences sharedPreferences;

  DineInRepository({required this.apiClient, required this.sharedPreferences});

  @override
  Future<List<Store>> getDineInStores({int? zoneId}) async {
    String url = '/api/v1/customer/dine-in/stores';
    if (zoneId != null) {
      url += '?zone_id=$zoneId';
    }
    final response = await apiClient.getData(url);
    if (response.statusCode == 200) {
      List<dynamic> data = response.body;
      return data.map((json) => Store.fromJson(json)).toList();
    }
    return [];
  }

  @override
  Future<List<StoreTableModel>> getTables(int storeId) async {
    final response = await apiClient.getData('/api/v1/customer/dine-in/tables?store_id=$storeId');
    if (response.statusCode == 200) {
      List<dynamic> data = response.body;
      return data.map((json) => StoreTableModel.fromJson(json)).toList();
    }
    return [];
  }

  @override
  Future<dynamic> checkAvailability(int storeId, int storeTableId, String reservationDate, String reservationTime) async {
    return await apiClient.postData('/api/v1/customer/dine-in/check-availability', {
      'store_id': storeId,
      'store_table_id': storeTableId,
      'reservation_date': reservationDate,
      'reservation_time': reservationTime,
    });
  }

  @override
  Future<dynamic> bookTable(int storeId, int storeTableId, String reservationDate, String reservationTime, int numberOfGuests, String? specialRequest) async {
    return await apiClient.postData('/api/v1/customer/dine-in/book', {
      'store_id': storeId,
      'store_table_id': storeTableId,
      'reservation_date': reservationDate,
      'reservation_time': reservationTime,
      'number_of_guests': numberOfGuests,
      'special_request': specialRequest,
    });
  }

  @override
  Future<List<TableReservationModel>> getMyReservations() async {
    final response = await apiClient.getData('/api/v1/customer/dine-in/my-reservations');
    if (response.statusCode == 200) {
      List<dynamic> data = response.body;
      return data.map((json) => TableReservationModel.fromJson(json)).toList();
    }
    return [];
  }

  @override
  Future<dynamic> cancelReservation(int id, String? reason) async {
    return await apiClient.postData('/api/v1/customer/dine-in/cancel/$id', {
      'reason': reason,
    });
  }
}
