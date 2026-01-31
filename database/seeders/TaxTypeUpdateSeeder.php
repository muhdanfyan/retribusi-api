<?php

namespace Database\Seeders;

use App\Models\Opd;
use App\Models\RetributionType;
use App\Models\RetributionClassification;
use App\Models\Zone;
use App\Models\RetributionRate;
use Illuminate\Database\Seeder;

class TaxTypeUpdateSeeder extends Seeder
{
    public function run(): void
    {
        $bapenda = Opd::where('code', 'BAPENDA')->first();
        if (!$bapenda) {
            $this->command->error('BAPENDA OPD not found. Please run BapendaMasterDataSeeder first.');
            return;
        }

        // 1. Ensure Retribusi PKD and PBJT exist with correct schema
        $unifiedSchema = [
            ['key' => 'tanggal_pendataan', 'label' => 'Tanggal Pendataan', 'type' => 'date', 'required' => true],
            ['key' => 'nama_jenis_usaha', 'label' => 'Nama Jenis Usaha', 'type' => 'text', 'required' => true],
            ['key' => 'omset_penjualan', 'label' => 'Omset Penjualan (Rata-rata/Bulan)', 'type' => 'number', 'required' => true],
            ['key' => 'tarif_pajak', 'label' => 'Tarif Pajak (%)', 'type' => 'text', 'required' => true],
            ['key' => 'keterangan_usaha', 'label' => 'Keterangan Usaha', 'type' => 'select', 'options' => ['Aktif', 'Tidak Aktif'], 'required' => true],
            ['key' => 'lokasi_google_maps', 'label' => 'Link Lokasi Google Maps', 'type' => 'text', 'required' => true],
        ];

        $unifiedRequirements = [
            ['key' => 'foto_lokasi_open_kamera', 'label' => 'Dokumentasi Open Kamera', 'required' => true],
            ['key' => 'formulir_data_dukung', 'label' => 'Upload Formulir Data Dukung', 'required' => true],
        ];

        $pkdType = RetributionType::where('opd_id', $bapenda->id)->where('name', 'Retribusi PKD')->first();
        if ($pkdType) {
            $pkdType->update([
                'form_schema' => $unifiedSchema,
                'requirements' => $unifiedRequirements
            ]);
        }

        $pbjtType = RetributionType::where('opd_id', $bapenda->id)->where('name', 'PBJT')->first();
        if ($pbjtType) {
            $pbjtType->update([
                'form_schema' => $unifiedSchema,
                'requirements' => $unifiedRequirements
            ]);
        }

        // 2. Add New Zones for Retribusi PKD (Pengelolaan Kekayaan Daerah)
        $pkdCls = RetributionClassification::where('opd_id', $bapenda->id)->where('code', 'PTKU')->first();
        
        $pkdLocations = [
            'Pantai Kamali' => ['code' => 'Z-KML', 'lat' => -5.4626, 'lng' => 122.6015],
            'Kota Mara' => ['code' => 'Z-KMR', 'lat' => -5.4678, 'lng' => 122.6048],
            'Stadion' => ['code' => 'Z-STD', 'lat' => -5.4745, 'lng' => 122.6112],
            'Pasar Buah' => ['code' => 'Z-PSB', 'lat' => -5.4645, 'lng' => 122.5990],
            'Wantiro' => ['code' => 'Z-WTR', 'lat' => -5.4412, 'lng' => 122.5856],
        ];

        foreach ($pkdLocations as $name => $info) {
            Zone::updateOrCreate(
                ['name' => $name, 'opd_id' => $bapenda->id],
                [
                    'retribution_type_id' => $pkdType->id ?? null,
                    'retribution_classification_id' => $pkdCls->id ?? null,
                    'code' => $info['code'],
                    'multiplier' => 1.0,
                    'amount' => 0,
                    'latitude' => $info['lat'],
                    'longitude' => $info['lng'],
                ]
            );
        }

        // 3. Add New Zones for Pajak Parkir (under PBJT - Jasa Parkir)
        $parkirCls = RetributionClassification::where('opd_id', $bapenda->id)->where('code', 'PBJT-PRK')->first();
        
        $parkirLocations = [
            'Bandara' => ['code' => 'Z-BDR', 'lat' => -5.4856, 'lng' => 122.6689],
            'Lipo' => ['code' => 'Z-LPO', 'lat' => -5.4712, 'lng' => 122.6089],
            'Pantai Nirwana' => ['code' => 'Z-NRW', 'lat' => -5.5523, 'lng' => 122.5645],
        ];

        foreach ($parkirLocations as $name => $info) {
            Zone::updateOrCreate(
                ['name' => $name, 'opd_id' => $bapenda->id],
                [
                    'retribution_type_id' => $pbjtType->id ?? null,
                    'retribution_classification_id' => $parkirCls->id ?? null,
                    'code' => $info['code'],
                    'multiplier' => 1.0,
                    'amount' => 0,
                    'latitude' => $info['lat'],
                    'longitude' => $info['lng'],
                ]
            );
        }

        $this->command->info('Tax types and zones updated successfully.');
    }
}
