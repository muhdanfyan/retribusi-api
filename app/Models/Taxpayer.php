<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Laravel\Sanctum\HasApiTokens;

class Taxpayer extends Model
{
    use HasFactory, HasApiTokens;

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
        'password',
    ];

    protected $hidden = [
        'password',
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
