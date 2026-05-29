import 'package:get/get.dart';
import '../../../../common/widgets/custom_snackbar.dart';
import '../../store/domain/models/store_model.dart';
import '../domain/models/store_table_model.dart';
import '../domain/models/table_reservation_model.dart';
import '../domain/services/dine_in_service_interface.dart';

class DineInController extends GetxController implements GetxService {
  final DineInServiceInterface dineInService;

  DineInController({required this.dineInService});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<Store> _dineInStores = [];
  List<Store> get dineInStores => _dineInStores;

  List<StoreTableModel> _tables = [];
  List<StoreTableModel> get tables => _tables;

  List<TableReservationModel> _myReservations = [];
  List<TableReservationModel> get myReservations => _myReservations;

  StoreTableModel? _selectedTable;
  StoreTableModel? get selectedTable => _selectedTable;

  DateTime? _selectedDate;
  DateTime? get selectedDate => _selectedDate;

  String? _selectedTime;
  String? get selectedTime => _selectedTime;

  void selectTable(StoreTableModel table) {
    _selectedTable = table;
    update();
  }

  void selectDate(DateTime date) {
    _selectedDate = date;
    update();
  }

  void selectTime(String time) {
    _selectedTime = time;
    update();
  }

  Future<void> getDineInStores({int? zoneId}) async {
    _isLoading = true;
    update();
    _dineInStores = await dineInService.getDineInStores(zoneId: zoneId);
    _isLoading = false;
    update();
  }

  Future<void> getTables(int storeId) async {
    _isLoading = true;
    update();
    _tables = await dineInService.getTables(storeId);
    _isLoading = false;
    update();
  }

  Future<bool> checkAvailability(int storeId, int storeTableId, String reservationDate, String reservationTime) async {
    _isLoading = true;
    update();
    Response response = await dineInService.checkAvailability(storeId, storeTableId, reservationDate, reservationTime);
    _isLoading = false;
    update();
    if (response.statusCode == 200) {
      return response.body['available'] == true;
    }
    return false;
  }

  Future<bool> bookTable(int storeId, int guests, String? specialRequest) async {
    if (_selectedTable == null || _selectedDate == null || _selectedTime == null) {
      showCustomSnackBar('Please select table, date and time');
      return false;
    }
    _isLoading = true;
    update();
    Response response = await dineInService.bookTable(
      storeId,
      _selectedTable!.id!,
      _selectedDate!.toIso8601String().split('T')[0],
      _selectedTime!,
      guests,
      specialRequest,
    );
    _isLoading = false;
    update();
    if (response.statusCode == 200) {
      showCustomSnackBar(response.body['message'] ?? 'Table reserved successfully', isError: false);
      return true;
    } else {
      showCustomSnackBar(response.statusText);
      return false;
    }
  }

  Future<void> getMyReservations() async {
    _isLoading = true;
    update();
    _myReservations = await dineInService.getMyReservations();
    _isLoading = false;
    update();
  }

  Future<bool> cancelReservation(int id, String? reason) async {
    _isLoading = true;
    update();
    Response response = await dineInService.cancelReservation(id, reason);
    _isLoading = false;
    update();
    if (response.statusCode == 200) {
      showCustomSnackBar(response.body['message'] ?? 'Reservation cancelled', isError: false);
      await getMyReservations();
      return true;
    } else {
      showCustomSnackBar(response.statusText);
      return false;
    }
  }
}
