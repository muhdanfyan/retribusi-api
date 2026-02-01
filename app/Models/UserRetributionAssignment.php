<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRetributionAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'retribution_type_id',
        'retribution_classification_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function retributionType(): BelongsTo
    {
        return $this->belongsTo(RetributionType::class);
    }

    public function retributionClassification(): BelongsTo
    {
        return $this->belongsTo(RetributionClassification::class);
    }
}
