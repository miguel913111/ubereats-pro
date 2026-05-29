<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreDeliveryMan extends Model
{
    protected $fillable = [
        'store_id',
        'f_name',
        'l_name',
        'phone',
        'email',
        'password',
        'identity_type',
        'identity_number',
        'identity_image',
        'image',
        'fcm_token',
        'status',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'store_id' => 'integer',
        'status' => 'integer',
        'active' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'delivery_man_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1)->where('active', 1);
    }

    public function getFullNameAttribute()
    {
        return trim($this->f_name . ' ' . ($this->l_name ?? ''));
    }
}
