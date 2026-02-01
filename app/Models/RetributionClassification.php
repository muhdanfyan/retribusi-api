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
        'icon',
    ];

    protected $appends = ['icon_url'];

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
