# Struktur Data Formulir SPOPD
## Surat Pendaftaran Objek Pajak Daerah - Pemerintah Kota Bau-Bau

Dokumen ini berisi struktur data lengkap dari formulir SPOPD untuk berbagai jenis Pajak Barang dan Jasa Tertentu (PBJT).

---

## Struktur Umum (Shared Fields)

Semua formulir SPOPD memiliki field-field berikut:

### Header
| Field | Tipe | Keterangan |
|-------|------|------------|
| `nomor_formulir` | string | Nomor urut formulir |
| `jenis_transaksi` | enum | `perekaman_data`, `pemutakhiran_data`, `penghapusan_data` |

### Data Subjek Pajak
| Field | Tipe | Keterangan |
|-------|------|------------|
| `npwpd` | string(20) | Nomor Pokok Wajib Pajak Daerah (format: XX-XX-XXXX-XXXXXXX) |
| `npwpd_lama` | string(20) | NPWPD Lama (jika ada perubahan) |
| `nama_usaha` | string(100) | Nama usaha/badan usaha |
| `alamat_usaha` | text | Alamat lengkap usaha |
| `nama_pemilik_pengelola` | string(100) | Nama pemilik/pengelola |
| `nik_pemilik_pengelola` | string(16) | NIK pemilik/pengelola |
| `alamat_pemilik_pengelola` | text | Alamat pemilik/pengelola |
| `nomor_telepon` | string(15) | Nomor telepon |
| `alamat_email` | string(100) | Alamat email |

### Pernyataan Subjek Pajak
| Field | Tipe | Keterangan |
|-------|------|------------|
| `nama_subjek_pajak` | string(100) | Nama yang menandatangani |
| `tanggal_pernyataan` | date | Tanggal pernyataan |
| `tanda_tangan` | blob | Tanda tangan digital |

### Identitas Pendata/Pejabat
| Field | Tipe | Keterangan |
|-------|------|------------|
| `petugas_pendata_tanggal` | date | Tanggal pendataan |
| `petugas_pendata_tanda_tangan` | blob | Tanda tangan petugas |
| `petugas_pendata_nama_jelas` | string(100) | Nama jelas petugas |
| `petugas_pendata_nip` | string(20) | NIP petugas |
| `pejabat_berwenang_tanggal` | date | Tanggal approval |
| `pejabat_berwenang_tanda_tangan` | blob | Tanda tangan pejabat |
| `pejabat_berwenang_nama_jelas` | string(100) | Nama jelas pejabat |
| `pejabat_berwenang_nip` | string(20) | NIP pejabat |

---

## 1. PBJT ATAS TENAGA LISTRIK (Halaman 11)

### Data Objek Pajak - Tenaga Listrik
| Field | Tipe | Keterangan |
|-------|------|------------|
| `golongan_usaha` | enum | `non_industri_pertambangan`, `industri`, `pertambangan`, `sosial` |
| `mesin_pembangkit_nama_tipe` | string(100) | Nama/tipe mesin pembangkit listrik |
| `mesin_pembangkit_kapasitas_daya` | integer | Kapasitas daya dalam VA |
| `penggunaan` | enum | `sumber_listrik_utama`, `sumber_listrik_cadangan`, `sumber_listrik_darurat` |

---

## 2. PBJT ATAS JASA KESENIAN DAN HIBURAN (Halaman 7)

### Data Objek Pajak - Kesenian dan Hiburan

#### Jenis Kesenian dan Hiburan
| Field | Tipe | Keterangan |
|-------|------|------------|
| `jenis_hiburan` | array[enum] | Multiple selection dari options berikut |

**Options Jenis Hiburan:**
- `bioskop_tradisional_film` - Bibenian Tradisional, Film (Bioskop/Keliling)
- `diskotik_klub_malam` - Diskotik/Klub Malam
- `sirkus_pameran_seni` - Sirkus/Pameran Seni
- `fitness_kebugaran_sanggar_senam` - Fitness Centre/Kebugaran, Sanggar Senam
- `karaoke` - Karaoke
- `permainan_bilyard` - Permainan Bilyard
- `pertunjukan_musik_tari_pameran_busana` - Pertunjukan Musik/Tari, Pameran Busana, Kontes Kecantikan
- `permainan_ketangkasan` - Permainan Ketangkasan
- `pertandingan_olahraga` - Pertandingan Olahraga

#### Waktu Pertunjukan
| Field | Tipe | Keterangan |
|-------|------|------------|
| `waktu_pertunjukan` | enum | `berjadwal_tetap_reguler`, `insidental` |

#### Penyelenggaraan Hiburan Insidental
| Field | Tipe | Keterangan |
|-------|------|------------|
| `judul_pertunjukan` | string(200) | Judul pertunjukan |
| `jumlah_kursi_penonton` | integer | Jumlah kursi penonton |
| `jumlah_hari_pertunjukan` | integer | Jumlah hari pertunjukan |

#### Jenis/Kelas Tiket dan Tarif (Array)
| Field | Tipe | Keterangan |
|-------|------|------------|
| `tiket_kelas[].no` | integer | Nomor urut |
| `tiket_kelas[].jenis_kelas` | string(50) | Jenis/kelas tiket (VIP, Regular, dll) |
| `tiket_kelas[].jumlah` | integer | Jumlah tiket |
| `tiket_kelas[].tarif_rp` | decimal(15,2) | Tarif dalam Rupiah |

#### Hiburan Kesenian Tradisional/Film/Pertunjukan Musik (Tarif Berjadwal Tetap)
| Field | Tipe | Keterangan |
|-------|------|------------|
| `jumlah_kursi` | integer | Jumlah kursi |
| `jumlah_pertunjukan` | integer | Jumlah pertunjukan per hari |
| `hari_pertunjukan` | enum | `hari` (per hari) |

#### Hiburan Permainan Bilyard/Permainan Ketangkasan/Video Games
| Field | Tipe | Keterangan |
|-------|------|------------|
| `jumlah_meja_mesin` | integer | Jumlah meja/mesin |
| `jam_buka` | time | Jam buka |
| `s_d` | time | Sampai dengan |
| `tarif_rp_per` | decimal(15,2) | Tarif per satuan waktu |

#### Hiburan Diskotik/Klub Malam
| Field | Tipe | Keterangan |
|-------|------|------------|
| `kapasitas_orang` | integer | Kapasitas dalam jumlah orang |
| `tarif_rp` | decimal(15,2) | Tarif masuk |

#### Hiburan Pusat Kebugaran/Sanggar Senam
| Field | Tipe | Keterangan |
|-------|------|------------|
| `kapasitas_orang` | integer | Kapasitas dalam jumlah orang |

#### Tarif Membership (Array)
| Field | Tipe | Keterangan |
|-------|------|------------|
| `membership[].no` | integer | Nomor urut |
| `membership[].jenis_kelas` | string(50) | Jenis/kelas membership |
| `membership[].tarif_rp` | decimal(15,2) | Tarif dalam Rupiah |

---

## 3. PBJT ATAS PAJAK AIR TANAH (Halaman 14)

### Data Objek Pajak - Air Tanah
| Field | Tipe | Keterangan |
|-------|------|------------|
| `tujuan_pemanfaatan` | enum | `non_niaga`, `niaga`, `industri_dengan_bahan_baku_air`, `pelayanan_publik_oleh_pdam` |
| `lokasi_sumber_air` | text | Lokasi sumber air |
| `mesin_pompa_nama_tipe` | string(100) | Nama/tipe mesin pompa |
| `mesin_pompa_kapasitas` | decimal(10,2) | Kapasitas dalam M³/Jam |
| `meteran_air` | enum | `ada`, `tidak_ada` |

---

## 4. PBJT ATAS JASA PARKIR (Halaman 12)

### Data Objek Pajak - Parkir
| Field | Tipe | Keterangan |
|-------|------|------------|
| `lokasi` | text | Lokasi area parkir |
| `pengelola` | enum | `dikelola_sendiri_pemilik_lahan_gedung`, `dikelola_jasa_parkir_pihak_ketiga` |

#### Kapasitas Parkir
| Field | Tipe | Keterangan |
|-------|------|------------|
| `luas_lahan` | decimal(10,2) | Luas lahan dalam m² |
| `parkir_roda_dua_kapasitas` | integer | Kapasitas kendaraan roda dua |
| `parkir_roda_empat_kapasitas` | integer | Kapasitas kendaraan roda empat |

#### Waktu Operasional
| Field | Tipe | Keterangan |
|-------|------|------------|
| `hari_operasional` | array[enum] | `senin`, `selasa`, `rabu`, `kamis`, `jumat`, `sabtu`, `minggu` |
| `jam_buka` | time | Jam buka |
| `jam_tutup` | time | Jam tutup (s/d) |
| `pengecualian_libur` | text | Pengecualian/libur jika ada |

#### Tarif (Rupiah)
| Field | Tipe | Keterangan |
|-------|------|------------|
| `tarif_sepeda` | decimal(15,2) | Tarif sepeda |
| `tarif_sepeda_motor` | decimal(15,2) | Tarif sepeda motor |
| `tarif_mobil_penumpang_pickup` | decimal(15,2) | Tarif mobil penumpang/pickup sejenisnya |
| `tarif_truk_gandengan_bus_besar` | decimal(15,2) | Tarif truk gandengan/bus besar sejenisnya |
| `tarif_truk_bus_sedang_kecil` | decimal(15,2) | Tarif truk/bus sedang/kecil sejenisnya |
| `tarif_tambahan` | decimal(15,2) | Tarif tambahan (jika ada) |

#### Karcis Parkir
| Field | Tipe | Keterangan |
|-------|------|------------|
| `jenis_karcis` | enum | `keluaran_sistem_informasi_berbasis_komputer`, `karcis_dengan_perforasi`, `karcis_biasa`, `tidak_ada` |

---

## 5. PBJT ATAS JASA PERHOTELAN (Halaman 3D)

### Data Objek Pajak - Perhotelan

#### Jenis Hotel
| Field | Tipe | Keterangan |
|-------|------|------------|
| `jenis_hotel` | enum | `bintang_lima`, `bintang_empat`, `bintang_tiga`, `bintang_dua`, `bintang_satu`, `non_bintang`, `rumah_kost` |

#### Jaringan Hotel
| Field | Tipe | Keterangan |
|-------|------|------------|
| `jaringan_hotel` | enum | `tidak_ada`, `nasional`, `internasional` |
| `nama_jaringan` | string(100) | Nama jaringan hotel (jika ada) |

#### Tipe dan Jumlah Kamar (Array)
| Field | Tipe | Keterangan |
|-------|------|------------|
| `kamar[].no` | integer | Nomor urut |
| `kamar[].tipe_kamar` | string(50) | Tipe kamar (Standard, Deluxe, Suite, dll) |
| `kamar[].jumlah_operasional` | integer | Jumlah kamar operasional |
| `kamar[].jumlah_non_operasional` | integer | Jumlah kamar non-operasional |
| `kamar[].tarif_rata_rata` | decimal(15,2) | Tarif rata-rata per kamar dalam Rupiah |

#### Fasilitas Hotel
| Field | Tipe | Keterangan |
|-------|------|------------|
| `fasilitas_hotel` | array[enum] | Multiple selection |

**Options Fasilitas:**
- `restoran_kafetaria_bar` - Restoran/Kafetaria/Bar
- `spa_pusat_kebugaran` - Spa/Pusat Kebugaran
- `diskotik_karaoke_klub_malam` - Diskotik/Karaoke/Klub Malam
- `ruang_rapat_pertemuan_ballroom` - Ruang Rapat/Pertemuan/Ballroom

#### Sistem Akuntansi
| Field | Tipe | Keterangan |
|-------|------|------------|
| `sistem_akuntansi` | enum | `pembukuan`, `pencatatan_sederhana`, `tidak_ada` |

#### Bon Penjualan (Bill)
| Field | Tipe | Keterangan |
|-------|------|------------|
| `jenis_bon_penjualan` | enum | `keluaran_sistem_informasi_berbasis_komputer`, `keluaran_mesin_cash_register`, `bon_penjualan_dengan_perforasi`, `bon_penjualan_biasa`, `tidak_ada` |

---

## Keterangan Pengisian
1. Isilah formulir dengan benar, jelas dan lengkap dengan menggunakan huruf balok/kapital
2. Kolom dengan warna abu-abu diisi oleh petugas
3. Beri tanda silang (X) pada kotak yang sesuai

---

## Catatan Implementasi

### Jenis Pajak (tax_type)
```
tenaga_listrik      = PBJT Atas Tenaga Listrik
kesenian_hiburan    = PBJT Atas Jasa Kesenian dan Hiburan
air_tanah           = Pajak Air Tanah
parkir              = PBJT Atas Jasa Parkir
perhotelan          = PBJT Atas Jasa Perhotelan
```

### Status Transaksi
```
perekaman_data     = Pendaftaran baru
pemutakhiran_data  = Update data existing
penghapusan_data   = Hapus/non-aktifkan objek pajak
```
