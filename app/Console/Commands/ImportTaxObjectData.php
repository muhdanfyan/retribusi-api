<?php

namespace App\Console\Commands;

use App\Models\Opd;
use App\Models\RetributionType;
use App\Models\TaxObject;
use App\Models\Taxpayer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportTaxObjectData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-tax-object-data {--dir= : The directory to import from}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = $this->option('dir') ?: '/Users/pondokit/Downloads/OBJEK PAJAK/Data pada sistim';

        if (!File::isDirectory($directory)) {
            $this->error("Directory not found: $directory");
            return 1;
        }

        // Ensure BAPENDA OPD exists
        $bapenda = Opd::firstOrCreate(
            ['code' => 'BAPENDA'],
            [
                'name' => 'Badan Pendapatan Daerah',
                'address' => 'Jl. Balai Kota Baubau',
                'phone' => '-',
                'email' => 'bapenda@baubau.go.id',
                'status' => 'approved',
                'is_active' => true,
            ]
        );

        $files = File::files($directory);
        $totalFiles = count($files);
        $this->info("Found $totalFiles files to process.");

        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        foreach ($files as $file) {
            if ($file->getExtension() !== 'csv') {
                $bar->advance();
                continue;
            }

            $this->processFile($file->getPathname(), $bapenda);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Import completed successfully!');
    }

    protected function processFile($filePath, $opd)
    {
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            // First read to check header
            $header = fgetcsv($handle, 1000, ",");
            
            // Expected header indices: 
            // 1: NPWPD, 2: NOP, 3: Nama WP, 4: Nama OP, 5: Jenis Pajak, 6: Alamat
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($data) < 7) continue;

                $npwpd = trim($data[1]);
                $nop = trim($data[2]);
                $namaWp = trim(($data[3] ?? ''));
                $namaOp = trim(($data[4] ?? ''));
                $jenisPajak = trim(($data[5] ?? 'Pajak Lainnya'));
                $alamat = trim(($data[6] ?? ''));

                if (empty($npwpd) && empty($nop)) continue;

                // Get or create Retribution Type
                $retributionType = RetributionType::firstOrCreate(
                    ['opd_id' => $opd->id, 'name' => "Pajak $jenisPajak"],
                    [
                        'category' => $jenisPajak,
                        'is_active' => true,
                        'unit' => 'per bulan',
                        'base_amount' => 0,
                    ]
                );

                // Get or create Taxpayer
                $taxpayer = Taxpayer::updateOrCreate(
                    ['npwpd' => $npwpd],
                    [
                        'opd_id' => $opd->id,
                        'name' => $namaWp ?: 'Tanpa Nama',
                        'is_active' => true,
                    ]
                );

                // Create or update Tax Object
                if (!empty($nop)) {
                    TaxObject::updateOrCreate(
                        ['nop' => $nop],
                        [
                            'taxpayer_id' => $taxpayer->id,
                            'retribution_type_id' => $retributionType->id,
                            'opd_id' => $opd->id,
                            'name' => $namaOp ?: $taxpayer->name,
                            'address' => $alamat,
                            'status' => 'active',
                        ]
                    );
                }
            }
            fclose($handle);
        }
    }
}
