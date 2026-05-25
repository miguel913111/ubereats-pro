<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GiftCardController extends Controller
{
    public function index(Request $request)
    {
        if (!Helpers::module_permission_check('gift_card')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $giftCards = GiftCard::latest()->paginate(25);
        return view('admin-views.gift-card.index', compact('giftCards'));
    }

    public function store(Request $request)
    {
        if (!Helpers::module_permission_check('gift_card')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'code' => 'required|string|max:50|unique:gift_cards',
            'amount' => 'required|numeric|min:0.01',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'expire_date' => 'required|date|after_or_equal:start_date',
            'limit' => 'nullable|integer|min:1',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $giftCard = new GiftCard();
        $giftCard->title = $request->title;
        $giftCard->description = $request->description;
        $giftCard->code = strtoupper($request->code);
        $giftCard->amount = $request->amount;
        $giftCard->min_purchase = $request->min_purchase ?? 0;
        $giftCard->max_discount = $request->max_discount ?? 0;
        $giftCard->start_date = $request->start_date;
        $giftCard->expire_date = $request->expire_date;
        $giftCard->limit = $request->limit;
        $giftCard->status = $request->status;
        $giftCard->created_by = 'admin';
        $giftCard->save();

        Toastr::success(translate('messages.Gift card created successfully'));
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        if (!Helpers::module_permission_check('gift_card')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'code' => 'required|string|max:50|unique:gift_cards,code,' . $id,
            'amount' => 'required|numeric|min:0.01',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'expire_date' => 'required|date|after_or_equal:start_date',
            'limit' => 'nullable|integer|min:1',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $giftCard = GiftCard::findOrFail($id);
        $giftCard->title = $request->title;
        $giftCard->description = $request->description;
        $giftCard->code = strtoupper($request->code);
        $giftCard->amount = $request->amount;
        $giftCard->min_purchase = $request->min_purchase ?? 0;
        $giftCard->max_discount = $request->max_discount ?? 0;
        $giftCard->start_date = $request->start_date;
        $giftCard->expire_date = $request->expire_date;
        $giftCard->limit = $request->limit;
        $giftCard->status = $request->status;
        $giftCard->save();

        Toastr::success(translate('messages.Gift card updated successfully'));
        return redirect()->back();
    }

    public function destroy($id)
    {
        if (!Helpers::module_permission_check('gift_card')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $giftCard = GiftCard::findOrFail($id);
        $giftCard->delete();

        Toastr::success(translate('messages.Gift card deleted successfully'));
        return redirect()->back();
    }

    public function status(Request $request)
    {
        if (!Helpers::module_permission_check('gift_card')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $giftCard = GiftCard::findOrFail($request->id);
        $giftCard->status = $request->status;
        $giftCard->save();

        Toastr::success(translate('messages.Status updated successfully'));
        return redirect()->back();
    }
}
