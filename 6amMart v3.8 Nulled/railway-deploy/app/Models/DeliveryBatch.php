<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_man_id',
        'zone_id',
        'status',
        'total_distance_km',
        'estimated_duration_min',
        'total_orders',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'total_distance_km' => 'float',
        'estimated_duration_min' => 'float',
        'total_orders' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function batchOrders()
    {
        return $this->hasMany(BatchOrder::class, 'batch_id');
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, BatchOrder::class, 'batch_id', 'id', 'id', 'order_id');
    }

    public function routeSegments()
    {
        return $this->hasMany(DeliveryRouteSegment::class, 'batch_id');
    }
}
