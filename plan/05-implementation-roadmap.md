# Implementation Roadmap
## Sistem Retribusi dan Pendapatan Daerah Kota Bau-Bau

---

> [!IMPORTANT]
> ## Cakupan Implementasi - Data BAPENDA
> 
> Sistem ini **saat ini fokus mengelola data yang dimiliki BAPENDA** (Badan Pendapatan Daerah) Kota Bau-Bau:
> 
> | Kategori | Objek yang Dikelola |
> |----------|---------------------|
> | **Retribusi Pengelolaan Kekayaan Daerah** | Pantai Kamali, Kota Mara, Stadion, Pasar Buah, Wantiro |
> | **Pajak Restoran** | Seluruh restoran/rumah makan (tarif 10%) |
> | **Pajak Hotel** | Seluruh hotel/penginapan (tarif 10%) |
> | **Pajak Hiburan Malam** | Diskotik, Klub Malam, Karaoke (tarif **40%**) |
> | **Pajak Parkir** | Bandara, LIPO, Pantai Nirwana |

---

## Phase 1: Foundation (Minggu 1-2)

### 1.1 Database Setup
- [ ] Finalisasi schema database
- [ ] Buat migration untuk semua tabel
- [ ] Setup seeder untuk data master
- [ ] Implementasi model Eloquent dengan relationships

### 1.2 Master Data
- [ ] Seed tax_types dengan 6 jenis retribusi/pajak
- [ ] Seed lokasi objek retribusi (Pantai Kamali, Kota Mara, dll)
- [ ] Setup tarif sesuai Perwali

### 1.3 Authentication
- [ ] User roles: Admin, Verifier, Cashier, Reporter
- [ ] Role-based access control
- [ ] API authentication (Sanctum/JWT)

---

## Phase 2: Core Features (Minggu 3-4)

### 2.1 Pendaftaran Objek Pajak (SPOPD)
- [ ] API endpoint untuk submit SPOPD
- [ ] Dynamic form berdasarkan jenis pajak
- [ ] File upload untuk dokumen pendukung
- [ ] Generate NPWPD otomatis

### 2.2 Verifikasi
- [ ] Dashboard verifikator
- [ ] Workflow approval
- [ ] Notifikasi status

### 2.3 Tagihan (Billing)
- [ ] Generate tagihan otomatis
- [ ] Kalkulasi pajak berdasarkan tarif
- [ ] Reminder jatuh tempo

---

## Phase 3: Payment & Reporting (Minggu 5-6)

### 3.1 Pembayaran
- [ ] Petugas module
- [ ] Multiple payment methods
- [ ] Generate kwitansi
- [ ] Validasi pembayaran

### 3.2 Reporting
- [ ] Laporan harian/bulanan/tahunan
- [ ] Export Excel/PDF
- [ ] Dashboard analytics
- [ ] Grafik pendapatan per jenis pajak

---

## Phase 4: Mobile App (Minggu 7-8)

### 4.1 Wajib Pajak Features
- [ ] Registrasi online
- [ ] Cek tagihan
- [ ] Riwayat pembayaran
- [ ] Notifikasi

### 4.2 Petugas Features
- [ ] Verifikasi lapangan
- [ ] Foto dokumentasi
- [ ] GPS lokasi
- [ ] Sync offline

---

## API Endpoints Summary

### Authentication
```
POST   /api/auth/login
POST   /api/auth/register
POST   /api/auth/logout
GET    /api/auth/me
```

### Tax Types
```
GET    /api/tax-types
GET    /api/tax-types/{code}
GET    /api/tax-types/{code}/form-schema
```

### Taxpayers
```
GET    /api/taxpayers
POST   /api/taxpayers
GET    /api/taxpayers/{npwpd}
PUT    /api/taxpayers/{npwpd}
```

### Tax Objects
```
GET    /api/tax-objects
POST   /api/tax-objects
GET    /api/tax-objects/{id}
PUT    /api/tax-objects/{id}
POST   /api/tax-objects/{id}/submit
```

### Verifications
```
GET    /api/verifications
GET    /api/verifications/pending
POST   /api/verifications/{id}/approve
POST   /api/verifications/{id}/reject
```

### Bills
```
GET    /api/bills
GET    /api/bills/{number}
POST   /api/bills/generate
GET    /api/bills/overdue
```

### Payments
```
GET    /api/payments
POST   /api/payments
GET    /api/payments/{number}
GET    /api/payments/{number}/receipt
```

### Reports
```
GET    /api/reports/daily
GET    /api/reports/monthly
GET    /api/reports/yearly
GET    /api/reports/by-tax-type
GET    /api/reports/export
```

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 11 |
| Database | PostgreSQL / MySQL |
| Auth | Laravel Sanctum |
| Admin | React + TypeScript |
| Mobile | React Native / Ionic |
| Storage | Local / S3 |
| Cache | Redis |

---

## Priority Features

| Priority | Feature | Status |
|----------|---------|--------|
| P0 | User Authentication | 🔄 In Progress |
| P0 | Tax Types Master Data | ⏳ Pending |
| P0 | Taxpayer Registration | ⏳ Pending |
| P1 | SPOPD Form Submission | ⏳ Pending |
| P1 | Verification Workflow | ⏳ Pending |
| P1 | Bill Generation | ⏳ Pending |
| P2 | Payment Processing | ⏳ Pending |
| P2 | Reports Dashboard | ⏳ Pending |
| P3 | Mobile App | ⏳ Pending |

---

*Dokumen ini akan diperbarui sesuai progress implementasi*
