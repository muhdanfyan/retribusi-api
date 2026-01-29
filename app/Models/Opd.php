<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'status',
        'is_active',
        'logo_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all users belonging to this OPD
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all retribution types belonging to this OPD
     */
    public function retributionTypes(): HasMany
    {
        return $this->hasMany(RetributionType::class);
    }

    /**
     * Get all taxpayers belonging to this OPD
     */
    public function taxpayers(): HasMany
    {
        return $this->hasMany(Taxpayer::class);
    }

    /**
     * Scope to get only approved OPDs
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get only pending OPDs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
