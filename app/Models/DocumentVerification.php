<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVerification extends Model
{
    protected $fillable = [
        'verifiable_type',
        'verifiable_id',
        'document_type',
        'document_number',
        'document_images',
        'notes',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'document_images' => 'array',
        'verified_at' => 'datetime',
        'status' => 'string',
    ];

    public function verifiable()
    {
        return $this->morphTo();
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Admin::class, 'verified_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
