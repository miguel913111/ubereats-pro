<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\TableReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DineInQrController extends Controller
{
    public function getQrCode(Request $request, $reservationId)
    {
        $reservation = TableReservation::where('id', $reservationId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$reservation) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => translate('messages.Reservation not found')]]], 404);
        }

        if (!$reservation->qr_code) {
            $reservation->qr_code = Str::random(32);
            $reservation->save();
        }

        return response()->json([
            'qr_code' => $reservation->qr_code,
            'reservation' => $reservation,
        ], 200);
    }

    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $reservation = TableReservation::where('qr_code', $request->qr_code)
            ->where('store_id', $request->user()->id)
            ->first();

        if (!$reservation) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => translate('messages.Invalid QR code')]]], 404);
        }

        if ($reservation->status != 'confirmed') {
            return response()->json(['errors' => [['code' => 'not_confirmed', 'message' => translate('messages.Reservation is not confirmed')]]], 403);
        }

        if ($reservation->checked_in_at) {
            return response()->json(['errors' => [['code' => 'already_checked_in', 'message' => translate('messages.Customer already checked in')]]], 403);
        }

        $reservation->checked_in_at = now();
        $reservation->status = 'completed';
        $reservation->save();

        return response()->json([
            'message' => translate('messages.Check-in successful'),
            'reservation' => $reservation,
        ], 200);
    }

    public function getReservationByQr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $reservation = TableReservation::with(['user', 'storeTable'])
            ->where('qr_code', $request->qr_code)
            ->where('store_id', $request->user()->id)
            ->first();

        if (!$reservation) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => translate('messages.Invalid QR code')]]], 404);
        }

        return response()->json([
            'reservation' => $reservation,
        ], 200);
    }
}
