<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreTable extends Model
{
    protected $fillable = [
        'store_id',
        'table_number',
        'capacity',
        'status',
        'description',
    ];

    protected $casts = [
        'store_id' => 'integer',
        'capacity' => 'integer',
        'status' => 'string',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function reservations()
    {
        return $this->hasMany(TableReservation::class, 'store_table_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
