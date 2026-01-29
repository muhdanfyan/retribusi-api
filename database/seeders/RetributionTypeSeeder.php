<?php

namespace Database\Seeders;

use App\Models\Opd;
use App\Models\User;
use App\Models\RetributionType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RetributionTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure necessary OPDs exist
        $opds = [
            'DISHUB' => 'Dinas Perhubungan',
            'DISPERINDAG' => 'Dinas Perdagangan, Perindustrian, Koperasi, UKM',
            'DLH' => 'Dinas Lingkungan Hidup',
            'BAPENDA' => 'Badan Pendapatan Daerah',
            'DISKOMINFO' => 'Dinas Komunikasi dan Informatika',
            'DPMPTSP' => 'Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu',
            'BAPPERIDA' => 'Badan Perencanaan Pembangunan dan Riset Daerah',
            'PUPR' => 'Dinas Pekerjaan Umum dan Penataan Ruang',
            'DINAS_PERIKANAN' => 'Dinas Perikanan',
            'PERKIM' => 'Dinas Perumahan, Kawasan Permukiman dan Pertanahan',
            'DUKCAPIL' => 'Dinas Kependudukan dan Pencatatan Sipil',
            'DAMKAR' => 'Dinas Pemadam Kebakaran dan Penyelamatan',
            'SATPOL_PP' => 'Satuan Polisi Pamong Praja (Satpol PP)',
            'DINAS_SOSIAL' => 'Dinas Sosial dan Tenaga Kerja',
            'PARIWISATA' => 'Dinas Pariwisata dan Kebudayaan',
            'KESBANGPOL' => 'Badan Kesatuan Bangsa dan Politik',
            'BPBD' => 'Badan Penanggulangan Bencana Daerah',
            'SETDA' => 'Sekretariat Daerah Kota Baubau',
            'SETWAN' => 'Sekretariat DPRD Kota Baubau',
        ];

        foreach ($opds as $code => $name) {
            $email = strtolower($code) . '@baubaukota.go.id';
            $opd = Opd::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'status' => 'approved',
                    'is_active' => true,
                    'address' => 'Jl. Balai Kota No. 1, Bau-Bau',
                    'email' => $email,
                ]
            );

            // Create admin user for this OPD
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Admin ' . $code,
                    'password' => Hash::make('password123'),
                    'role' => 'opd',
                    'opd_id' => $opd->id,
                    'status' => 'active',
                ]
            );
        }

        $retributionTypes = [
            [
                'opd_code' => 'DISHUB',
                'name' => 'Retribusi Parkir',
                'category' => 'Parkir',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703397/retribusi/mobile/icons/parkir.png',
                'base_amount' => 2000,
                'unit' => 'per kali',
            ],
            [
                'opd_code' => 'DISHUB',
                'name' => 'Retribusi Terminal',
                'category' => 'Terminal',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703406/retribusi/mobile/icons/terminal.png',
                'base_amount' => 5000,
                'unit' => 'per masuk',
            ],
            [
                'opd_code' => 'DISHUB',
                'name' => 'Retribusi e-Ticket',
                'category' => 'Transportasi',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703393/retribusi/mobile/icons/e-Ticket.png',
                'base_amount' => 15000,
                'unit' => 'per tiket',
            ],
            [
                'opd_code' => 'DISHUB',
                'name' => 'Uji Kendaraan Bermotor',
                'category' => 'Kendaraan',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703396/retribusi/mobile/icons/kendaraan.png',
                'base_amount' => 50000,
                'unit' => 'per tahun',
            ],
            [
                'opd_code' => 'DISHUB',
                'name' => 'Jasa Kepelabuhanan',
                'category' => 'Pelabuhan',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703401/retribusi/mobile/icons/pelabuhan.png',
                'base_amount' => 25000,
                'unit' => 'per tambat',
            ],
            [
                'opd_code' => 'DISPERINDAG',
                'name' => 'Retribusi Pasar',
                'category' => 'Pasar',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703399/retribusi/mobile/icons/pasar.png',
                'base_amount' => 2000,
                'unit' => 'per hari',
            ],
            [
                'opd_code' => 'DISPERINDAG',
                'name' => 'Izin UMK',
                'category' => 'Perizinan',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703391/retribusi/mobile/icons/Izin%20UMK.png',
                'base_amount' => 0,
                'unit' => 'per izin',
            ],
            [
                'opd_code' => 'DISPERINDAG',
                'name' => 'TDP/SIUP',
                'category' => 'Perizinan',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703403/retribusi/mobile/icons/tdp-siup.png',
                'base_amount' => 0,
                'unit' => 'per pendaftaran',
            ],
            [
                'opd_code' => 'DLH',
                'name' => 'Retribusi Sampah',
                'category' => 'Kebersihan',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703402/retribusi/mobile/icons/sampah.png',
                'base_amount' => 20000,
                'unit' => 'per bulan',
            ],
            [
                'opd_code' => 'PDAM',
                'name' => 'Tagihan Air PDAM',
                'category' => 'PDAM',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703399/retribusi/mobile/icons/pdam.png',
                'base_amount' => 0,
                'unit' => 'per m3',
            ],
            [
                'opd_code' => 'PUPR',
                'name' => 'Retribusi IMB',
                'category' => 'Bangunan',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703394/retribusi/mobile/icons/imb.png',
                'base_amount' => 500000,
                'unit' => 'per izin',
            ],
            [
                'opd_code' => 'DISKOMINFO',
                'name' => 'Retribusi Menara/Internet',
                'category' => 'Telekomunikasi',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703395/retribusi/mobile/icons/internet.png',
                'base_amount' => 1000000,
                'unit' => 'per tahun',
            ],
            [
                'opd_code' => 'DISKOMINFO',
                'name' => 'Tagihan Telkom',
                'category' => 'Telekomunikasi',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703404/retribusi/mobile/icons/telkom.png',
                'base_amount' => 0,
                'unit' => 'per tagihan',
            ],
            [
                'opd_code' => 'BAPENDA',
                'name' => 'Retribusi Reklame',
                'category' => 'Pajak',
                'icon' => 'https://res.cloudinary.com/ddhgtgsed/image/upload/v1769703392/retribusi/mobile/icons/Reklame.png',
                'base_amount' => 100000,
                'unit' => 'per m2',
            ],
        ];

        foreach ($retributionTypes as $data) {
            $opd = Opd::where('code', $data['opd_code'])->first();
            if ($opd) {
                RetributionType::updateOrCreate(
                    ['opd_id' => $opd->id, 'name' => $data['name']],
                    [
                        'category' => $data['category'],
                        'icon' => $data['icon'],
                        'base_amount' => $data['base_amount'],
                        'unit' => $data['unit'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
