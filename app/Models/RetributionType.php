<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetributionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'opd_id',
        'name',
        'category',
        'icon',
        'base_amount',
        'unit',
        'is_active',
        'form_schema',
        'requirements',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'form_schema' => 'array',
        'requirements' => 'array',
    ];

    /**
     * Get the OPD that owns this retribution type
     */
    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    /**
     * Get all taxpayers for this retribution type (many-to-many)
     */
    public function taxpayers(): BelongsToMany
    {
        return $this->belongsToMany(Taxpayer::class, 'taxpayer_retribution_type')
            ->withPivot(['custom_amount', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get all bills for this retribution type
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Get all tax objects for this type
     */
    public function taxObjects(): HasMany
    {
        return $this->hasMany(TaxObject::class);
    }

    /**
     * Scope to get only active retribution types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
