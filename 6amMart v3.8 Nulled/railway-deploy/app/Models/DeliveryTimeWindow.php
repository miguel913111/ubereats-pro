<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryTimeWindow extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'store_id',
        'day_of_week',
        'start_time',
        'end_time',
        'extra_charge',
        'is_peak',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'extra_charge' => 'float',
        'is_peak' => 'boolean',
        'status' => 'boolean',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
