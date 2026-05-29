import 'dart:math';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:get/get.dart';
import 'package:nexofood_delivery/features/delivery_module/order/controllers/order_controller.dart';
import 'package:nexofood_delivery/features/delivery_module/order/domain/models/order_model.dart';
import 'package:nexofood_delivery/features/profile/controllers/profile_controller.dart';
import 'package:nexofood_delivery/util/dimensions.dart';
import 'package:nexofood_delivery/util/images.dart';
import 'package:nexofood_delivery/common/widgets/custom_app_bar_widget.dart';
import 'package:nexofood_delivery/features/delivery_module/order/widgets/location_card_widget.dart';

class OrderLocationScreen extends StatefulWidget {
  final OrderModel orderModel;
  final OrderController orderController;
  final int index;
  final Function onTap;
  const OrderLocationScreen({super.key, required this.orderModel, required this.orderController, required this.index, required this.onTap});

  @override
  State<OrderLocationScreen> createState() => _OrderLocationScreenState();
}

class _OrderLocationScreenState extends State<OrderLocationScreen> {
  final MapController _mapController = MapController();
  final List<Marker> _markers = [];

  @override
  Widget build(BuildContext context) {
    bool parcel = widget.orderModel.orderType == 'parcel';

    return Scaffold(
      appBar: CustomAppBarWidget(title: 'order_location'.tr),
      body: SafeArea(
        child: Stack(children: [
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

          Positioned(
            bottom: Dimensions.paddingSizeSmall, left: Dimensions.paddingSizeSmall, right: Dimensions.paddingSizeSmall,
            child: LocationCardWidget(
              orderModel: widget.orderModel, orderController: widget.orderController,
              onTap: widget.onTap, index: widget.index,
            ),
          ),
        ]),
      ),
    );
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      setMarker(widget.orderModel, widget.orderModel.orderType == 'parcel');
    });
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
