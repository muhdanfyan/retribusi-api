<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetributionClassification extends Model
{
    protected $fillable = [
        'opd_id',
        'retribution_type_id',
        'name',
        'code',
        'description',
    ];

    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }

    public function retributionType()
    {
        return $this->belongsTo(RetributionType::class);
    }

    public function zones()
    {
        return $this->hasMany(Zone::class, 'retribution_classification_id');
    }

    public function rates()
    {
        return $this->hasMany(RetributionRate::class);
    }

    public function taxObjects()
    {
        return $this->hasMany(TaxObject::class);
    }
}
