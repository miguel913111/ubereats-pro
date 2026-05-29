<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\StoreDeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreDeliveryZoneController extends Controller
{
    public function getByStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zones = StoreDeliveryZone::where('store_id', $request->store_id)
            ->where('status', 1)
            ->get();

        return response()->json($zones, 200);
    }

    public function checkInZone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zones = StoreDeliveryZone::where('store_id', $request->store_id)
            ->where('status', 1)
            ->get();

        if ($zones->isEmpty()) {
            return response()->json([
                'in_zone' => true,
                'message' => translate('messages.No custom delivery zones defined for this store'),
            ], 200);
        }

        $inZone = false;
        $matchedZone = null;

        foreach ($zones as $zone) {
            $coords = $zone->coordinates;
            if ($this->isPointInPolygon($request->lat, $request->lng, $coords)) {
                $inZone = true;
                $matchedZone = $zone;
                break;
            }
        }

        return response()->json([
            'in_zone' => $inZone,
            'zone' => $matchedZone,
            'delivery_charge' => $matchedZone?->delivery_charge ?? 0,
            'minimum_order_amount' => $matchedZone?->minimum_order_amount ?? 0,
            'message' => $inZone ? translate('messages.Delivery available in this area') : translate('messages.Delivery not available in this area'),
        ], 200);
    }

    private function isPointInPolygon($lat, $lng, $polygon)
    {
        if (empty($polygon) || !is_array($polygon)) {
            return false;
        }

        $points = $polygon;
        $inside = false;
        $x = $lng;
        $y = $lat;
        $n = count($points);
        $j = $n - 1;

        for ($i = 0; $i < $n; $i++) {
            $xi = $points[$i][1] ?? 0;
            $yi = $points[$i][0] ?? 0;
            $xj = $points[$j][1] ?? 0;
            $yj = $points[$j][0] ?? 0;

            $intersect = (($yi > $y) != ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
            if ($intersect) {
                $inside = !$inside;
            }
            $j = $i;
        }

        return $inside;
    }
}
