<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\BusinessSetting;

class AddBatchDeliverySettings extends Migration
{
    public function up()
    {
        $settings = [
            ['key' => 'batch_delivery_enabled', 'value' => '1'],
            ['key' => 'batch_max_radius_km', 'value' => '3.0'],
            ['key' => 'batch_max_orders', 'value' => '4'],
            ['key' => 'batch_time_window_minutes', 'value' => '30'],
            ['key' => 'batch_min_orders_to_group', 'value' => '2'],
        ];

        foreach ($settings as $setting) {
            BusinessSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }

    public function down()
    {
        $keys = [
            'batch_delivery_enabled',
            'batch_max_radius_km',
            'batch_max_orders',
            'batch_time_window_minutes',
            'batch_min_orders_to_group',
        ];

        BusinessSetting::whereIn('key', $keys)->delete();
    }
}
