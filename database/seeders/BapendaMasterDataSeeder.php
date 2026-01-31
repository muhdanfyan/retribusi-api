<?php

namespace Database\Seeders;

use App\Models\Opd;
use App\Models\RetributionType;
use App\Models\RetributionClassification;
use App\Models\Zone;
use App\Models\RetributionRate;
use Illuminate\Database\Seeder;

class BapendaMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get or Create BAPENDA OPD
        $bapenda = Opd::firstOrCreate(
            ['code' => 'BAPENDA'],
            [
                'name' => 'Badan Pendapatan Daerah',
                'address' => 'Kantor BAPENDA Kota Bau-Bau',
                'phone' => '0401-999888',
                'email' => 'bapenda@baubau.go.id',
                'status' => 'approved',
                'is_active' => true,
            ]
        );

        // --- Assets Mapping (Cloudinary Permanent URLs) ---
        $pajakLogo = 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769855477/retribusi/icons/cixchfed9fiadty2c4a1.jpg';
        $retribusiLogo = 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769855483/retribusi/icons/mqbtlhf4modvik6ikhvi.jpg';
        
        $types = [
            'PBJT' => ['cat' => 'Pajak', 'icon' => $pajakLogo],
            'Pajak Reklame' => ['cat' => 'Pajak', 'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769855480/retribusi/icons/airqm7ydazqqpsqezlrv.jpg'],
            'Pajak MBLB' => ['cat' => 'Pajak', 'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769855474/retribusi/icons/pl1ag8vgja8jwzabaavc.jpg'],
            'Pajak Sarang Burung Walet' => ['cat' => 'Pajak', 'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769855486/retribusi/icons/agqc0orhv7i9wg4a7x1t.jpg'],
            'Air Tanah' => ['cat' => 'Pajak', 'icon' => $pajakLogo],
            'BPHTB' => ['cat' => 'Pajak', 'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769855470/retribusi/icons/tjhkxpabcvlhjegvnzyf.jpg'],
            'Opsen PKB' => ['cat' => 'Pajak', 'icon' => $pajakLogo],
            'Opsen BBNKB' => ['cat' => 'Pajak', 'icon' => $pajakLogo],
            'Retribusi PKD' => ['cat' => 'Retribusi', 'icon' => $retribusiLogo],
            'Retribusi Jasa Umum' => ['cat' => 'Retribusi', 'icon' => $retribusiLogo],
            'Retribusi Perizinan Tertentu' => ['cat' => 'Retribusi', 'icon' => $retribusiLogo],
        ];

        // --- SCHEMA DEFINITIONS (Aligned with Google Form) ---
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

        // 2. SEED TYPES
        $typeModels = [];
        foreach ($types as $name => $info) {
            $typeModels[$name] = RetributionType::updateOrCreate(
                ['opd_id' => $bapenda->id, 'name' => $name],
                [
                    'category' => $info['cat'],
                    'icon' => $info['icon'],
                    'is_active' => true,
                    'form_schema' => $unifiedSchema,
                    'requirements' => $unifiedRequirements,
                ]
            );
        }

        // 3. SEED CLASSIFICATIONS
        $pbjtSubs = [
            'PBJT - Makan dan Minum' => 'PBJT-MNM',
            'PBJT - Tenaga Listrik' => 'PBJT-LIS',
            'PBJT - Jasa Perhotelan' => 'PBJT-HTL',
            'PBJT - Jasa Parkir' => 'PBJT-PRK',
            'PBJT - Jasa Kesenian dan Hiburan' => 'PBJT-HBR',
        ];

        foreach ($pbjtSubs as $name => $code) {
            RetributionClassification::updateOrCreate(
                ['retribution_type_id' => $typeModels['PBJT']->id, 'name' => $name],
                ['opd_id' => $bapenda->id, 'code' => $code]
            );
        }

        $pkdCls = RetributionClassification::updateOrCreate(
            ['retribution_type_id' => $typeModels['Retribusi PKD']->id, 'name' => 'Penyediaan Tempat Kegiatan Usaha'],
            ['opd_id' => $bapenda->id, 'code' => 'PTKU']
        );

        foreach ($typeModels as $typeName => $model) {
            if ($typeName === 'PBJT' || $typeName === 'Retribusi PKD') continue;
            RetributionClassification::updateOrCreate(
                ['retribution_type_id' => $model->id, 'name' => $typeName],
                ['opd_id' => $bapenda->id, 'code' => $model->category === 'Pajak' ? 'TAX' : 'RET']
            );
        }

        // 4. SEED ZONES (Specific Jasa Usaha Locations)
        $jasaUsahaLocationData = [
            'Islamic Center' => ['code' => 'Z-IC', 'lat' => -5.478950, 'lng' => 122.597500, 'rates' => [['name' => 'Kios Sentra Kuliner', 'amount' => 6000000, 'unit' => 'Tahun/Kios']]],
            'Pasar Buah Wale' => ['code' => 'Z-PBW', 'lat' => -5.4645, 'lng' => 122.5990, 'rates' => [['name' => 'Lapak Pujasera', 'amount' => 60000, 'unit' => 'Bulan/Lapak']]],
            'Murhum' => ['code' => 'Z-MRH', 'lat' => -5.4578, 'lng' => 122.5954, 'rates' => [['name' => 'Lapak Pujasera', 'amount' => 60000, 'unit' => 'Bulan/Lapak']]],
            'Pelabuhan' => ['code' => 'Z-PLB', 'lat' => -5.4577, 'lng' => 122.5954, 'rates' => [['name' => 'Sewa Kantor', 'amount' => 200000, 'unit' => 'Bulan']]],
            'Wisata Bahari' => ['code' => 'Z-WB', 'lat' => -5.4950, 'lng' => 122.5850, 'rates' => [['name' => 'Sewa Toko/Kios', 'amount' => 15000000, 'unit' => 'Tahun']]],
            'Batu Sori' => ['code' => 'Z-BS', 'lat' => -5.4700, 'lng' => 122.6186, 'rates' => [['name' => 'Kios Kuliner', 'amount' => 500000, 'unit' => 'Bulan']]],
            'Pujasera Pelataran' => ['code' => 'Z-PLT', 'lat' => -5.4700, 'lng' => 122.6048, 'rates' => [['name' => 'Kotamara', 'amount' => 120000, 'unit' => 'Lapak/Bulan']]],
        ];

        foreach ($jasaUsahaLocationData as $zoneName => $info) {
            $zone = Zone::updateOrCreate(
                ['name' => $zoneName, 'opd_id' => $bapenda->id],
                [
                    'retribution_type_id' => $typeModels['Retribusi PKD']->id,
                    'retribution_classification_id' => $pkdCls->id,
                    'code' => $info['code'],
                    'multiplier' => 1.0,
                    'amount' => 0,
                    'latitude' => $info['lat'],
                    'longitude' => $info['lng'],
                ]
            );

            foreach ($info['rates'] as $rateData) {
                RetributionRate::updateOrCreate(
                    ['opd_id' => $bapenda->id, 'zone_id' => $zone->id, 'name' => $rateData['name']],
                    [
                        'retribution_type_id' => $typeModels['Retribusi PKD']->id,
                        'retribution_classification_id' => $pkdCls->id,
                        'amount' => $rateData['amount'],
                        'unit' => $rateData['unit'],
                        'is_active' => true
                    ]
                );
            }
        }

        // --- TAX RATES (Perwali Sync) ---
        $hiburanCls = RetributionClassification::where('code', 'PBJT-HBR')->first();
        if ($hiburanCls) {
            RetributionRate::updateOrCreate(['retribution_classification_id' => $hiburanCls->id, 'name' => 'Tarif Hiburan Umum'], ['opd_id' => $bapenda->id, 'retribution_type_id' => $typeModels['PBJT']->id, 'amount' => 10, 'unit' => '%', 'is_active' => true]);
            RetributionRate::updateOrCreate(['retribution_classification_id' => $hiburanCls->id, 'name' => 'Tarif Khusus'], ['opd_id' => $bapenda->id, 'retribution_type_id' => $typeModels['PBJT']->id, 'amount' => 40, 'unit' => '%', 'is_active' => true]);
        }

        $parkirCls = RetributionClassification::where('code', 'PBJT-PRK')->first();
        if ($parkirCls) {
            RetributionRate::updateOrCreate(['retribution_classification_id' => $parkirCls->id, 'name' => 'Tarif Parkir'], ['opd_id' => $bapenda->id, 'retribution_type_id' => $typeModels['PBJT']->id, 'amount' => 30, 'unit' => '%', 'is_active' => true]);
        }
    }
}
