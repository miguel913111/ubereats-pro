import 'package:flutter_test/flutter_test.dart';
import 'package:get/get.dart';
import 'package:nexofood_user/features/dine_in/controllers/dine_in_controller.dart';
import 'package:nexofood_user/features/dine_in/domain/models/store_table_model.dart';
import 'package:nexofood_user/features/dine_in/domain/models/table_reservation_model.dart';
import 'package:nexofood_user/features/dine_in/domain/services/dine_in_service_interface.dart';
import 'package:nexofood_user/features/store/domain/models/store_model.dart';

class MockDineInService implements DineInServiceInterface {
  @override
  Future<List<Store>> getDineInStores({int? zoneId}) async {
    return [
      Store(id: 1, name: 'Test Restaurant', address: '123 Main St'),
    ];
  }

  @override
  Future<List<StoreTableModel>> getTables(int storeId) async {
    return [
      StoreTableModel(id: 1, storeId: storeId, tableNumber: 'A1', capacity: 4),
    ];
  }

  @override
  Future<dynamic> checkAvailability(int storeId, int storeTableId, String reservationDate, String reservationTime) async {
    return {'available': true};
  }

  @override
  Future<dynamic> bookTable(int storeId, int storeTableId, String reservationDate, String reservationTime, int numberOfGuests, String? specialRequest) async {
    return {'success': true, 'reservation': {'id': 1}};
  }

  @override
  Future<List<TableReservationModel>> getMyReservations() async {
    return [
      TableReservationModel(id: 1, storeId: 1, status: 'pending'),
    ];
  }

  @override
  Future<dynamic> cancelReservation(int id, String? reason) async {
    return {'success': true};
  }
}

void main() {
  setUp(() {
    Get.reset();
  });

  test('DineInController initializes with empty stores list', () {
    final controller = DineInController(dineInService: MockDineInService());
    expect(controller.dineInStores, isEmpty);
    expect(controller.isLoading, false);
  });

  test('DineInController getDineInStores populates stores', () async {
    final controller = DineInController(dineInService: MockDineInService());
    await controller.getDineInStores();
    expect(controller.dineInStores, isNotEmpty);
    expect(controller.dineInStores.first.name, 'Test Restaurant');
  });

  test('DineInController getTables populates tables', () async {
    final controller = DineInController(dineInService: MockDineInService());
    await controller.getTables(1);
    expect(controller.tables, isNotEmpty);
    expect(controller.tables.first.tableNumber, 'A1');
  });
}
