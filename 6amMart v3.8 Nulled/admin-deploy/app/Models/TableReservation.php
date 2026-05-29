<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableReservation extends Model
{
    protected $fillable = [
        'store_id',
        'store_table_id',
        'user_id',
        'reservation_date',
        'reservation_time',
        'number_of_guests',
        'status',
        'special_request',
        'cancellation_reason',
        'confirmed_at',
        'qr_code',
        'checked_in_at',
    ];

    protected $casts = [
        'store_id' => 'integer',
        'store_table_id' => 'integer',
        'user_id' => 'integer',
        'reservation_date' => 'date',
        'reservation_time' => 'string',
        'number_of_guests' => 'integer',
        'status' => 'string',
        'confirmed_at' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function storeTable()
    {
        return $this->belongsTo(StoreTable::class, 'store_table_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('reservation_date', '>=', now());
    }
}
