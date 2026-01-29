<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Taxpayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'opd_id',
        'nik',
        'name',
        'address',
        'phone',
        'npwpd',
        'object_name',
        'object_address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the OPD that owns this taxpayer
     */
    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    /**
     * Get all retribution types for this taxpayer (many-to-many)
     */
    public function retributionTypes(): BelongsToMany
    {
        return $this->belongsToMany(RetributionType::class, 'taxpayer_retribution_type')
            ->withPivot(['custom_amount', 'notes'])
            ->withTimestamps();
    }

    /**
     * Scope to get only active taxpayers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
