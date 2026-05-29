import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:nexofood_user/common/widgets/custom_ink_well.dart';
import 'package:nexofood_user/common/widgets/no_data_screen.dart';
import 'package:nexofood_user/common/widgets/paginated_list_view.dart';
import 'package:nexofood_user/features/ride_share_module/ride_order/controllers/ride_controller.dart';
import 'package:nexofood_user/features/ride_share_module/ride_order/screens/ride_order_complete_screen.dart';
import 'package:nexofood_user/features/ride_share_module/ride_payment/screens/ride_payment_screen.dart';
import 'package:nexofood_user/helper/date_converter.dart';
import 'package:nexofood_user/helper/responsive_helper.dart';
import 'package:nexofood_user/util/app_constants.dart';
import 'package:nexofood_user/util/dimensions.dart';
import 'package:nexofood_user/util/images.dart';
import 'package:nexofood_user/util/styles.dart';
import 'package:nexofood_user/features/ride_share_module/ride_location/domain/models/ride_details_model.dart';

class RideOrderViewWidget extends StatefulWidget {
  final bool isRunning;
  const RideOrderViewWidget({super.key, required this.isRunning});

  @override
  State<RideOrderViewWidget> createState() => _RideOrderViewWidgetState();
}

class _RideOrderViewWidgetState extends State<RideOrderViewWidget> {
  @override
  Widget build(BuildContext context) {
    return const SizedBox();
  }
}
