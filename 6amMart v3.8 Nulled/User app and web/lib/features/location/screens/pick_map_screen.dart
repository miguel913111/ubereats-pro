import 'dart:async';
import 'package:geolocator/geolocator.dart';
import 'package:loading_animation_widget/loading_animation_widget.dart';
import 'package:nexofood_user/common/widgets/custom_app_bar.dart';
import 'package:nexofood_user/common/widgets/custom_floating_action_button.dart';
import 'package:nexofood_user/features/location/controllers/location_controller.dart';
import 'package:nexofood_user/features/location/widgets/animated_map_icon_extended.dart';
import 'package:nexofood_user/features/location/widgets/animated_map_icon_minimized.dart';
import 'package:nexofood_user/features/splash/controllers/splash_controller.dart';
import 'package:nexofood_user/features/profile/controllers/profile_controller.dart';
import 'package:nexofood_user/features/address/domain/models/address_model.dart';
import 'package:nexofood_user/features/auth/controllers/auth_controller.dart';
import 'package:nexofood_user/helper/address_helper.dart';
import 'package:nexofood_user/helper/auth_helper.dart';
import 'package:nexofood_user/helper/responsive_helper.dart';
import 'package:nexofood_user/helper/route_helper.dart';
import 'package:nexofood_user/util/dimensions.dart';
import 'package:nexofood_user/util/styles.dart';
import 'package:nexofood_user/common/widgets/custom_button.dart';
import 'package:nexofood_user/common/widgets/custom_snackbar.dart';
import 'package:nexofood_user/common/widgets/menu_drawer.dart';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:get/get.dart';
import 'package:nexofood_user/common/models/google_maps_compat.dart';
import 'package:nexofood_user/features/location/widgets/serach_location_widget.dart';

class PickMapScreen extends StatefulWidget {
  final bool fromSignUp;
  final bool fromAddAddress;
  final bool canRoute;
  final String? route;
  final dynamic googleMapController;
  final Function(AddressModel address)? onPicked;
  final bool fromLandingPage;
  final bool fromGuestCheckout;
  const PickMapScreen({super.key,
    required this.fromSignUp, required this.fromAddAddress, required this.canRoute,
    required this.route, this.googleMapController, this.onPicked, this.fromLandingPage = false, this.fromGuestCheckout = false,
  });

  @override
  State<PickMapScreen> createState() => _PickMapScreenState();
}

class _PickMapScreenState extends State<PickMapScreen> {
  late MapController _mapController;
  LatLng? _cameraPosition;
  late LatLng _initialPosition;
  bool locationAlreadyAllow = false;
  bool _mapReady = false;
  double _currentZoomLevel = 16.0;
  Timer? _cameraIdleTimer;

  @override
  void initState() {
    super.initState();
    _mapController = MapController();
    _cameraPosition = LatLng(
      double.parse(Get.find<SplashController>().configModel!.defaultLocation!.lat ?? '0'),
      double.parse(Get.find<SplashController>().configModel!.defaultLocation!.lng ?? '0'),
    );

    if(widget.fromAddAddress) {
      Get.find<LocationController>().setPickData();
    }
    _initialPosition = LatLng(
      double.parse(Get.find<SplashController>().configModel!.defaultLocation!.lat ?? '0'),
      double.parse(Get.find<SplashController>().configModel!.defaultLocation!.lng ?? '0'),
    );
    _checkAlreadyLocationEnable();
  }

  void _safeMove(LatLng target, double zoom) {
    if (!_mapReady) return;
    try {
      _mapController.move(target, zoom);
      _cameraPosition = target;
    } catch (_) {
      // Map not fully initialized yet, ignore
    }
  }

  Future<void> _checkAlreadyLocationEnable() async {
    LocationPermission permission = await Geolocator.checkPermission();
    if(permission == LocationPermission.whileInUse) {
      locationAlreadyAllow = true;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: ResponsiveHelper.isDesktop(context) ? Colors.transparent : Theme.of(context).cardColor,
      appBar: widget.fromGuestCheckout && !ResponsiveHelper.isDesktop(context) ? CustomAppBar(title: 'delivery_address'.tr) : null,
      endDrawer: const MenuDrawer(), endDrawerEnableOpenDragGesture: false,
      body: SafeArea(child: Center(child: Container(
        height:  ResponsiveHelper.isDesktop(context) ? 600 : null,
        width: ResponsiveHelper.isDesktop(context) ? 700 : Dimensions.webMaxWidth,
        decoration: context.width > 700 ? BoxDecoration(
          color: Theme.of(context).cardColor, borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
        ) : null,

        child: GetBuilder<LocationController>(builder: (locationController) {

          return ResponsiveHelper.isDesktop(context) ? Padding(
            padding: const  EdgeInsets.symmetric(vertical: Dimensions.paddingSizeSmall, horizontal: Dimensions.paddingSizeLarge),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Align(
                  alignment: Alignment.topRight,
                  child: IconButton(
                    onPressed: () => Get.back(),
                    icon: const Icon(Icons.clear),
                  ),
                ),

                Text('type_your_address_here_to_pick_form_map'.tr, style: robotoBold),
                const SizedBox(height: Dimensions.paddingSizeDefault),

                SearchLocationWidget(
                  mapController: GoogleMapController(),
                  pickedAddress: locationController.pickAddress,
                  isEnabled: null,
                  fromDialog: true,
                  onLocationSelected: (lat, lng) {
                    _safeMove(LatLng(lat, lng), _currentZoomLevel);
                  },
                ),
                const SizedBox(height: Dimensions.paddingSizeDefault),

                SizedBox(
                  height: 350,
                  child:  Stack(children: [
                    ClipRRect(
                      borderRadius:BorderRadius.circular(Dimensions.radiusDefault),
                      child: _buildMap(locationController),
                    ),

                    Center(child: Padding(
                      padding: const EdgeInsets.only(bottom: Dimensions.pickMapIconSize * 0.65),
                      child: locationController.isCameraMoving ? const AnimatedMapIconExtended() : const AnimatedMapIconMinimised(),
                    )),

                    Positioned(
                      bottom: 75, right: Dimensions.paddingSizeSmall,
                      child: FloatingActionButton(
                        mini: true, backgroundColor: Theme.of(context).cardColor,
                        onPressed: () => Get.find<LocationController>().checkPermission(() {
                          Get.find<LocationController>().getCurrentLocation(false, mapController: null).then((address) {
                            if (address.latitude != null && address.longitude != null) {
                              _safeMove(LatLng(double.parse(address.latitude!), double.parse(address.longitude!)), _currentZoomLevel);
                            }
                          });
                        }),
                        child: Icon(Icons.my_location, color: Theme.of(context).primaryColor),
                      ),
                    ),
                  ]),
                ),
                const SizedBox(height: Dimensions.paddingSizeExtraLarge),

                Row(
                  children: [
                    const Spacer(),
                    CustomButton(
                      isBold: false,
                      width: 120,
                      radius: Dimensions.radiusSmall,
                      buttonText: 'cancel'.tr,
                      isLoading: locationController.isLoading,
                      color: Theme.of(context).disabledColor.withValues(alpha: 0.2),
                      textColor: Theme.of(context).textTheme.bodyLarge?.color,
                      onPressed: () {
                        Get.back();
                      },
                    ),

                    SizedBox(width: Dimensions.paddingSizeSmall),
                    Flexible(
                      child: CustomButton(
                        isBold: false,
                        radius: Dimensions.radiusSmall,
                        buttonText: locationController.inZone ? widget.fromAddAddress ? 'pick_address'.tr : 'pick_location'.tr
                            : 'service_not_available_in_this_area'.tr,
                        isLoading: locationController.isLoading,
                        onPressed: locationController.isLoading ? (){} : (locationController.buttonDisabled || locationController.loading) ? null : () async {
                          await _onPickAddressButtonPressed(locationController);
                        },
                      ),
                    ),
                  ],
                ),

              ],
            ),
          ) : Stack(children: [
            _buildMap(locationController),

            Center(child: Padding(
              padding: const EdgeInsets.only(bottom: Dimensions.pickMapIconSize * 0.65),
              child: locationController.isCameraMoving ? const AnimatedMapIconExtended() : const AnimatedMapIconMinimised(),
            )),

            Positioned(
              top: Dimensions.paddingSizeLarge, left: Dimensions.paddingSizeSmall, right: Dimensions.paddingSizeSmall,
              child: SearchLocationWidget(
                mapController: GoogleMapController(),
                pickedAddress: locationController.pickAddress,
                isEnabled: null,
                onLocationSelected: (lat, lng) {
                  _safeMove(LatLng(lat, lng), _currentZoomLevel);
                },
              ),
            ),

            Positioned(
              bottom: 80, right: Dimensions.paddingSizeLarge,
              child: Column(
                children: [
                  FloatingActionButton(
                    mini: true, backgroundColor: Theme.of(context).cardColor,
                    onPressed: () => Get.find<LocationController>().checkPermission(() {
                      Get.find<LocationController>().getCurrentLocation(false, mapController: null).then((address) {
                        if (address.latitude != null && address.longitude != null) {
                          _safeMove(LatLng(double.parse(address.latitude!), double.parse(address.longitude!)), _currentZoomLevel);
                        }
                      });
                    }),
                    child: Icon(Icons.my_location, color: Theme.of(context).primaryColor),
                  ),
                  const SizedBox(height: Dimensions.paddingSizeDefault),

                  Container(
                    decoration: BoxDecoration(
                      color: Theme.of(context).cardColor,
                      boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.2), blurRadius: 6, spreadRadius: 0.5, offset: const Offset(0, 4))],
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Column(children: [
                      CustomFloatingActionButton(
                        icon: Icons.add, heroTag: 'add_button',
                        onTap: () {
                          setState(() {
                            _currentZoomLevel++;
                          });
                          _safeMove(_mapController.camera.center, _currentZoomLevel);
                        },
                      ),

                      Container(
                        width: 20, height: 1,
                        color: Theme.of(context).disabledColor.withValues(alpha: 0.5),
                      ),

                      CustomFloatingActionButton(
                        icon: Icons.remove, heroTag: 'remove_button',
                        onTap: () {
                          setState(() {
                            _currentZoomLevel--;
                          });
                          _safeMove(_mapController.camera.center, _currentZoomLevel);
                        },
                      ),


                    ]),
                  ),
                ],
              ),
            ),

            Positioned(
              bottom: Dimensions.paddingSizeLarge, left: Dimensions.paddingSizeLarge, right: Dimensions.paddingSizeLarge,
              child: InkWell(
                highlightColor: Colors.transparent,
                splashColor: Colors.transparent,
                  onTap: locationController.isLoading ? (){} : (locationController.buttonDisabled || locationController.loading) ? null : () async {
                    await _onPickAddressButtonPressed(locationController);
                  },
                child: Builder(
                  builder: (context) {
                    print('======Button Disabled: ${locationController.buttonDisabled}, Loading: ${locationController.loading}');
                    return Container(
                      padding: EdgeInsets.all(locationController.loading ? Dimensions.paddingSizeExtraSmall : Dimensions.paddingSizeDefault - 2),
                      alignment: Alignment.center,
                      decoration: BoxDecoration(
                        color: locationController.loading ? Theme.of(context).primaryColor.withValues(alpha: 0.8)
                            : locationController.buttonDisabled ? Theme.of(context).disabledColor.withValues(alpha: 0.7)
                            : Theme.of(context).primaryColor,
                        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                      ),
                      child: locationController.loading ? Center(
                        child: LoadingAnimationWidget.waveDots(color: Colors.white, size: 40),
                      ) : Text(
                        locationController.inZone ? widget.fromAddAddress ? 'pick_address'.tr : widget.fromGuestCheckout ? 'confirm_address'.tr : 'pick_location'.tr : 'service_not_available_in_this_area'.tr,
                        style: robotoBold.copyWith(fontSize: Dimensions.fontSizeLarge, color: Colors.white),
                      ),
                    );
                  }
                ),
              ),
            ),
          ]);

        }),
      ))),
    );
  }

  Widget _buildMap(LocationController locationController) {
    // Use current camera position if available, otherwise use saved position or default
    LatLng initialCenter = _cameraPosition ?? LatLng(
      locationController.pickPosition.latitude != 0 ? locationController.pickPosition.latitude
          : widget.fromAddAddress ? locationController.position.latitude : _initialPosition.latitude,
      locationController.pickPosition.longitude != 0 ? locationController.pickPosition.longitude
          : widget.fromAddAddress ? locationController.position.longitude : _initialPosition.longitude,
    );

    return FlutterMap(
      options: MapOptions(
        initialCenter: initialCenter,
        initialZoom: _currentZoomLevel,
        minZoom: 0,
        maxZoom: 16,
        onPositionChanged: (position, hasGesture) {
          _cameraPosition = position.center;
          if (hasGesture) {
            locationController.updateCameraMovingStatus(true);
            locationController.disableButton();
            _cameraIdleTimer?.cancel();
            _cameraIdleTimer = Timer(const Duration(milliseconds: 600), () {
              locationController.updateCameraMovingStatus(false);
              if (_cameraPosition != null) {
                locationController.updatePosition(
                  CameraPosition(target: _cameraPosition!, zoom: _currentZoomLevel),
                  false,
                );
              }
            });
          }
        },
        onMapReady: () {
          _mapReady = true;
          if (!widget.fromAddAddress && widget.route != RouteHelper.onBoarding) {
            Get.find<LocationController>().getCurrentLocation(false, mapController: null).then((address) {
              if (address.latitude != null && address.longitude != null) {
                _safeMove(LatLng(double.parse(address.latitude!), double.parse(address.longitude!)), _currentZoomLevel);
              }
            });
          }
        },
      ),
      mapController: _mapController,
      children: [
        TileLayer(
          urlTemplate: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
          subdomains: const ['a', 'b', 'c'],
          userAgentPackageName: 'com.nexofood.user',
        ),
      ],
    );
  }

  Future<void> _onPickAddressButtonPressed(LocationController locationController) async {
    print('======PICK UP BUTTON CLICKED======');
    // Use center of map as picked position
    LatLng? picked = _cameraPosition ?? _mapController.camera.center;
    print('======picked: $picked');
    if (picked != null) {
      print('======calling updatePosition...');
      await locationController.updatePosition(
        CameraPosition(target: picked, zoom: _currentZoomLevel),
        false,
      );
      print('======updatePosition done');
      // setPickData() copies _position/_address into _pickPosition/_pickAddress,
      // which would overwrite the values updatePosition just wrote. Skip it here.
      print('======SKIPPING setPickData (would overwrite pick values)');
    }

    print('======pickPosition.latitude: ${locationController.pickPosition.latitude}');
    print('======pickAddress: ${locationController.pickAddress}');
    print('======pickAddress isEmpty: ${locationController.pickAddress?.isEmpty}');
    if(locationController.pickPosition.latitude != 0 && locationController.pickAddress != null && locationController.pickAddress!.isNotEmpty) {
      if(widget.onPicked != null) {
        AddressModel address = AddressModel(
          latitude: locationController.pickPosition.latitude.toString(),
          longitude: locationController.pickPosition.longitude.toString(),
          addressType: 'my_location', address: locationController.pickAddress,
          contactPersonName: AddressHelper.getUserAddressFromSharedPref()?.contactPersonName,
          contactPersonNumber: AddressHelper.getUserAddressFromSharedPref()?.contactPersonNumber,
        );
        widget.onPicked!(address);
        Get.back();
      }else if(widget.fromAddAddress) {
        Get.back();
      }else {
        AddressModel address = AddressModel(
          latitude: locationController.pickPosition.latitude.toString(),
          longitude: locationController.pickPosition.longitude.toString(),
          addressType: 'my_location', address: locationController.pickAddress,
        );

        if(widget.fromLandingPage) {
          if(!AuthHelper.isGuestLoggedIn() && !AuthHelper.isLoggedIn()) {
            Get.find<AuthController>().guestLogin().then((response) {
              if(response.isSuccess) {
                Get.find<ProfileController>().setForceFullyUserEmpty();
                Get.back();
                locationController.saveAddressAndNavigate(
                  address, widget.fromSignUp, widget.route, widget.canRoute, ResponsiveHelper.isDesktop(Get.context),
                );
              }
            });
          } else {
            Get.back();
            locationController.saveAddressAndNavigate(
              address, widget.fromSignUp, widget.route, widget.canRoute, ResponsiveHelper.isDesktop(context),
            );
          }
        }else {
          print('========here calling m f  dm djjk======');
          locationController.saveAddressAndNavigate(
            address, widget.fromSignUp, widget.route, widget.canRoute, ResponsiveHelper.isDesktop(context),
          );
        }
      }
    }else {
      showCustomSnackBar('pick_an_address'.tr);
    }
  }

  Future<bool> _locationCheck() async {
    bool locationServiceEnabled = true;
    LocationPermission permission = await Geolocator.checkPermission();

    if(permission == LocationPermission.denied) {
      locationServiceEnabled = false;
      permission = await Geolocator.requestPermission();
    }
    if(permission == LocationPermission.deniedForever) {
      locationServiceEnabled = false;
    }
    return locationServiceEnabled;
  }

  @override
  void dispose() {
    _cameraIdleTimer?.cancel();
    super.dispose();
  }
}
