# Sistem Retribusi dan Pendapatan Daerah
## Pemerintah Kota Bau-Bau

### Dokumen Perencanaan Implementasi Sistem

---

> [!IMPORTANT]
> **Cakupan Sistem Saat Ini**
> 
> Sistem ini **khusus mengelola data yang dimiliki BAPENDA (Badan Pendapatan Daerah)** Kota Bau-Bau. Data dan objek pajak/retribusi yang diimplementasikan adalah yang berada di bawah pengelolaan langsung BAPENDA.

---

## 1. Latar Belakang

Sistem ini dibangun untuk mengelola pendapatan daerah Kota Bau-Bau yang meliputi:
- **Retribusi Pengelolaan Kekayaan Daerah**
- **Pajak Daerah dan Retribusi Daerah (PDRD)**

### Referensi Regulasi
- Perwali Tata Cara Pemungutan PDRD

---

## 2. Kategori Pendapatan Daerah

### A. RETRIBUSI PENGELOLAAN KEKAYAAN DAERAH

| No | Objek Retribusi | Jenis | Keterangan |
|----|-----------------|-------|------------|
| 1 | **Pantai Kamali** | Wisata | Retribusi fasilitas pantai |
| 2 | **Kota Mara** | Wisata/Fasilitas | Retribusi pengelolaan |
| 3 | **Stadion** | Olahraga | Retribusi penggunaan fasilitas |
| 4 | **Pasar Buah** | Pasar | Retribusi pedagang |
| 5 | **Wantiro** | Wisata/Fasilitas | Retribusi pengunjung |

---

### B. PAJAK HOTEL DAN RESTORAN

| No | Jenis Pajak | Tarif | Keterangan |
|----|-------------|-------|------------|
| 1 | **Restoran** | 10% | Pajak makanan dan minuman |
| 2 | **Hotel** | 10% | Pajak penginapan |
| 3 | **Hiburan Malam** | **40%** | Pajak hiburan malam (tertinggi) |

---

### C. PAJAK PARKIR

| No | Lokasi | Jenis | Keterangan |
|----|--------|-------|------------|
| 1 | **Bandara** | Parkir Kendaraan | Area parkir bandara |
| 2 | **LIPO** | Parkir Kendaraan | Area parkir pusat perbelanjaan |
| 3 | **Pantai Nirwana** | Parkir Kendaraan | Area parkir wisata |

---

## 3. Jenis PBJT (Pajak Barang dan Jasa Tertentu)

Berdasarkan formulir SPOPD yang telah dianalisis:

| Kode | Jenis PBJT | Halaman Formulir |
|------|-----------|------------------|
| `tenaga_listrik` | PBJT Atas Tenaga Listrik | Halaman 11 |
| `kesenian_hiburan` | PBJT Atas Jasa Kesenian dan Hiburan | Halaman 7 |
| `air_tanah` | Pajak Air Tanah | Halaman 14 |
| `parkir` | PBJT Atas Jasa Parkir | Halaman 12 |
| `perhotelan` | PBJT Atas Jasa Perhotelan | Halaman 3D |

---

## 4. Dokumen Terkait

| No | Dokumen | Lokasi |
|----|---------|--------|
| 1 | Struktur Data SPOPD | `docs/spopd-form-structure.md` |
| 2 | JSON Schema SPOPD | `docs/spopd-form-data.json` |
| 3 | Master Data Objek Pajak | `plan/02-master-data-objek-pajak.md` |
| 4 | Rencana Implementasi | `plan/03-implementation-roadmap.md` |
| 5 | Database Schema | `plan/04-database-schema.md` |

---

## 5. Alur Proses Umum

```
┌───────────────┐    ┌─────────────────┐    ┌────────────────┐
│  Pendaftaran  │───▶│   Verifikasi    │───▶│   Penerbitan   │
│  Objek Pajak  │    │   Petugas       │    │   SKPD/SKRD    │
└───────────────┘    └─────────────────┘    └────────────────┘
                                                    │
                                                    ▼
┌───────────────┐    ┌─────────────────┐    ┌────────────────┐
│   Pelaporan   │◀───│  Validasi       │◀───│   Pembayaran   │
│   Periodik    │    │  Pembayaran     │    │   Wajib Pajak  │
└───────────────┘    └─────────────────┘    └────────────────┘
```

---

*Dokumen ini merupakan bagian dari perencanaan sistem Retribusi dan Pendapatan Daerah Kota Bau-Bau*
