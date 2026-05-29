<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'order_id',
        'delivery_sequence',
        'distance_from_prev_km',
        'estimated_time_min',
        'picked_up_at',
        'delivered_at',
    ];

    protected $casts = [
        'delivery_sequence' => 'integer',
        'distance_from_prev_km' => 'float',
        'estimated_time_min' => 'float',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(DeliveryBatch::class, 'batch_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
