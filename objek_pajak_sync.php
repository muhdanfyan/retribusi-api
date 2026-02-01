<?php

use App\Models\Taxpayer;
use App\Models\TaxObject;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting synchronization of Tax Objects from Taxpayers table...\n";

$taxpayers = Taxpayer::with(['retributionTypes'])->get();
$count = 0;

foreach ($taxpayers as $tp) {
    if (!$tp->object_name) {
        echo "Skipping Taxpayer ID: {$tp->id} (No object name)\n";
        continue;
    }

    // Check if object already exists
    $exists = TaxObject::where('taxpayer_id', $tp->id)
        ->where('name', $tp->object_name)
        ->exists();

    if ($exists) {
        echo "Tax Object already exists for Taxpayer ID: {$tp->id}\n";
        continue;
    }

    // For each retribution type assigned to taxpayer, create a tax object record
    foreach ($tp->retributionTypes as $type) {
        TaxObject::create([
            'nop' => 'NOP-' . str_pad($tp->id, 4, '0', STR_PAD_LEFT) . '-' . str_pad($type->id, 3, '0', STR_PAD_LEFT),
            'taxpayer_id' => $tp->id,
            'retribution_type_id' => $type->id,
            'opd_id' => $tp->opd_id,
            'name' => $tp->object_name,
            'address' => $tp->object_address ?: $tp->address,
            'latitude' => $tp->latitude,
            'longitude' => $tp->longitude,
            'status' => 'active',
        ]);
        $count++;
    }
}

echo "Successfully created {$count} tax object records.\n";
