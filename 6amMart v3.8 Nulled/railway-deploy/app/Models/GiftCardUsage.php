<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCardUsage extends Model
{
    protected $fillable = [
        'gift_card_id',
        'user_id',
        'order_id',
        'amount',
    ];

    protected $casts = [
        'gift_card_id' => 'integer',
        'user_id' => 'integer',
        'order_id' => 'integer',
        'amount' => 'float',
    ];

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
