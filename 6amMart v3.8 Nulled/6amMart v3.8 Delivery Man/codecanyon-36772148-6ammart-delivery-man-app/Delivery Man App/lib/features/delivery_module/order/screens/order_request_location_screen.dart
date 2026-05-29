import 'package:expandable_bottom_sheet/expandable_bottom_sheet.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:get/get.dart';
import 'package:nexofood_delivery/features/delivery_module/order/controllers/order_controller.dart';
import 'package:nexofood_delivery/features/delivery_module/order/domain/models/order_model.dart';
import 'package:nexofood_delivery/features/delivery_module/order/widgets/request_location_card_widget.dart';
import 'package:nexofood_delivery/features/profile/controllers/profile_controller.dart';
import 'package:nexofood_delivery/common/widgets/custom_app_bar_widget.dart';

class OrderRequestLocationScreen extends StatefulWidget {
  final OrderModel orderModel;
  final OrderController orderController;
  const OrderRequestLocationScreen({super.key, required this.orderModel, required this.orderController});

  @override
  State<OrderRequestLocationScreen> createState() => _OrderRequestLocationScreenState();
}

class _OrderRequestLocationScreenState extends State<OrderRequestLocationScreen> {
  final MapController _mapController = MapController();
  final List<Marker> _markers = [];
  GlobalKey<ExpandableBottomSheetState> key = GlobalKey<ExpandableBottomSheetState>();

  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(milliseconds: 600), () {
      key.currentState?.expand();
    });
    WidgetsBinding.instance.addPostFrameCallback((_) {
      setMarker(widget.orderModel, widget.orderModel.orderType == 'parcel');
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBarWidget(
        title: 'new_order_request'.tr,
        subtitle: '${'order_id'.tr}: #${widget.orderModel.id}',
      ),
      body: SafeArea(
        child: ExpandableBottomSheet(
          key: key,
          background: Stack(children: [
            FlutterMap(
              mapController: _mapController,
              options: MapOptions(
                initialCenter: LatLng(
                  double.parse(widget.orderModel.deliveryAddress?.latitude ?? '0'),
                  double.parse(widget.orderModel.deliveryAddress?.longitude ?? '0'),
                ),
                initialZoom: 16,
                minZoom: 0,
                maxZoom: 16,
              ),
              children: [
                TileLayer(
                  urlTemplate: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                  subdomains: const ['a', 'b', 'c'],
                  userAgentPackageName: 'com.nexofood.delivery',
                ),
                MarkerLayer(markers: _markers),
              ],
            ),
          ]),
          persistentContentHeight: 100,
          expandableContent: RequestLocationCardWidget(
            orderModel: widget.orderModel, orderController: widget.orderController,
          ),
        ),
      ),
    );
  }

  void setMarker(OrderModel orderModel, bool parcel) async {
    try {
      double deliveryLat = double.parse(orderModel.deliveryAddress?.latitude ?? '0');
      double deliveryLng = double.parse(orderModel.deliveryAddress?.longitude ?? '0');
      double storeLat = double.parse(orderModel.storeLat ?? '0');
      double storeLng = double.parse(orderModel.storeLng ?? '0');
      double receiverLat = double.parse(orderModel.receiverDetails?.latitude ?? '0');
      double receiverLng = double.parse(orderModel.receiverDetails?.longitude ?? '0');
      double deliveryManLat = Get.find<ProfileController>().recordLocationBody?.latitude ?? 0;
      double deliveryManLng = Get.find<ProfileController>().recordLocationBody?.longitude ?? 0;

      List<LatLng> points = [];

      setState(() {
        _markers.clear();

        // Add destination marker
        if (orderModel.deliveryAddress != null) {
          _markers.add(Marker(
            point: LatLng(deliveryLat, deliveryLng),
            width: 40,
            height: 40,
            child: Icon(Icons.location_on, color: parcel ? Colors.orange : Colors.red, size: 40),
          ));
          points.add(LatLng(deliveryLat, deliveryLng));
        }

        // Add receiver marker for parcel order
        if (parcel && orderModel.receiverDetails != null) {
          _markers.add(Marker(
            point: LatLng(receiverLat, receiverLng),
            width: 40,
            height: 40,
            child: Icon(Icons.person_pin_circle, color: Colors.blue, size: 40),
          ));
          points.add(LatLng(receiverLat, receiverLng));
        }

        // Add store marker for normal order
        if (!parcel && orderModel.storeLat != null && orderModel.storeLng != null) {
          _markers.add(Marker(
            point: LatLng(storeLat, storeLng),
            width: 40,
            height: 40,
            child: Icon(Icons.store, color: Colors.green, size: 40),
          ));
          points.add(LatLng(storeLat, storeLng));
        }

        // Add delivery boy marker
        if (Get.find<ProfileController>().recordLocationBody != null) {
          _markers.add(Marker(
            point: LatLng(deliveryManLat, deliveryManLng),
            width: 40,
            height: 40,
            child: Icon(Icons.delivery_dining, color: Colors.purple, size: 40),
          ));
          points.add(LatLng(deliveryManLat, deliveryManLng));
        }
      });

      if (points.length > 1) {
        _mapController.fitCamera(CameraFit.bounds(
          bounds: LatLngBounds.fromPoints(points),
          padding: const EdgeInsets.all(50),
        ));
      }
    } catch (e) {
      if (kDebugMode) {
        print('Error setting markers: $e');
      }
    }
  }
}
