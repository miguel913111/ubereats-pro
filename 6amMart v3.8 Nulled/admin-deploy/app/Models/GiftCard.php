<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    protected $fillable = [
        'title',
        'description',
        'code',
        'amount',
        'min_purchase',
        'max_discount',
        'start_date',
        'expire_date',
        'total_uses',
        'used_count',
        'limit',
        'status',
        'image',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'min_purchase' => 'float',
        'max_discount' => 'float',
        'total_uses' => 'integer',
        'used_count' => 'integer',
        'limit' => 'integer',
        'status' => 'integer',
        'start_date' => 'date',
        'expire_date' => 'date',
    ];

    public function usages()
    {
        return $this->hasMany(GiftCardUsage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('expire_date', '>=', now());
    }
}
