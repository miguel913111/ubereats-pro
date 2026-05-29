import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:nexofood_vendor/features/hybrid_pricing/controllers/hybrid_pricing_controller.dart';

class HybridPricingScreen extends StatefulWidget {
  const HybridPricingScreen({Key? key}) : super(key: key);

  @override
  State<HybridPricingScreen> createState() => _HybridPricingScreenState();
}

class _HybridPricingScreenState extends State<HybridPricingScreen> {
  @override
  void initState() {
    super.initState();
    Get.find<HybridPricingController>().getMyPricingModels();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('pricing_models'.tr)),
      body: GetBuilder<HybridPricingController>(
        builder: (controller) {
          final models = controller.pricingModels;
          if (models == null) {
            return const Center(child: CircularProgressIndicator());
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Active Models
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('active_models'.tr, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 12),
                        _buildModelToggle(
                          'commission'.tr,
                          models['commission']?['active'] ?? false,
                          models['commission']?['percentage']?.toString() ?? '0',
                          '%',
                        ),
                        _buildModelToggle(
                          'subscription'.tr,
                          models['subscription']?['active'] ?? false,
                          models['subscription']?['current_plan'] ?? '-',
                          '',
                        ),
                        _buildModelToggle(
                          'fixed_delivery_fee'.tr,
                          models['fixed_delivery_fee']?['active'] ?? false,
                          models['fixed_delivery_fee']?['amount']?.toString() ?? '0',
                          '',
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),

                // Driver Rates
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('driver_rates'.tr, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 12),
                        _buildRateRow('per_km_charge'.tr, models['driver_rates']?['per_km_charge'] ?? 0),
                        _buildRateRow('fixed_charge'.tr, models['driver_rates']?['fixed_charge'] ?? 0),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),

                // Simulation
                if (models['simulation'] != null)
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('earnings_simulation'.tr, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 12),
                          _buildEarningRow('platform_earns'.tr, models['simulation']['totals']?['platform_earnings'] ?? 0, Colors.blue),
                          _buildEarningRow('store_earns'.tr, models['simulation']['totals']?['store_earnings'] ?? 0, Colors.green),
                          _buildEarningRow('driver_earns'.tr, models['simulation']['totals']?['driver_earnings'] ?? 0, Colors.orange),
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildModelToggle(String title, bool active, String value, String suffix) {
    return ListTile(
      leading: Icon(
        active ? Icons.check_circle : Icons.cancel,
        color: active ? Colors.green : Colors.grey,
      ),
      title: Text(title),
      trailing: Text(
        '$value $suffix',
        style: const TextStyle(fontWeight: FontWeight.bold),
      ),
    );
  }

  Widget _buildRateRow(String label, double value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label),
          Text(value.toStringAsFixed(2), style: const TextStyle(fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  Widget _buildEarningRow(String label, double value, Color color) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label),
          Text(
            value.toStringAsFixed(2),
            style: TextStyle(fontWeight: FontWeight.bold, color: color),
          ),
        ],
      ),
    );
  }
}
