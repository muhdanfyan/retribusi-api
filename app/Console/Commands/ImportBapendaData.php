<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportBapendaData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-bapenda-data {path?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import tax data from BAPENDA CSV files';

    private $opdId = 7; // BAPENDA

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $basePath = $this->argument('path') ?: '/Users/pondokit/Downloads/DATA PAJAK/Data pada sistim';
        
        if (!is_dir($basePath)) {
            $this->error("Directory not found: $basePath");
            return Command::FAILURE;
        }
        $files = [
            'Hotel.csv' => ['type_id' => 27, 'class_id' => 4],
            'Hiburan.csv' => ['type_id' => 27, 'class_id' => 6],
            'Parkir.csv' => ['type_id' => 27, 'class_id' => 5],
            'Reklame.csv' => ['type_id' => 28, 'class_id' => 8],
            'Air Bawah Tanah.csv' => ['type_id' => 31, 'class_id' => 11],
            'Mineral Non Logam dan Batuan.csv' => ['type_id' => 29, 'class_id' => 9],
            'Sarang Burung Walet.csv' => ['type_id' => 30, 'class_id' => 10],
        ];

        // Handle standard files
        foreach ($files as $filename => $ids) {
            $path = $basePath . '/' . $filename;
            if (file_exists($path)) {
                $this->importFile($path, $ids['type_id'], $ids['class_id']);
            } else {
                $this->warn("File not found: $filename");
            }
        }

        // Handle Restoran files (multiple)
        $restoranFiles = glob($basePath . '/Restoran *.csv');
        $this->info("Found " . count($restoranFiles) . " Restoran files.");
        foreach ($restoranFiles as $path) {
            $this->importFile($path, 27, 2);
        }

        return Command::SUCCESS;
    }

    private function importFile($path, $typeId, $classId)
    {
        $this->info("Importing: " . basename($path));
        
        if (($handle = fopen($path, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ","); // Skip header
            
            $batchSize = 500;
            $count = 0;
            $password = \Hash::make('password');
            $opdId = $this->opdId;

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Column mapping: No., NPWPD, NOP, Nama WP, Nama OP, Jenis Pajak, Alamat
                if (count($data) < 7) continue;
                
                $npwpd = trim($data[1]);
                $nop = trim($data[2]);
                $wpName = trim($data[3]);
                $opName = trim($data[4]);
                $address = trim($data[6]);

                if (empty($npwpd) && empty($nop)) continue;

                try {
                    // Use Cache to avoid hitting DB for same taxpayer in same file
                    $taxpayer = \App\Models\Taxpayer::firstOrCreate(
                        ['npwpd' => $npwpd],
                        [
                            'opd_id' => $opdId,
                            'name' => $wpName ?: ($opName ?: 'Unknown'),
                            'address' => $address,
                            'is_active' => true,
                            'password' => $password
                        ]
                    );

                    // Upsert Tax Object
                    \App\Models\TaxObject::updateOrCreate(
                        ['nop' => $nop ?: null],
                        [
                            'taxpayer_id' => $taxpayer->id,
                            'retribution_type_id' => $typeId,
                            'retribution_classification_id' => $classId,
                            'opd_id' => $opdId,
                            'name' => $opName ?: ($wpName ?: 'Unnamed Object'),
                            'address' => $address,
                            'status' => 'active'
                        ]
                    );

                    $count++;
                    if ($count % $batchSize == 0) {
                        $this->output->write('.');
                    }
                } catch (\Exception $e) {
                    $this->error("\nError in " . basename($path) . " line " . ($count + 2) . ": " . $e->getMessage());
                    continue;
                }
            }
            fclose($handle);
            $this->info("\nFinished " . basename($path) . ": $count records.");
        }
    }
}
