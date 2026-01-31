<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetributionRate extends Model
{
    protected $fillable = [
        'opd_id',
        'retribution_type_id',
        'retribution_classification_id',
        'zone_id',
        'name',
        'amount',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }

    public function retributionType()
    {
        return $this->belongsTo(RetributionType::class);
    }

    public function classification()
    {
        return $this->belongsTo(RetributionClassification::class, 'retribution_classification_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
