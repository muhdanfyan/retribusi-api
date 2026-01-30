<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = [
        'opd_id',
        'retribution_type_id',
        'name',
        'code',
        'multiplier',
        'amount',
        'description',
        'latitude',
        'longitude',
    ];

    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }

    public function retributionType()
    {
        return $this->belongsTo(RetributionType::class);
    }
}
