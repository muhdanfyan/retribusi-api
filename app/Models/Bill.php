<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    protected $fillable = [
        'taxpayer_id',
        'tax_object_id',
        'opd_id',
        'retribution_type_id',
        'bill_number',
        'amount',
        'status',
        'period',
        'period_start',
        'period_end',
        'metadata',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'period_start' => 'date',
        'period_end' => 'date',
        'metadata' => 'json',
    ];

    public function taxpayer(): BelongsTo
    {
        return $this->belongsTo(Taxpayer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function retributionType(): BelongsTo
    {
        return $this->belongsTo(RetributionType::class);
    }

    public function taxObject(): BelongsTo
    {
        return $this->belongsTo(TaxObject::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
