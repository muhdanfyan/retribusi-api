<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupMasterDataHierarchy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-master-data-hierarchy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup misclassified retribution types and setup the new 4-level hierarchy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Master Data Cleanup...');

        // 1. Create High Level Type for BAPENDA if not exists
        $type = \App\Models\RetributionType::firstOrCreate(
            ['name' => 'Retribusi Pemakaian Kekayaan Daerah'],
            [
                'opd_id' => 7, // BAPENDA VPS ID
                'category' => 'Retribusi Jasa Usaha',
                'base_amount' => 0,
                'unit' => 'per_objek'
            ]
        );

        $this->info('Main Retribution Type ensured: ' . $type->name);

        // 2. Identify misclassified types (Zones currently entered as Types)
        // These are the IDs captured from VPS check: 21, 22, 23, 24, 25
        $misclassifiedIds = [21, 22, 23, 24, 25];
        
        foreach ($misclassifiedIds as $id) {
            $misType = \App\Models\RetributionType::find($id);
            if ($misType) {
                $this->info("Moving misclassified Type: {$misType->name} -> Zone");

                // Create Zone for this location
                $zone = \App\Models\Zone::updateOrCreate(
                    ['name' => $misType->name],
                    [
                        'opd_id' => $misType->opd_id,
                        'retribution_type_id' => $type->id,
                        'description' => 'Migrated from RetributionType'
                    ]
                );

                // Create a Rate (Tarif) for this Zone
                \App\Models\RetributionRate::updateOrCreate(
                    [
                        'retribution_type_id' => $type->id,
                        'zone_id' => $zone->id
                    ],
                    [
                        'opd_id' => $misType->opd_id,
                        'name' => 'Tarif Standar ' . $misType->name,
                        'amount' => 120000, // Default based on user report
                        'unit' => 'Bulan',
                        'is_active' => true
                    ]
                );

                // Update existing Tax Objects to point to the new Type and Zone
                $count = \App\Models\TaxObject::where('retribution_type_id', $id)->count();
                if ($count > 0) {
                    \App\Models\TaxObject::where('retribution_type_id', $id)->update([
                        'retribution_type_id' => $type->id,
                        'zone_id' => $zone->id
                    ]);
                    $this->info("Updated {$count} Tax Objects for {$misType->name}");
                }

                // Finally delete the misclassified type
                $misType->delete();
            }
        }

        $this->info('Master Data Cleanup Completed Successfully.');
    }
}
