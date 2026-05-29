<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\CashBack;
use Illuminate\Http\Request;

class CashBackController extends Controller
{
    public function calculate(Request $request)
    {
        $amount = $request->get('amount', 0);
        $userId = $request->user()?->id;

        $cashBack = Helpers::getCalculatedCashBackAmount(amount: $amount, customer_id: $userId);

        return response()->json([
            'cashback_amount' => data_get($cashBack, 'calculated_amount', 0),
            'cashback_percentage' => data_get($cashBack, 'cashback_amount', 0),
            'cashback_type' => data_get($cashBack, 'cashback_type', ''),
            'min_purchase' => data_get($cashBack, 'min_purchase', 0),
            'max_discount' => data_get($cashBack, 'max_discount', 0),
            'eligible' => data_get($cashBack, 'calculated_amount', 0) > 0,
        ], 200);
    }

    public function list()
    {
        $cashBacks = CashBack::active()->Running()->latest()->get();
        return response()->json($cashBacks, 200);
    }

    public function myCashBackHistory(Request $request)
    {
        $history = \App\Models\CashBackHistory::where('user_id', $request->user()->id)
            ->with('order')
            ->latest()
            ->get();

        return response()->json($history, 200);
    }
}
