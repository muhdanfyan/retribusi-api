<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Opd;
use Illuminate\Support\Facades\Hash;

class ImportPetugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bapenda = Opd::where('name', 'like', '%Badan Pendapatan Daerah%')
            ->orWhere('name', 'like', '%BAPENDA%')
            ->first();

        if (!$bapenda) {
            $this->command->error('OPD BAPENDA not found!');
            return;
        }

        $csvFile = base_path('DATA PAJAK/Daftar E-mail Bid. Pengawasan & Pengembangan.csv');
        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found at: {$csvFile}. Please ensure the file is in the root directory under 'DATA PAJAK/'.");
            return;
        }

        $handle = fopen($csvFile, 'r');
        $row = 0;
        $count = 0;

        // Default password for all imported users
        $password = Hash::make('sipanda123');

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;
            // Skip headers and empty rows (Rows 1-7 in the provided file are noise/headers)
            if ($row < 8) continue;
            
            // Expected columns: 0: NO, 1: NAME, 4: EMAIL, 5: NO HP
            if (empty($data[4])) continue;

            $name = trim($data[1]);
            $email = trim($data[4]);
            $phone = !empty($data[5]) ? trim($data[5]) : null;

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => $password,
                    'role' => 'petugas',
                    'opd_id' => $bapenda->id,
                    'status' => 'active',
                    'phone' => $phone,
                    'nik' => null, // NIK not in CSV, will be filled manually via UI
                ]
            );
            $count++;
        }
        fclose($handle);

        $this->command->info("Successfully imported {$count} petugas to OPD: {$bapenda->name}");
    }
}
