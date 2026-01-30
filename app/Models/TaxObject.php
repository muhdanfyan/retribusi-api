<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxObject extends Model
{
    use HasFactory;

    protected $fillable = [
        'taxpayer_id',
        'retribution_type_id',
        'opd_id',
        'zone_id',
        'name',
        'address',
        'latitude',
        'longitude',
        'metadata',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the taxpayer that owns this object
     */
    public function taxpayer(): BelongsTo
    {
        return $this->belongsTo(Taxpayer::class);
    }

    /**
     * Get the retribution type for this object
     */
    public function retributionType(): BelongsTo
    {
        return $this->belongsTo(RetributionType::class);
    }

    /**
     * Get the OPD for this object
     */
    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    /**
     * Get the zone for this object
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Get the user who approved this object
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all bills for this object
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }
}
