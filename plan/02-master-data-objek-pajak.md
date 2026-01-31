# Master Data Objek Retribusi dan Pajak
## Pemerintah Kota Bau-Bau

---

> [!NOTE]
> **Dokumen ini berisi data objek retribusi dan pajak yang dikelola oleh BAPENDA (Badan Pendapatan Daerah) Kota Bau-Bau.**
> 
> Untuk tahap awal implementasi, sistem akan fokus pada objek-objek berikut yang berada di bawah pengelolaan BAPENDA:
> 
> **Retribusi Pengelolaan Kekayaan Daerah:**
> 1. Pantai Kamali
> 2. Kota Mara
> 3. Stadion
> 4. Pasar Buah
> 5. Wantiro
> 
> **Pajak Hotel dan Restoran:**
> 1. Restoran (10%)
> 2. Hotel (10%)
> 3. Hiburan Malam (40%)
> 
> **Pajak Parkir:**
> - Bandara
> - LIPO
> - Pantai Nirwana

---

## A. RETRIBUSI PENGELOLAAN KEKAYAAN DAERAH

### 1. Pantai Kamali
```json
{
  "kode": "RET-PKD-001",
  "nama": "Pantai Kamali",
  "jenis": "wisata_alam",
  "kategori": "pengelolaan_kekayaan_daerah",
  "lokasi": {
    "alamat": "Pantai Kamali, Kota Bau-Bau",
    "kelurahan": "",
    "kecamatan": "",
    "koordinat": null
  },
  "tarif": {
    "tiket_masuk": 0,
    "parkir_motor": 0,
    "parkir_mobil": 0,
    "fasilitas_lainnya": 0
  },
  "jam_operasional": {
    "buka": "06:00",
    "tutup": "18:00"
  },
  "status": "aktif"
}
```

### 2. Kota Mara
```json
{
  "kode": "RET-PKD-002",
  "nama": "Kota Mara",
  "jenis": "wisata_budaya",
  "kategori": "pengelolaan_kekayaan_daerah",
  "lokasi": {
    "alamat": "Kota Mara, Kota Bau-Bau",
    "kelurahan": "",
    "kecamatan": "",
    "koordinat": null
  },
  "tarif": {
    "tiket_masuk": 0,
    "parkir_motor": 0,
    "parkir_mobil": 0,
    "fasilitas_lainnya": 0
  },
  "status": "aktif"
}
```

### 3. Stadion
```json
{
  "kode": "RET-PKD-003",
  "nama": "Stadion",
  "jenis": "fasilitas_olahraga",
  "kategori": "pengelolaan_kekayaan_daerah",
  "lokasi": {
    "alamat": "Stadion Kota Bau-Bau",
    "kelurahan": "",
    "kecamatan": "",
    "koordinat": null
  },
  "tarif": {
    "sewa_harian": 0,
    "sewa_event": 0,
    "fasilitas_lainnya": 0
  },
  "kapasitas": 0,
  "status": "aktif"
}
```

### 4. Pasar Buah
```json
{
  "kode": "RET-PKD-004",
  "nama": "Pasar Buah",
  "jenis": "pasar_tradisional",
  "kategori": "pengelolaan_kekayaan_daerah",
  "lokasi": {
    "alamat": "Pasar Buah, Kota Bau-Bau",
    "kelurahan": "",
    "kecamatan": "",
    "koordinat": null
  },
  "tarif": {
    "retribusi_harian": 0,
    "retribusi_bulanan": 0
  },
  "jumlah_kios": 0,
  "jumlah_los": 0,
  "status": "aktif"
}
```

### 5. Wantiro
```json
{
  "kode": "RET-PKD-005",
  "nama": "Wantiro",
  "jenis": "wisata_alam",
  "kategori": "pengelolaan_kekayaan_daerah",
  "lokasi": {
    "alamat": "Wantiro, Kota Bau-Bau",
    "kelurahan": "",
    "kecamatan": "",
    "koordinat": null
  },
  "tarif": {
    "tiket_masuk": 0,
    "parkir_motor": 0,
    "parkir_mobil": 0
  },
  "status": "aktif"
}
```

---

## B. PAJAK HOTEL DAN RESTORAN

### 1. Restoran
```json
{
  "kode": "PAJ-PHR-REST",
  "nama": "Pajak Restoran",
  "jenis": "restoran",
  "kategori": "pajak_hotel_restoran",
  "tarif_persen": 10,
  "dasar_pengenaan": "nilai_penjualan_makanan_minuman",
  "keterangan": "Pajak atas penjualan makanan dan minuman di restoran/rumah makan"
}
```

### 2. Hotel
```json
{
  "kode": "PAJ-PHR-HOTEL",
  "nama": "Pajak Hotel",
  "jenis": "hotel",
  "kategori": "pajak_hotel_restoran",
  "tarif_persen": 10,
  "dasar_pengenaan": "nilai_pembayaran_penginapan",
  "sub_kategori": [
    {
      "kode": "bintang_lima",
      "nama": "Hotel Bintang 5",
      "tarif_persen": 10
    },
    {
      "kode": "bintang_empat",
      "nama": "Hotel Bintang 4",
      "tarif_persen": 10
    },
    {
      "kode": "bintang_tiga",
      "nama": "Hotel Bintang 3",
      "tarif_persen": 10
    },
    {
      "kode": "bintang_dua",
      "nama": "Hotel Bintang 2",
      "tarif_persen": 10
    },
    {
      "kode": "bintang_satu",
      "nama": "Hotel Bintang 1",
      "tarif_persen": 10
    },
    {
      "kode": "non_bintang",
      "nama": "Hotel Non-Bintang",
      "tarif_persen": 10
    },
    {
      "kode": "rumah_kost",
      "nama": "Rumah Kost",
      "tarif_persen": 10
    }
  ]
}
```

### 3. Hiburan Malam
```json
{
  "kode": "PAJ-PHR-HIBMALAM",
  "nama": "Pajak Hiburan Malam",
  "jenis": "hiburan_malam",
  "kategori": "pajak_hotel_restoran",
  "tarif_persen": 40,
  "dasar_pengenaan": "nilai_tagihan_kepada_pengunjung",
  "keterangan": "Tarif tertinggi sesuai ketentuan peraturan",
  "sub_kategori": [
    {
      "kode": "diskotik",
      "nama": "Diskotik",
      "tarif_persen": 40
    },
    {
      "kode": "klub_malam",
      "nama": "Klub Malam",
      "tarif_persen": 40
    },
    {
      "kode": "karaoke",
      "nama": "Karaoke",
      "tarif_persen": 40
    }
  ]
}
```

---

## C. PAJAK PARKIR

### 1. Bandara
```json
{
  "kode": "PAJ-PARKIR-BANDARA",
  "nama": "Parkir Bandara",
  "jenis": "parkir",
  "kategori": "pajak_parkir",
  "lokasi": {
    "nama": "Bandara",
    "alamat": "Bandara Betoambari, Kota Bau-Bau"
  },
  "pengelola": "dikelola_jasa_parkir_pihak_ketiga",
  "tarif": {
    "sepeda_motor": 0,
    "mobil": 0,
    "bus_truk": 0
  },
  "pajak_persen": 30,
  "status": "aktif"
}
```

### 2. LIPO (Pusat Perbelanjaan)
```json
{
  "kode": "PAJ-PARKIR-LIPO",
  "nama": "Parkir LIPO",
  "jenis": "parkir",
  "kategori": "pajak_parkir",
  "lokasi": {
    "nama": "LIPO",
    "alamat": "LIPO Mall/Plaza, Kota Bau-Bau"
  },
  "pengelola": "dikelola_jasa_parkir_pihak_ketiga",
  "tarif": {
    "sepeda_motor": 0,
    "mobil": 0
  },
  "pajak_persen": 30,
  "status": "aktif"
}
```

### 3. Pantai Nirwana
```json
{
  "kode": "PAJ-PARKIR-NIRWANA",
  "nama": "Parkir Pantai Nirwana",
  "jenis": "parkir",
  "kategori": "pajak_parkir",
  "lokasi": {
    "nama": "Pantai Nirwana",
    "alamat": "Pantai Nirwana, Kota Bau-Bau"
  },
  "pengelola": "dikelola_sendiri",
  "tarif": {
    "sepeda_motor": 0,
    "mobil": 0
  },
  "pajak_persen": 30,
  "status": "aktif"
}
```

---

## D. RINGKASAN KODE OBJEK PAJAK

| Kategori | Kode Prefix | Contoh |
|----------|-------------|--------|
| Retribusi Pengelolaan Kekayaan Daerah | `RET-PKD-` | RET-PKD-001 |
| Pajak Hotel | `PAJ-PHR-HOTEL` | PAJ-PHR-HOTEL |
| Pajak Restoran | `PAJ-PHR-REST` | PAJ-PHR-REST |
| Pajak Hiburan Malam | `PAJ-PHR-HIBMALAM` | PAJ-PHR-HIBMALAM |
| Pajak Parkir | `PAJ-PARKIR-` | PAJ-PARKIR-BANDARA |
| PBJT Tenaga Listrik | `PBJT-LISTRIK-` | PBJT-LISTRIK-001 |
| PBJT Kesenian/Hiburan | `PBJT-HIBURAN-` | PBJT-HIBURAN-001 |
| Pajak Air Tanah | `PAJ-AIRTANAH-` | PAJ-AIRTANAH-001 |
| PBJT Perhotelan | `PBJT-HOTEL-` | PBJT-HOTEL-001 |

---

## E. CATATAN IMPLEMENTASI

1. **Tarif** - Nilai tarif harus diisi sesuai dengan Peraturan Walikota (Perwali) yang berlaku
2. **Lokasi** - Data koordinat dan alamat lengkap perlu dilengkapi
3. **Status** - Objek pajak dapat berstatus `aktif`, `non_aktif`, atau `dalam_proses`
4. **Kode Unik** - Setiap objek pajak memiliki kode unik untuk identifikasi

---

*Dokumen ini harus diperbarui sesuai dengan data aktual dari Badan Pendapatan Daerah*
