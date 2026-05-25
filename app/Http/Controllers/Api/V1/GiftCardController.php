<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Models\GiftCardUsage;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GiftCardController extends Controller
{
    public function list(Request $request)
    {
        $giftCards = GiftCard::active()->latest()->get();
        return response()->json($giftCards, 200);
    }

    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $giftCard = GiftCard::where('code', $request->code)
            ->where('status', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('expire_date', '>=', now())
            ->first();

        if (!$giftCard) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => translate('messages.Gift card not found or expired')]]], 404);
        }

        if ($giftCard->limit && $giftCard->used_count >= $giftCard->limit) {
            return response()->json(['errors' => [['code' => 'limit_reached', 'message' => translate('messages.Gift card usage limit reached')]]], 403);
        }

        $alreadyUsed = GiftCardUsage::where('gift_card_id', $giftCard->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($alreadyUsed) {
            return response()->json(['errors' => [['code' => 'already_used', 'message' => translate('messages.You have already used this gift card')]]], 403);
        }

        return response()->json([
            'gift_card' => $giftCard,
            'message' => translate('messages.Gift card applied successfully')
        ], 200);
    }

    public function purchase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gift_card_id' => 'required|integer',
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $giftCard = GiftCard::find($request->gift_card_id);
        if (!$giftCard || !$giftCard->status) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => translate('messages.Gift card not found')]]], 404);
        }

        $user = User::find($request->user()->id);

        if ($request->payment_method == 'wallet') {
            if ($user->wallet_balance < $giftCard->amount) {
                return response()->json(['errors' => [['code' => 'insufficient_fund', 'message' => translate('messages.Insufficient wallet balance')]]], 403);
            }

            $user->wallet_balance -= $giftCard->amount;
            $user->save();

            $walletTransaction = new WalletTransaction();
            $walletTransaction->user_id = $user->id;
            $walletTransaction->transaction_id = Str::uuid();
            $walletTransaction->transaction_type = 'gift_card_purchase';
            $walletTransaction->debit = $giftCard->amount;
            $walletTransaction->balance = $user->wallet_balance;
            $walletTransaction->reference = $giftCard->id;
            $walletTransaction->save();

            $this->redeemToWallet($user, $giftCard);

            return response()->json(['message' => translate('messages.Gift card purchased and redeemed successfully')], 200);
        }

        // For digital payment, generate payment link similar to WalletController::add_fund
        return response()->json([
            'message' => translate('messages.Payment initialization required'),
            'gift_card' => $giftCard,
        ], 200);
    }

    public function redeem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $giftCard = GiftCard::where('code', $request->code)
            ->where('status', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('expire_date', '>=', now())
            ->first();

        if (!$giftCard) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => translate('messages.Gift card not found or expired')]]], 404);
        }

        if ($giftCard->limit && $giftCard->used_count >= $giftCard->limit) {
            return response()->json(['errors' => [['code' => 'limit_reached', 'message' => translate('messages.Gift card usage limit reached')]]], 403);
        }

        $user = User::find($request->user()->id);
        $alreadyUsed = GiftCardUsage::where('gift_card_id', $giftCard->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyUsed) {
            return response()->json(['errors' => [['code' => 'already_used', 'message' => translate('messages.You have already used this gift card')]]], 403);
        }

        $this->redeemToWallet($user, $giftCard);

        return response()->json([
            'message' => translate('messages.Gift card redeemed successfully'),
            'amount' => $giftCard->amount,
        ], 200);
    }

    private function redeemToWallet($user, $giftCard)
    {
        $user->wallet_balance += $giftCard->amount;
        $user->save();

        $walletTransaction = new WalletTransaction();
        $walletTransaction->user_id = $user->id;
        $walletTransaction->transaction_id = Str::uuid();
        $walletTransaction->transaction_type = 'gift_card_redeem';
        $walletTransaction->credit = $giftCard->amount;
        $walletTransaction->balance = $user->wallet_balance;
        $walletTransaction->reference = $giftCard->id;
        $walletTransaction->save();

        GiftCardUsage::create([
            'gift_card_id' => $giftCard->id,
            'user_id' => $user->id,
            'amount' => $giftCard->amount,
        ]);

        $giftCard->used_count += 1;
        $giftCard->save();
    }

    public function myGiftCards(Request $request)
    {
        $usages = GiftCardUsage::where('user_id', $request->user()->id)
            ->with('giftCard')
            ->latest()
            ->get();

        return response()->json($usages, 200);
    }
}
