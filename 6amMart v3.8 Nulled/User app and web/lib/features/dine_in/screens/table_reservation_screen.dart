import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../common/widgets/custom_app_bar.dart';
import '../../../common/widgets/custom_button.dart';
import '../../../util/dimensions.dart';
import '../../../util/styles.dart';
import '../../store/domain/models/store_model.dart';
import '../controllers/dine_in_controller.dart';
import '../domain/models/store_table_model.dart';

class TableReservationScreen extends StatefulWidget {
  final Store store;
  const TableReservationScreen({Key? key, required this.store}) : super(key: key);

  @override
  State<TableReservationScreen> createState() => _TableReservationScreenState();
}

class _TableReservationScreenState extends State<TableReservationScreen> {
  int _guests = 2;
  final TextEditingController _specialRequestController = TextEditingController();

  @override
  void initState() {
    super.initState();
    Get.find<DineInController>().getTables(widget.store.id!);
  }

  @override
  void dispose() {
    _specialRequestController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: 'reserve_table'.tr),
      body: GetBuilder<DineInController>(
        builder: (controller) {
          return Column(
            children: [
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('select_date'.tr, style: robotoMedium),
                      const SizedBox(height: Dimensions.paddingSizeSmall),
                      InkWell(
                        onTap: () async {
                          final date = await showDatePicker(
                            context: context,
                            initialDate: DateTime.now(),
                            firstDate: DateTime.now(),
                            lastDate: DateTime.now().add(const Duration(days: 30)),
                          );
                          if (date != null) {
                            controller.selectDate(date);
                          }
                        },
                        child: Container(
                          padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                          decoration: BoxDecoration(
                            border: Border.all(color: Colors.grey),
                            borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.calendar_today),
                              const SizedBox(width: Dimensions.paddingSizeSmall),
                              Text(
                                controller.selectedDate != null
                                    ? controller.selectedDate!.toIso8601String().split('T')[0]
                                    : 'tap_to_select_date'.tr,
                                style: robotoRegular,
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: Dimensions.paddingSizeDefault),
                      Text('select_time'.tr, style: robotoMedium),
                      const SizedBox(height: Dimensions.paddingSizeSmall),
                      InkWell(
                        onTap: () async {
                          final time = await showTimePicker(
                            context: context,
                            initialTime: TimeOfDay.now(),
                          );
                          if (time != null) {
                            controller.selectTime('${time.hour.toString().padLeft(2, '0')}:${time.minute.toString().padLeft(2, '0')}:00');
                          }
                        },
                        child: Container(
                          padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                          decoration: BoxDecoration(
                            border: Border.all(color: Colors.grey),
                            borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.access_time),
                              const SizedBox(width: Dimensions.paddingSizeSmall),
                              Text(
                                controller.selectedTime ?? 'tap_to_select_time'.tr,
                                style: robotoRegular,
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: Dimensions.paddingSizeDefault),
                      Text('number_of_guests'.tr, style: robotoMedium),
                      const SizedBox(height: Dimensions.paddingSizeSmall),
                      Row(
                        children: [
                          IconButton(
                            onPressed: _guests > 1 ? () => setState(() => _guests--) : null,
                            icon: const Icon(Icons.remove_circle_outline),
                          ),
                          Text('$_guests', style: robotoMedium.copyWith(fontSize: 18)),
                          IconButton(
                            onPressed: () => setState(() => _guests++),
                            icon: const Icon(Icons.add_circle_outline),
                          ),
                        ],
                      ),
                      const SizedBox(height: Dimensions.paddingSizeDefault),
                      Text('select_table'.tr, style: robotoMedium),
                      const SizedBox(height: Dimensions.paddingSizeSmall),
                      controller.tables.isEmpty
                          ? Text('no_tables_available'.tr, style: robotoRegular)
                          : Wrap(
                              spacing: Dimensions.paddingSizeSmall,
                              runSpacing: Dimensions.paddingSizeSmall,
                              children: controller.tables.map((table) {
                                final isSelected = controller.selectedTable?.id == table.id;
                                return ChoiceChip(
                                  label: Text('${table.tableNumber} (${table.capacity}p)'),
                                  selected: isSelected,
                                  onSelected: (_) => controller.selectTable(table),
                                );
                              }).toList(),
                            ),
                      const SizedBox(height: Dimensions.paddingSizeDefault),
                      Text('special_request'.tr, style: robotoMedium),
                      const SizedBox(height: Dimensions.paddingSizeSmall),
                      TextField(
                        controller: _specialRequestController,
                        decoration: InputDecoration(
                          hintText: 'optional'.tr,
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                          ),
                        ),
                        maxLines: 2,
                      ),
                    ],
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                child: CustomButton(
                  buttonText: 'confirm_reservation'.tr,
                  isLoading: controller.isLoading,
                  onPressed: controller.selectedTable == null || controller.selectedDate == null || controller.selectedTime == null
                      ? null
                      : () async {
                          bool success = await controller.bookTable(
                            widget.store.id!,
                            _guests,
                            _specialRequestController.text.isEmpty ? null : _specialRequestController.text,
                          );
                          if (success) {
                            Get.back();
                          }
                        },
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}
