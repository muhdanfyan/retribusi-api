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
        'tariff_percent',
        'icon',
        'base_amount',
        'unit',
        'is_active',
        'form_schema',
        'requirements',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'tariff_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'form_schema' => 'array',
        'requirements' => 'array',
    ];

    /**
     * Get the icon URL or Lucide name
     */
    public function getIconAttribute($value)
    {
        if (empty($value)) return null;

        // If it's already a full URL or a Lucide icon (no slash), return as is
        if (filter_var($value, FILTER_VALIDATE_URL) || !str_contains($value, '/')) {
            return $value;
        }

        // Handle relative paths for local storage
        if (str_starts_with($value, '/storage') || str_starts_with($value, 'storage')) {
            $path = ltrim($value, '/');
            return config('app.url') . '/' . $path;
        }

        return $value;
    }

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
     * Get all classifications for this type
     */
    public function classifications(): HasMany
    {
        return $this->hasMany(RetributionClassification::class);
    }

    /**
     * Get all rates for this type
     */
    public function rates(): HasMany
    {
        return $this->hasMany(RetributionRate::class);
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
