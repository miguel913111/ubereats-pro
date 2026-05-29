import 'package:geolocator/geolocator.dart';
import 'package:nexofood_user/common/controllers/theme_controller.dart';
import 'package:nexofood_user/common/widgets/custom_snackbar.dart';
import 'package:nexofood_user/common/widgets/footer_view.dart';
import 'package:nexofood_user/features/address/domain/models/address_model.dart';
import 'package:nexofood_user/features/location/controllers/location_controller.dart';
import 'package:nexofood_user/features/location/widgets/permission_dialog_widget.dart';
import 'package:nexofood_user/helper/address_helper.dart';
import 'package:nexofood_user/helper/responsive_helper.dart';
import 'package:nexofood_user/util/dimensions.dart';
import 'package:nexofood_user/util/styles.dart';
import 'package:nexofood_user/common/widgets/custom_app_bar.dart';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:get/get.dart';
import 'package:nexofood_user/common/widgets/menu_drawer.dart';
import 'package:nexofood_user/features/order/widgets/address_details_widget.dart';
import 'package:url_launcher/url_launcher_string.dart';

class MapScreen extends StatefulWidget {
  final AddressModel address;
  final bool fromStore;
  final bool isFood;
  final String storeName;
  const MapScreen({super.key, required this.address, this.fromStore = false, this.isFood = false, required this.storeName});

  @override
  MapScreenState createState() => MapScreenState();
}

class MapScreenState extends State<MapScreen> {
  late LatLng _latLng;
  final MapController _mapController = MapController();
  bool isHovered = false;
  List<Marker> _markers = [];

  @override
  void initState() {
    super.initState();
    _latLng = LatLng(double.parse(widget.address.latitude!), double.parse(widget.address.longitude!));
    _setMarker();
  }

  void onEntered(bool isHovered) {
    setState(() {
      this.isHovered = isHovered;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: widget.storeName != 'null' && widget.storeName.isNotEmpty ? widget.storeName : 'location'.tr),
      endDrawer: const MenuDrawer(), endDrawerEnableOpenDragGesture: false,
      body: SingleChildScrollView(
        physics: isHovered || !ResponsiveHelper.isDesktop(context) ? const NeverScrollableScrollPhysics() : const AlwaysScrollableScrollPhysics(),
        child: FooterView(
          child: Center(
            child: SizedBox(
              width: Dimensions.webMaxWidth,
              height: ResponsiveHelper.isDesktop(context) ? 600 : MediaQuery.of(context).size.height * 0.85,
              child: Stack(children: [
                MouseRegion(
                  onEnter: (event) => onEntered(true),
                  onExit: (event) => onEntered(false),
                  child: FlutterMap(
                    mapController: _mapController,
                    options: MapOptions(
                      initialCenter: _latLng,
                      initialZoom: 16,
                      minZoom: 0,
                      maxZoom: 16,
                    ),
                    children: [
                      TileLayer(
                        urlTemplate: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                        subdomains: const ['a', 'b', 'c'],
                        userAgentPackageName: 'com.nexofood.user',
                      ),
                      MarkerLayer(markers: _markers),
                    ],
                  ),
                ),

                Positioned(
                  left: Dimensions.paddingSizeLarge, right: Dimensions.paddingSizeLarge, bottom: Dimensions.paddingSizeLarge,
                  child: Column(
                    children: [

                      Align(
                        alignment: Alignment.centerRight,
                        child: InkWell(
                          onTap: () => _checkPermission(() async {
                            AddressModel address = await Get.find<LocationController>().getCurrentLocation(false, mapController: null);
                            _setMarker(address: address, fromCurrentLocation: true);
                          }),
                          child: Container(
                            padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                            decoration: BoxDecoration(borderRadius: BorderRadius.circular(50), color: Theme.of(context).cardColor),
                            child: Icon(Icons.my_location_outlined, color: Theme.of(context).primaryColor, size: 25),
                          ),
                        ),
                      ),
                      const SizedBox(height: Dimensions.paddingSizeLarge),

                      InkWell(
                        onTap: () {
                          _mapController.move(_latLng, 17);
                        },
                        child: Container(
                          padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                            color: Theme.of(context).cardColor,
                          ),
                          child: widget.fromStore ? Row(children: [
                            Expanded(
                              child: Text(widget.address.address ?? '', style: robotoMedium, maxLines: 2, overflow: TextOverflow.ellipsis),
                            ),
                            const SizedBox(width: Dimensions.paddingSizeDefault),

                            InkWell(
                              onTap: () async {
                                String url ='https://www.google.com/maps/dir/?api=1&destination=${widget.address.latitude}'
                                    ',${widget.address.longitude}&mode=d';
                                if (await canLaunchUrlString(url)) {
                                  await launchUrlString(url, mode: LaunchMode.externalApplication);
                                } else {
                                  showCustomSnackBar('unable_to_launch_google_map'.tr);
                                }
                              },
                              child: const Icon(Icons.directions),
                            ),

                          ]) : Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [

                              Row(children: [

                                Icon(
                                  widget.address.addressType == 'home' ? Icons.home_outlined : widget.address.addressType == 'office'
                                      ? Icons.work_outline : Icons.location_on,
                                  size: 30, color: Theme.of(context).primaryColor,
                                ),
                                const SizedBox(width: Dimensions.paddingSizeSmall),

                                Expanded(
                                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisAlignment: MainAxisAlignment.center, children: [

                                    Text(widget.address.addressType!.tr, style: robotoRegular.copyWith(
                                      fontSize: Dimensions.fontSizeSmall, color: Theme.of(context).disabledColor,
                                    )),
                                    const SizedBox(height: Dimensions.paddingSizeExtraSmall),

                                    AddressDetailsWidget(addressDetails: widget.address),

                                  ]),
                                ),
                              ]),
                              const SizedBox(height: Dimensions.paddingSizeSmall),

                              Text('- ${widget.address.contactPersonName}', style: robotoMedium.copyWith(
                                color: Theme.of(context).primaryColor,
                                fontSize: Dimensions.fontSizeLarge,
                              )),

                              Text('- ${widget.address.contactPersonNumber}', style: robotoRegular),

                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),


              ]),
            ),
          ),
        ),
      ),
    );
  }

  void _setMarker({AddressModel? address, bool fromCurrentLocation = false}) async {
    LatLng storeLatLng = LatLng(double.parse(widget.address.latitude!), double.parse(widget.address.longitude!));
    LatLng? userLatLng;

    if (address == null) {
      var userAddr = AddressHelper.getUserAddressFromSharedPref();
      if (userAddr != null && userAddr.latitude != null) {
        userLatLng = LatLng(double.parse(userAddr.latitude!), double.parse(userAddr.longitude!));
      }
    } else {
      userLatLng = LatLng(double.parse(address.latitude!), double.parse(address.longitude!));
    }

    setState(() {
      _markers = [
        Marker(
          point: storeLatLng,
          width: 50,
          height: 50,
          child: Icon(
            widget.fromStore ? (widget.isFood ? Icons.restaurant : Icons.store) : Icons.location_on,
            color: Colors.red,
            size: 40,
          ),
        ),
      ];

      if (userLatLng != null) {
        _markers.add(Marker(
          point: userLatLng,
          width: 30,
          height: 30,
          child: const Icon(Icons.person_pin_circle, color: Colors.blue, size: 30),
        ));
      }
    });

    if (fromCurrentLocation && userLatLng != null) {
      _mapController.move(userLatLng, GetPlatform.isWeb ? 7 : 15);
    } else {
      if (userLatLng != null) {
        var bounds = LatLngBounds.fromPoints([storeLatLng, userLatLng]);
        _mapController.fitCamera(CameraFit.bounds(
          bounds: bounds,
          padding: const EdgeInsets.all(50),
        ));
      } else {
        _mapController.move(storeLatLng, GetPlatform.isWeb ? 7 : 15);
      }
    }
  }

  void _checkPermission(Function onTap) async {
    LocationPermission permission = await Geolocator.checkPermission();
    if(permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    if(permission == LocationPermission.denied) {
      showCustomSnackBar('you_have_to_allow'.tr);
    }else if(permission == LocationPermission.deniedForever) {
      Get.dialog(const PermissionDialogWidget());
    }else {
      onTap();
    }
  }
}
