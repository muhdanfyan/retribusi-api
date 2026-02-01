<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjectVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_object_id',
        'pendata_tanggal',
        'pendata_nama',
        'pendata_nip',
        'pendata_tanda_tangan_url',
        'pejabat_tanggal',
        'pejabat_nama',
        'pejabat_nip',
        'pejabat_tanda_tangan_url',
        'status',
        'notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'pendata_tanggal' => 'date',
        'pejabat_tanggal' => 'date',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the tax object that this verification belongs to
     */
    public function taxObject(): BelongsTo
    {
        return $this->belongsTo(TaxObject::class);
    }

    /**
     * Get the user who performed the verification
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
