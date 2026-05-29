import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import '../../../common/widgets/custom_button_widget.dart';
import '../../../common/widgets/custom_snackbar_widget.dart';
import '../controllers/store_delivery_zone_controller.dart';

class MapZoneScreen extends StatefulWidget {
  const MapZoneScreen({Key? key}) : super(key: key);

  @override
  State<MapZoneScreen> createState() => _MapZoneScreenState();
}

class _MapZoneScreenState extends State<MapZoneScreen> {
  final List<LatLng> _polygonPoints = [];
  final MapController _mapController = MapController();
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _chargeController = TextEditingController();

  @override
  void dispose() {
    _mapController.dispose();
    _nameController.dispose();
    _chargeController.dispose();
    super.dispose();
  }

  void _onMapTap(LatLng position) {
    setState(() {
      _polygonPoints.add(position);
    });
  }

  void _clearPoints() {
    setState(() {
      _polygonPoints.clear();
    });
  }

  Future<void> _saveZone() async {
    if (_polygonPoints.length < 3) {
      showCustomSnackBar('Please add at least 3 points');
      return;
    }
    if (_nameController.text.isEmpty || _chargeController.text.isEmpty) {
      showCustomSnackBar('Please fill name and delivery charge');
      return;
    }
    final coordinates = _polygonPoints.map((p) => {'lat': p.latitude, 'lng': p.longitude}).toList();
    final controller = Get.find<StoreDeliveryZoneController>();
    Response response = await controller.createZone({
      'name': _nameController.text,
      'delivery_charge': _chargeController.text,
      'coordinates': coordinates,
    });
    if (response.statusCode == 200) {
      showCustomSnackBar(response.body['message'] ?? 'Zone saved', isError: false);
      Get.back(result: true);
    } else {
      showCustomSnackBar(response.statusText ?? 'Failed to save zone');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('draw_delivery_zone'.tr),
        actions: [
          TextButton(
            onPressed: _clearPoints,
            child: Text('clear'.tr, style: const TextStyle(color: Colors.white)),
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(8),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _nameController,
                    decoration: InputDecoration(
                      labelText: 'zone_name'.tr,
                      border: const OutlineInputBorder(),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: TextField(
                    controller: _chargeController,
                    decoration: InputDecoration(
                      labelText: 'delivery_charge'.tr,
                      border: const OutlineInputBorder(),
                    ),
                    keyboardType: TextInputType.number,
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: FlutterMap(
              mapController: _mapController,
              options: MapOptions(
                initialCenter: const LatLng(38.7223, -9.1393),
                initialZoom: 13,
                onTap: (tapPosition, point) => _onMapTap(point),
              ),
              children: [
                TileLayer(
                  urlTemplate: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                  subdomains: const ['a', 'b', 'c'],
                  userAgentPackageName: 'com.nexofood.vendor',
                ),
                if (_polygonPoints.length >= 3)
                  PolygonLayer(
                    polygons: [
                      Polygon(
                        points: _polygonPoints,
                        color: Colors.blue.withOpacity(0.3),
                        borderColor: Colors.blue,
                        borderStrokeWidth: 2,
                      ),
                    ],
                  ),
                MarkerLayer(
                  markers: _polygonPoints.map((p) => Marker(
                    point: p,
                    width: 20,
                    height: 20,
                    child: Container(
                      decoration: const BoxDecoration(
                        color: Colors.red,
                        shape: BoxShape.circle,
                      ),
                    ),
                  )).toList(),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(8),
            child: CustomButtonWidget(
              buttonText: 'save_zone'.tr,
              onPressed: _saveZone,
            ),
          ),
        ],
      ),
    );
  }
}
