<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRouteSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'sequence',
        'from_lat',
        'from_lng',
        'to_lat',
        'to_lng',
        'from_type',
        'to_type',
        'order_id',
        'distance_km',
        'estimated_minutes',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'from_lat' => 'float',
        'from_lng' => 'float',
        'to_lat' => 'float',
        'to_lng' => 'float',
        'distance_km' => 'float',
        'estimated_minutes' => 'float',
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
