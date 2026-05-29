/// Compatibility stubs for google_maps_flutter classes.
/// This allows code to compile while we gradually migrate to flutter_map.
library google_maps_compat;

import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/gestures.dart';
import 'package:latlong2/latlong.dart' show LatLng;

export 'package:latlong2/latlong.dart' show LatLng;

class GoogleMapController {
  Future<void> animateCamera(CameraUpdate update) async {}
  Future<void> moveCamera(CameraUpdate update) async {}
  Future<double> getZoomLevel() async => 15;
  Future<LatLngBounds> getVisibleRegion() async => LatLngBounds(
    southwest: const LatLng(0, 0),
    northeast: const LatLng(0, 0),
  );
  void dispose() {}
}

class CameraPosition {
  final LatLng target;
  final double zoom;
  final double? tilt;
  final double? bearing;
  const CameraPosition({required this.target, this.zoom = 0, this.tilt, this.bearing});
}

class CameraUpdate {
  static CameraUpdate newCameraPosition(CameraPosition cameraPosition) => CameraUpdate();
  static CameraUpdate newLatLng(LatLng latLng) => CameraUpdate();
  static CameraUpdate newLatLngBounds(LatLngBounds bounds, double padding) => CameraUpdate();
  static CameraUpdate newLatLngZoom(LatLng latLng, double zoom) => CameraUpdate();
  static CameraUpdate zoomTo(double zoom) => CameraUpdate();
  static CameraUpdate zoomIn() => CameraUpdate();
  static CameraUpdate zoomOut() => CameraUpdate();
}

class MarkerId {
  final String value;
  const MarkerId(this.value);
}

class InfoWindow {
  final String? title;
  final String? snippet;
  const InfoWindow({this.title, this.snippet});
}

class Marker {
  final MarkerId markerId;
  final LatLng position;
  final dynamic icon;
  final bool visible;
  final bool draggable;
  final int zIndex;
  final int? zIndexInt;
  final bool flat;
  final Offset anchor;
  final VoidCallback? onTap;
  final InfoWindow? infoWindow;
  const Marker({
    required this.markerId,
    required this.position,
    this.icon,
    this.visible = true,
    this.draggable = false,
    this.zIndex = 0,
    this.zIndexInt,
    this.flat = false,
    this.anchor = const Offset(0.5, 0.5),
    this.onTap,
    this.infoWindow,
    this.rotation,
    this.width,
    this.height,
  });
  final double? rotation;
  final int? width;
  final int? height;
}

class BitmapDescriptor {
  static Future<BitmapDescriptor> defaultMarkerWithHue(double hue) async => BitmapDescriptor();
  static Future<BitmapDescriptor> fromAssetImage(ImageConfiguration config, String assetName) async => BitmapDescriptor();
  static Future<BitmapDescriptor> asset(ImageConfiguration config, String assetName) async => BitmapDescriptor();
  static BitmapDescriptor fromBytes(dynamic bytes, {int? width, int? height}) => BitmapDescriptor();
  static BitmapDescriptor bytes(dynamic bytes, {int? width, int? height}) => BitmapDescriptor();
  static const double hueRed = 0.0;
  static const double hueBlue = 240.0;
  static const double hueGreen = 120.0;
  static const double hueYellow = 60.0;
  static const double hueCyan = 180.0;
  static const double hueMagenta = 300.0;
  static const double hueOrange = 30.0;
  static const double hueRose = 330.0;
  static const double hueViolet = 270.0;
}

class LatLngBounds {
  final LatLng southwest;
  final LatLng northeast;
  LatLngBounds({required this.southwest, required this.northeast});
}

class MinMaxZoomPreference {
  final double? minZoom;
  final double? maxZoom;
  const MinMaxZoomPreference(this.minZoom, this.maxZoom);
}

class PolygonId {
  final String value;
  const PolygonId(this.value);
}

class GoogleMap extends StatelessWidget {
  final CameraPosition initialCameraPosition;
  final MinMaxZoomPreference? minMaxZoomPreference;
  final bool zoomGesturesEnabled;
  final bool myLocationButtonEnabled;
  final bool myLocationEnabled;
  final bool zoomControlsEnabled;
  final bool indoorViewEnabled;
  final bool scrollGesturesEnabled;
  final bool rotateGesturesEnabled;
  final bool tiltGesturesEnabled;
  final bool compassEnabled;
  final bool mapToolbarEnabled;
  final Set<Marker> markers;
  final Set<Polyline> polylines;
  final Set<Polygon> polygons;
  final String? style;
  final EdgeInsets padding;
  final Set<Factory<OneSequenceGestureRecognizer>>? gestureRecognizers;
  final void Function(GoogleMapController)? onMapCreated;
  final void Function(LatLng)? onTap;
  final void Function(CameraPosition)? onCameraMove;
  final VoidCallback? onCameraIdle;
  final VoidCallback? onCameraMoveStarted;

  const GoogleMap({
    super.key,
    required this.initialCameraPosition,
    this.minMaxZoomPreference,
    this.zoomGesturesEnabled = true,
    this.myLocationButtonEnabled = true,
    this.myLocationEnabled = false,
    this.zoomControlsEnabled = true,
    this.indoorViewEnabled = false,
    this.scrollGesturesEnabled = true,
    this.rotateGesturesEnabled = true,
    this.tiltGesturesEnabled = true,
    this.compassEnabled = true,
    this.mapToolbarEnabled = true,
    this.markers = const {},
    this.polylines = const {},
    this.polygons = const {},
    this.style,
    this.padding = EdgeInsets.zero,
    this.gestureRecognizers,
    this.mapType,
    this.onMapCreated,
    this.onTap,
    this.onCameraMove,
    this.onCameraIdle,
    this.onCameraMoveStarted,
  });
  final MapType? mapType;

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.grey[200],
      child: const Center(
        child: Text('OpenStreetMap migration in progress'),
      ),
    );
  }
}

enum MapType { none, normal, satellite, terrain, hybrid }

class Polyline {
  final String polylineId;
  final List<LatLng> points;
  final Color color;
  final int width;
  const Polyline({
    required this.polylineId,
    required this.points,
    this.color = Colors.black,
    this.width = 5,
  });
}

class Polygon {
  final dynamic polygonId;
  final List<LatLng> points;
  final Color fillColor;
  final Color strokeColor;
  final int strokeWidth;
  const Polygon({
    required this.polygonId,
    required this.points,
    this.fillColor = Colors.black,
    this.strokeColor = Colors.black,
    this.strokeWidth = 5,
  });
}
