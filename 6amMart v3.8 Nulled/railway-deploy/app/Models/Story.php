<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    protected $fillable = [
        'store_id',
        'title',
        'image',
        'video',
        'type',
        'duration',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'store_id' => 'integer',
        'duration' => 'integer',
        'status' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1)
            ->where('expires_at', '>', now());
    }
}
