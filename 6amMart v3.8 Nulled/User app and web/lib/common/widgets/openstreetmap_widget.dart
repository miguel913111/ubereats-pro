import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart' as latlong2;

/// OpenStreetMap widget that replaces GoogleMap from google_maps_flutter.
/// Provides a simplified interface compatible with existing code.
class OpenStreetMapWidget extends StatefulWidget {
  final latlong2.LatLng initialPosition;
  final double zoom;
  final List<OSMMarker> markers;
  final List<OSMPolyline> polylines;
  final List<OSMPolygon> polygons;
  final bool myLocationEnabled;
  final bool myLocationButtonEnabled;
  final MapType mapType;
  final Function(latlong2.LatLng)? onTap;
  final Function(latlong2.LatLng)? onCameraMove;
  final VoidCallback? onMapCreated;
  final EdgeInsets padding;

  const OpenStreetMapWidget({
    Key? key,
    required this.initialPosition,
    this.zoom = 14.0,
    this.markers = const [],
    this.polylines = const [],
    this.polygons = const [],
    this.myLocationEnabled = false,
    this.myLocationButtonEnabled = false,
    this.mapType = MapType.normal,
    this.onTap,
    this.onCameraMove,
    this.onMapCreated,
    this.padding = EdgeInsets.zero,
  }) : super(key: key);

  @override
  State<OpenStreetMapWidget> createState() => _OpenStreetMapWidgetState();
}

class _OpenStreetMapWidgetState extends State<OpenStreetMapWidget> {
  late MapController _mapController;

  @override
  void initState() {
    super.initState();
    _mapController = MapController();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      widget.onMapCreated?.call();
    });
  }

  @override
  void dispose() {
    _mapController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return FlutterMap(
      mapController: _mapController,
      options: MapOptions(
        initialCenter: widget.initialPosition,
        initialZoom: widget.zoom,
        onTap: (tapPosition, point) {
          widget.onTap?.call(point);
        },
        onPositionChanged: (position, hasGesture) {
          if (position.center != null) {
            widget.onCameraMove?.call(position.center!);
          }
        },
      ),
      children: [
        TileLayer(
          urlTemplate: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
          subdomains: const ['a', 'b', 'c'],
          userAgentPackageName: 'com.nexofood.user',
        ),
        if (widget.polylines.isNotEmpty)
          PolylineLayer(
            polylines: widget.polylines.map((p) => Polyline(
              points: p.points,
              color: p.color,
              strokeWidth: p.width,
            )).toList(),
          ),
        if (widget.polygons.isNotEmpty)
          PolygonLayer(
            polygons: widget.polygons.map((p) => Polygon(
              points: p.points,
              color: p.fillColor.withOpacity(0.3),
              borderColor: p.strokeColor,
              borderStrokeWidth: p.strokeWidth.toDouble(),
            )).toList(),
          ),
        MarkerLayer(
          markers: widget.markers.map((m) => Marker(
            point: m.position,
            width: m.icon != null ? 40 : 80,
            height: m.icon != null ? 40 : 80,
            child: m.icon ?? (m.infoWindow?.title != null
              ? GestureDetector(
                  onTap: m.onTap,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(4),
                      boxShadow: [
                        BoxShadow(color: Colors.black26, blurRadius: 4),
                      ],
                    ),
                    child: Text(
                      m.infoWindow!.title!,
                      style: const TextStyle(fontSize: 12),
                    ),
                  ),
                )
              : const Icon(Icons.location_on, color: Colors.red, size: 40)),
          )).toList(),
        ),
      ],
    );
  }

  void animateCamera(OSMCameraUpdate update) {
    if (update.target != null) {
      _mapController.move(update.target!, update.zoom ?? _mapController.camera.zoom);
    }
  }
}

class OSMMarker {
  final latlong2.LatLng position;
  final String? markerId;
  final OSMInfoWindow? infoWindow;
  final Widget? icon;
  final VoidCallback? onTap;

  OSMMarker({
    required this.position,
    this.markerId,
    this.infoWindow,
    this.icon,
    this.onTap,
  });
}

class OSMInfoWindow {
  final String? title;
  final String? snippet;

  OSMInfoWindow({this.title, this.snippet});
}

class OSMPolyline {
  final String? polylineId;
  final List<latlong2.LatLng> points;
  final Color color;
  final double width;

  OSMPolyline({
    this.polylineId,
    required this.points,
    this.color = Colors.blue,
    this.width = 4.0,
  });
}

class OSMPolygon {
  final String? polygonId;
  final List<latlong2.LatLng> points;
  final Color fillColor;
  final Color strokeColor;
  final double strokeWidth;

  OSMPolygon({
    this.polygonId,
    required this.points,
    this.fillColor = Colors.red,
    this.strokeColor = Colors.red,
    this.strokeWidth = 2,
  });
}

class OSMCameraUpdate {
  final latlong2.LatLng? target;
  final double? zoom;

  OSMCameraUpdate({this.target, this.zoom});

  factory OSMCameraUpdate.newLatLng(latlong2.LatLng target) {
    return OSMCameraUpdate(target: target);
  }

  factory OSMCameraUpdate.newLatLngZoom(latlong2.LatLng target, double zoom) {
    return OSMCameraUpdate(target: target, zoom: zoom);
  }
}

enum MapType { normal, satellite, terrain, hybrid }

class OSMMapController {
  MapController? _internal;

  void attach(MapController controller) {
    _internal = controller;
  }

  void move(latlong2.LatLng target, double zoom) {
    _internal?.move(target, zoom);
  }
}
