# Database Schema
## Sistem Retribusi dan Pendapatan Daerah

---

## Entity Relationship Diagram

```mermaid
erDiagram
    USERS ||--o{ TAX_OBJECTS : manages
    USERS ||--o{ VERIFICATIONS : performs
    USERS ||--o{ BILLS : creates
    
    TAX_TYPES ||--o{ TAX_OBJECTS : categorizes
    TAX_OBJECTS ||--o{ VERIFICATIONS : has
    TAX_OBJECTS ||--o{ BILLS : generates
    
    TAXPAYERS ||--o{ TAX_OBJECTS : owns
    BILLS ||--o{ PAYMENTS : receives
    
    USERS {
        uuid id PK
        string name
        string email
        string password
        enum role
        timestamps
    }
    
    TAX_TYPES {
        uuid id PK
        string code UK
        string name
        decimal tariff_percent
        text description
        json form_schema
        boolean is_active
        timestamps
    }
    
    TAXPAYERS {
        uuid id PK
        string npwpd UK
        string npwpd_lama
        string nama_usaha
        text alamat_usaha
        string nama_pemilik
        string nik_pemilik
        text alamat_pemilik
        string telepon
        string email
        timestamps
    }
    
    TAX_OBJECTS {
        uuid id PK
        uuid taxpayer_id FK
        uuid tax_type_id FK
        string kode_objek UK
        enum transaction_type
        json object_data
        enum status
        uuid created_by FK
        timestamps
    }
    
    VERIFICATIONS {
        uuid id PK
        uuid tax_object_id FK
        uuid verified_by FK
        enum status
        text notes
        string file_url
        timestamp verified_at
        timestamps
    }
    
    BILLS {
        uuid id PK
        uuid tax_object_id FK
        string bill_number UK
        decimal amount
        decimal tax_amount
        date due_date
        enum status
        uuid created_by FK
        timestamps
    }
    
    PAYMENTS {
        uuid id PK
        uuid bill_id FK
        string payment_number UK
        decimal amount
        string payment_method
        timestamp paid_at
        string receipt_url
        timestamps
    }
```

---

## Tabel Detail

### 1. users
```sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'verifier', 'cashier', 'reporter') NOT NULL,
    phone VARCHAR(15),
    avatar_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 2. revenue_types (Jenis Retribusi/Pajak)
> **Note**: Di database fisik menggunakan nama tabel `retribution_types`.

```sql
CREATE TABLE revenue_types (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    category ENUM(
        'pajak_daerah',
        'retribusi_daerah',
        'lain_lain_pad'
    ) NOT NULL,
    sub_category VARCHAR(100), -- Contoh: 'PBJT Tenaga Listrik', 'Parkir', dll
    tariff_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
    base_amount DECIMAL(15,2) DEFAULT 0,
    unit VARCHAR(50) DEFAULT 'per_bulan',
    description TEXT,
    form_schema JSON, -- Definisi field SPOPD
    requirements JSON, -- Persyaratan dokumen
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. taxpayers (Wajib Pajak)
```sql
CREATE TABLE taxpayers (
    id BIGINT PRIMARY KEY, -- Laravel default ID
    opd_id BIGINT NULL, -- Referensi ke OPD (nullable)
    nik VARCHAR(16) NOT NULL,
    name VARCHAR(255) NOT NULL, -- Nama WP
    address TEXT NULL, -- Alamat WP
    phone VARCHAR(20) NULL,
    npwpd VARCHAR(50) NULL, -- Nomor Pokok WP Daerah (Format: PXXXXXXXXXXXX)
    object_name VARCHAR(255) NULL, -- Nama objek (kios, kendaraan, dll)
    object_address VARCHAR(255) NULL, -- Alamat objek
    password VARCHAR(255) NULL, -- Hashed password (default: NIK)
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 4. tax_objects (Objek Pajak)
```sql
CREATE TABLE tax_objects (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    taxpayer_id UUID REFERENCES taxpayers(id),
    revenue_type_id UUID REFERENCES revenue_types(id) NOT NULL,
    opd_id UUID, -- Referensi ke OPD pengelola
    zone_id UUID, -- Referensi ke Zona/Wilayah
    
    nop VARCHAR(50) UNIQUE NOT NULL, -- Nomor Objek Pajak (dari Excel)
    name VARCHAR(100) NOT NULL, -- Nama OP di Excel
    address TEXT, -- Alamat OP di Excel
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    
    nomor_formulir VARCHAR(20), -- No Formulir SPOPD
    transaction_type ENUM('perekaman_data', 'pemutakhiran_data', 'penghapusan_data') NOT NULL,
    
    -- Data objek spesifik per jenis pajak (dinamis sesuai form_schema)
    object_data JSON NOT NULL, -- Disimpan di 'metadata' di database fisik
    
    -- Pernyataan subjek pajak
    nama_penandatangan VARCHAR(100),
    tanggal_pernyataan DATE,
    tanda_tangan_url VARCHAR(255),
    
    status ENUM('draft', 'submitted', 'verified', 'rejected', 'active', 'inactive') DEFAULT 'draft',
    approved_at TIMESTAMP,
    approved_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 5. verifications
```sql
CREATE TABLE verifications (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tax_object_id UUID REFERENCES tax_objects(id) NOT NULL,
    verified_by UUID REFERENCES users(id),
    
    -- Petugas Pendata
    pendata_tanggal DATE,
    pendata_nama_jelas VARCHAR(100),
    pendata_nip VARCHAR(20),
    pendata_tanda_tangan_url VARCHAR(255),
    
    -- Pejabat Berwenang
    pejabat_tanggal DATE,
    pejabat_nama_jelas VARCHAR(100),
    pejabat_nip VARCHAR(20),
    pejabat_tanda_tangan_url VARCHAR(255),
    
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    file_url VARCHAR(255),
    verified_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 6. bills (Tagihan)
```sql
CREATE TABLE bills (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tax_object_id UUID REFERENCES tax_objects(id) NOT NULL,
    bill_number VARCHAR(50) UNIQUE NOT NULL,
    
    -- Periode tagihan
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    
    -- Nilai
    base_amount DECIMAL(15,2) NOT NULL,
    tax_percent DECIMAL(5,2) NOT NULL,
    tax_amount DECIMAL(15,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    
    -- Tanggal
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    
    status ENUM('draft', 'issued', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 7. payments (Pembayaran)
```sql
CREATE TABLE payments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    bill_id UUID REFERENCES bills(id) NOT NULL,
    payment_number VARCHAR(50) UNIQUE NOT NULL,
    
    amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('cash', 'transfer', 'qris', 'virtual_account') NOT NULL,
    
    -- Bukti pembayaran
    receipt_url VARCHAR(255),
    reference_number VARCHAR(100),
    
    paid_at TIMESTAMP NOT NULL,
    received_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## Contoh object_data per Jenis Pajak

### Pajak Hotel
```json
{
  "jenis_hotel": "bintang_tiga",
  "jaringan_hotel": {
    "status": "nasional",
    "nama_jaringan": "Aston"
  },
  "tipe_kamar": [
    {
      "tipe": "Standard",
      "jumlah_operasional": 20,
      "jumlah_non_operasional": 2,
      "tarif_rata_rata": 350000
    },
    {
      "tipe": "Deluxe",
      "jumlah_operasional": 10,
      "jumlah_non_operasional": 0,
      "tarif_rata_rata": 500000
    }
  ],
  "fasilitas": ["restoran_kafetaria_bar", "ruang_rapat_pertemuan_ballroom"],
  "sistem_akuntansi": "pembukuan",
  "bon_penjualan": "keluaran_sistem_informasi_berbasis_komputer"
}
```

### Pajak Parkir
```json
{
  "lokasi": "Jl. Sultan Hasanuddin No. 1",
  "pengelola": "dikelola_jasa_parkir_pihak_ketiga",
  "kapasitas": {
    "luas_lahan": 5000,
    "parkir_roda_dua": 200,
    "parkir_roda_empat": 100
  },
  "waktu_operasional": {
    "hari": ["senin", "selasa", "rabu", "kamis", "jumat", "sabtu", "minggu"],
    "jam_buka": "06:00",
    "jam_tutup": "22:00"
  },
  "tarif": {
    "sepeda_motor": 3000,
    "mobil_penumpang": 5000,
    "bus_truk": 10000
  },
  "karcis": "keluaran_sistem_informasi_berbasis_komputer"
}
```

### Pajak Air Tanah
```json
{
  "tujuan_pemanfaatan": "industri_dengan_bahan_baku_air",
  "lokasi_sumber_air": "Jl. Industri Km 5",
  "mesin_pompa": {
    "nama_tipe": "Grundfos SP 46-7",
    "kapasitas_m3_jam": 50
  },
  "meteran_air": "ada"
}
```

---

## Indexes

```sql
-- Performance indexes
CREATE INDEX idx_tax_objects_taxpayer ON tax_objects(taxpayer_id);
CREATE INDEX idx_tax_objects_type ON tax_objects(tax_type_id);
CREATE INDEX idx_tax_objects_status ON tax_objects(status);
CREATE INDEX idx_bills_tax_object ON bills(tax_object_id);
CREATE INDEX idx_bills_status ON bills(status);
CREATE INDEX idx_bills_due_date ON bills(due_date);
CREATE INDEX idx_payments_bill ON payments(bill_id);
CREATE INDEX idx_verifications_tax_object ON verifications(tax_object_id);
```

---

*Schema ini dirancang untuk mendukung berbagai jenis pajak dengan struktur data yang fleksibel menggunakan JSON*
