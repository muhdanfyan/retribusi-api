<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RetributionType;
use App\Models\RetributionClassification;
use App\Models\RetributionRate;

class UpdateJasaUsahaRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bapenda = \App\Models\Opd::where('name', 'like', '%Badan Pendapatan Daerah%')->first();
        $targetOpdId = $bapenda ? $bapenda->id : 4;

        // 1. Rename RetributionType 15 to 'Retribusi Jasa Usaha'
        $type = RetributionType::find(15);
        if ($type) {
            $type->update(['name' => 'Retribusi Jasa Usaha', 'is_active' => 1, 'opd_id' => $targetOpdId]);
        } else {
            // Try to find by old name if ID 15 is not there
            $type = RetributionType::where('name', 'Retribusi PKD')->first();
            if ($type) {
                $type->update(['name' => 'Retribusi Jasa Usaha', 'is_active' => 1, 'opd_id' => $targetOpdId]);
            }
        }

        if (!$type) {
            $this->command->error('Retribution Type not found!');
            return;
        }

        // 2. Update or Create Classification
        $classification = RetributionClassification::updateOrCreate(
            [
                'retribution_type_id' => $type->id,
                'name' => 'Penyediaan tempat kegiatan usaha (Pasar Grosir, Pertokoan, dll)'
            ],
            [
                'opd_id' => $type->opd_id,
                'code' => 'PTKU',
                'description' => 'Sesuai Struktur dan Besarnya Tarif Retribusi Jasa Usaha bagian a'
            ]
        );

        if (!$classification) {
             $this->command->error('Retribution Classification not found!');
             return;
        }

        // 3. Clear existing rates for this classification to avoid duplicates/confusion
        RetributionRate::where('retribution_classification_id', $classification->id)->delete();

        // 4. Insert new rates from image
        $rates = [
            ['name' => 'Kawasan Sentra Kuliner Islamic Center', 'amount' => 6000000, 'unit' => 'Tahun/Kios'],
            ['name' => 'Pujasera Pasar Buah Kel. Wale', 'amount' => 60000, 'unit' => 'Bulan/Lapak'],
            ['name' => 'Pujasera Kel. Murhum', 'amount' => 60000, 'unit' => 'Bulan/Lapak'],
            ['name' => 'Lahan Pelabuhan: Kantor', 'amount' => 200000, 'unit' => 'Bulan'],
            ['name' => 'Lahan Pelabuhan: Toko', 'amount' => 150000, 'unit' => 'Bulan'],
            ['name' => 'Lahan Pelabuhan: Kios/Petak', 'amount' => 120000, 'unit' => 'Bulan'],
            ['name' => 'Lahan Pelabuhan: Rumah makan/warung/cafetaria', 'amount' => 120000, 'unit' => 'Bulan'],
            ['name' => 'Lahan Pelabuhan: Los', 'amount' => 75000, 'unit' => 'Bulan'],
            ['name' => 'Lahan Pelabuhan: Spanduk', 'amount' => 50000, 'unit' => 'Bulan'],
            ['name' => 'Toko/Kios Wisata Bahari Perikanan', 'amount' => 15000000, 'unit' => 'Tahun'],
            ['name' => 'Kios Kuliner/Cendramata Batu Sori', 'amount' => 500000, 'unit' => 'Bulan'],
            ['name' => 'Pelataran: Pujasera Bukit Kolema', 'amount' => 120000, 'unit' => 'Bulan/Lapak'],
            ['name' => 'Pelataran: Pujasera Kotamara', 'amount' => 120000, 'unit' => 'Bulan/Lapak'],
            ['name' => 'Pelataran: Pujasera Pantai Kamali', 'amount' => 90000, 'unit' => 'Bulan/Lapak'],
            ['name' => 'Pelataran: Pujasera Darurat non permanen', 'amount' => 120000, 'unit' => 'Bulan/Lapak'],
        ];

        foreach ($rates as $rateData) {
            RetributionRate::create([
                'opd_id' => $classification->opd_id,
                'retribution_type_id' => $type->id,
                'retribution_classification_id' => $classification->id,
                'name' => $rateData['name'],
                'amount' => $rateData['amount'],
                'unit' => $rateData['unit'],
                'is_active' => true,
            ]);
        }

        $this->command->info('Retribution rates updated successfully from official document.');
    }
}
