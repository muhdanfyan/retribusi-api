<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Opd;
use App\Models\RetributionType;
use App\Models\Taxpayer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@retribusi.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        // Create Dev Super Admin
        User::create([
            'name' => 'Dev Super Admin',
            'email' => 'superadmin@sipanda.online',
            'password' => Hash::make('Sipanda123#'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        // Create sample OPDs with approved status
        $dishub = Opd::create([
            'name' => 'Dinas Perhubungan',
            'code' => 'DISHUB',
            'address' => 'Jl. Protokol No. 1',
            'phone' => '0401-123456',
            'email' => 'dishub@baubau.go.id',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $disperindag = Opd::create([
            'name' => 'Dinas Perindustrian dan Perdagangan',
            'code' => 'DISPERINDAG',
            'address' => 'Jl. Pasar No. 2',
            'phone' => '0401-654321',
            'email' => 'disperindag@baubau.go.id',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $dlh = Opd::create([
            'name' => 'Dinas Lingkungan Hidup',
            'code' => 'DLH',
            'address' => 'Jl. Hijau No. 3',
            'phone' => '0401-111222',
            'email' => 'dlh@baubau.go.id',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $bapenda = Opd::create([
            'name' => 'Badan Pendapatan Daerah',
            'code' => 'BAPENDA',
            'address' => 'Jl. Bapenda No. 1',
            'phone' => '0401-999888',
            'email' => 'bapenda@baubau.go.id',
            'status' => 'approved',
            'is_active' => true,
        ]);

        // Create OPD admin users
        User::create([
            'name' => 'Admin Dishub',
            'email' => 'dishub@retribusi.id',
            'password' => Hash::make('password123'),
            'role' => 'opd',
            'opd_id' => $dishub->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Admin Disperindag',
            'email' => 'disperindag@retribusi.id',
            'password' => Hash::make('password123'),
            'role' => 'opd',
            'opd_id' => $disperindag->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Admin DLH',
            'email' => 'dlh@retribusi.id',
            'password' => Hash::make('password123'),
            'role' => 'opd',
            'opd_id' => $dlh->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Admin BAPENDA',
            'email' => 'admin@bapenda.go.id',
            'password' => Hash::make('password123'),
            'role' => 'opd',
            'opd_id' => $bapenda->id,
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Kasir BAPENDA',
            'email' => 'kasir@bapenda.go.id',
            'password' => Hash::make('password123'),
            'role' => 'kasir',
            'opd_id' => $bapenda->id,
            'status' => 'active',
        ]);

        // Create retribution types for Dishub
        $parkirMobil = RetributionType::create([
            'opd_id' => $dishub->id,
            'name' => 'Retribusi Parkir Mobil',
            'category' => 'Parkir',
            'icon' => 'car',
            'base_amount' => 5000,
            'unit' => 'per jam',
            'is_active' => true,
        ]);

        $parkirMotor = RetributionType::create([
            'opd_id' => $dishub->id,
            'name' => 'Retribusi Parkir Motor',
            'category' => 'Parkir',
            'icon' => 'bike',
            'base_amount' => 2000,
            'unit' => 'per jam',
            'is_active' => true,
        ]);

        $terminal = RetributionType::create([
            'opd_id' => $dishub->id,
            'name' => 'Retribusi Terminal',
            'category' => 'Terminal',
            'icon' => 'bus',
            'base_amount' => 10000,
            'unit' => 'per bus',
            'is_active' => true,
        ]);

        // Create retribution types for Disperindag
        $kios = RetributionType::create([
            'opd_id' => $disperindag->id,
            'name' => 'Retribusi Kios Pasar',
            'category' => 'Pasar',
            'icon' => 'store',
            'base_amount' => 150000,
            'unit' => 'per bulan',
            'is_active' => true,
        ]);

        $los = RetributionType::create([
            'opd_id' => $disperindag->id,
            'name' => 'Retribusi Los Pasar',
            'category' => 'Pasar',
            'icon' => 'market',
            'base_amount' => 50000,
            'unit' => 'per bulan',
            'is_active' => true,
        ]);

        // Create retribution types for DLH
        $sampah = RetributionType::create([
            'opd_id' => $dlh->id,
            'name' => 'Retribusi Persampahan',
            'category' => 'Kebersihan',
            'icon' => 'trash',
            'base_amount' => 30000,
            'unit' => 'per bulan',
            'is_active' => true,
        ]);

        // Create sample taxpayers
        $wp1 = Taxpayer::create([
            'opd_id' => $dishub->id,
            'nik' => '7301010101010001',
            'name' => 'Budi Santoso',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '081234567890',
            'npwpd' => 'NPWPD-001',
            'object_name' => 'Lahan Parkir Mall',
            'object_address' => 'Jl. Pusat Kota No. 1',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $wp1->retributionTypes()->attach([$parkirMobil->id, $parkirMotor->id]);

        $wp2 = Taxpayer::create([
            'opd_id' => $disperindag->id,
            'nik' => '7301010101010002',
            'name' => 'Siti Aminah',
            'address' => 'Jl. Pasar No. 5',
            'phone' => '081234567891',
            'npwpd' => 'NPWPD-002',
            'object_name' => 'Kios A-01',
            'object_address' => 'Pasar Sentral Blok A',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $wp2->retributionTypes()->attach([$kios->id]);

        $wp3 = Taxpayer::create([
            'opd_id' => $dlh->id,
            'nik' => '7301010101010003',
            'name' => 'Ahmad Yani',
            'address' => 'Jl. Perumahan No. 20',
            'phone' => '081234567892',
            'npwpd' => 'NPWPD-003',
            'object_name' => 'Rumah Tinggal',
            'object_address' => 'Jl. Perumahan No. 20',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $wp3->retributionTypes()->attach([$sampah->id]);

        // Run BAPENDA Master Data Seeder
        $this->call(BapendaMasterDataSeeder::class);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Super Admin: admin@retribusi.id / password123');
        $this->command->info('OPD Dishub: dishub@retribusi.id / password123');
        $this->command->info('OPD Disperindag: disperindag@retribusi.id / password123');
        $this->command->info('OPD DLH: dlh@retribusi.id / password123');
    }
}
