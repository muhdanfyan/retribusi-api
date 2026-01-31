# Data Objek Pajak BAPENDA - Statistik & Implementasi Aktual
## Sumber: /Users/pondokit/Downloads/OBJEK PAJAK

> **Tanggal Terakhir Diperbarui**: 31 Januari 2026
> **Status**: Selesai di-import ke Database & Arsitektur Split Selesai

---

## 📊 RINGKASAN DATA AKTUAL (Hasil Import)

Setelah melalui proses deduplikasi NPWPD dan pembersihan data, berikut adalah statistik akhir yang berhasil masuk ke sistem:

| Entitas | Jumlah Records | Keterangan |
|---------|----------------|------------|
| **Wajib Pajak (Taxpayers)** | 1,295 | Deduplikasi berdasarkan NPWPD |
| **Objek Pajak (Tax Objects)** | 42,475 | Terhubung ke NOP & WP |
| **Jenis Pajak (Tax Types)** | 7 | Parkir, Hotel, Hiburan, Restoran, dll. |

---

## 🛠️ IMPLEMENTASI TEKNIS DATABASE

### 1. Perubahan Schema Database
Untuk mengakomodasi data aktual, telah dilakukan modifikasi pada tabel:
- **`tax_objects`**: Penambahan kolom `nop` (Nomor Objek Pajak) sebagai identifier unik objek.
- **`taxpayers`**: Kolom `nik` dibuat nullable untuk mendukung entitas bisnis yang hanya memiliki `npwpd`.

### 2. Automasi Import
Dibuat Artisan Command khusus `app:import-tax-object-data` yang melakukan:
- Mapping otomatis kategori pajak ke OPD (BAPENDA).
- Deduplikasi Wajib Pajak secara cerdas menggunakan NPWPD.
- Penanganan data besar (chunking) khusus untuk kategori Restoran.

---

## 🏗️ ARSITEKTUR REPOSITORY (Split)

Berdasarkan kebutuhan fokus fitur (Petugas Kasir), ekosistem aplikasi sekarang terbagi menjadi:

### 1. `retribusi-admin` (Master Admin)
- **Fokus**: Kelola OPD, User Management Global, Konfigurasi Sistem.
- **User**: Super Admin, Admin OPD.
- **Path**: `/Users/pondokit/Herd/retribusi-admin`

### 2. `retribusi-petugas` (Cashier/Petugas Portal)
- **Fokus**: Penagihan (Billing), Pembayaran (Payment), Laporan Kasir, Manajemen Wajib Pajak.
- **User**: Kasir, Verifikator, Petugas Lapangan.
- **Path**: `/Users/pondokit/Herd/retribusi-petugas`
- **Kustomisasi**: 
  - Branding: **SIPANDA Petugas**.
  - Navigasi disederhanakan hanya untuk fitur petugas (Dashboard, Billing, Reporting, Profile).
  - Penghapusan module administratif yang tidak perlu (User Mgmt, OPD Mgmt, System Admin).

---

## 📈 DETAIL PER KATEGORI (Aktual)

| Jenis Pajak | Status | Mapping OPD | Keterangan |
|-------------|--------|-------------|------------|
| Pajak Restoran | AKTIF | BAPENDA | Data terbesar (~40k objek) |
| Pajak Hotel | AKTIF | BAPENDA | Termasuk Kos & Wisma |
| Pajak Hiburan | AKTIF | BAPENDA | Termasuk rate 10% & 40% |
| Pajak Parkir | AKTIF | BAPENDA | Objek parkir komersial |
| Pajak Air Tanah | AKTIF | BAPENDA | Industri & Komersial |

---

*Dokumen ini merupakan catatan permanen hasil integrasi data dan restrukturisasi aplikasi Januari 2026.*
