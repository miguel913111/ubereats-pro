<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreDeliveryZone extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'coordinates',
        'delivery_charge',
        'minimum_order_amount',
        'status',
    ];

    protected $casts = [
        'store_id' => 'integer',
        'coordinates' => 'array',
        'delivery_charge' => 'float',
        'minimum_order_amount' => 'float',
        'status' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
