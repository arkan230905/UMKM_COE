# Aset Module - API Documentation

## Overview
Modul Aset menyediakan API lengkap untuk mengelola aset tetap, termasuk perhitungan penyusutan otomatis dengan 3 metode, dan posting jurnal otomatis.

## Base URL
```
http://localhost:8000/api
```

## Authentication
Semua endpoint memerlukan Sanctum token authentication (kecuali kategori options).

Header:
```
Authorization: Bearer {token}
```

---

## Endpoints

### 1. List Aset
**GET** `/asets`

Menampilkan daftar semua aset dengan pagination.

**Query Parameters:**
- `per_page` (int, default: 15) - Jumlah item per halaman
- `status` (string) - Filter: aktif, tidak_aktif, dihapus
- `metode_penyusutan` (string) - Filter: garis_lurus, saldo_menurun, sum_of_years_digits
- `search` (string) - Cari berdasarkan nama, kode, atau kategori

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "kode_aset": "AST-202511-0001",
      "nama_aset": "Kursi Salon",
      "kategori": "Furniture & Fixtures",
      "tanggal_perolehan": "2022-11-02",
      "harga_perolehan": 4000000,
      "nilai_sisa": 2500000,
      "umur_ekonomis_tahun": 4,
      "metode_penyusutan": "garis_lurus",
      "nilai_buku": 3000000,
      "akumulasi_penyusutan": 1000000,
      "status": "aktif",
      "lokasi": "Salon Utama",
      "nomor_serial": "SL-001",
      "keterangan": "Kursi salon dari manual book",
      "created_at": "2025-11-02T12:00:00Z",
      "updated_at": "2025-11-02T12:00:00Z"
    }
  ],
  "pagination": {
    "total": 3,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

### 2. Get Detail Aset
**GET** `/asets/{id}`

Menampilkan detail aset beserta depreciation schedules.

**Response (200):**
```json
{
  "id": 1,
  "kode_aset": "AST-202511-0001",
  "nama_aset": "Kursi Salon",
  "kategori": "Furniture & Fixtures",
  "tanggal_perolehan": "2022-11-02",
  "harga_perolehan": 4000000,
  "nilai_sisa": 2500000,
  "umur_ekonomis_tahun": 4,
  "metode_penyusutan": "garis_lurus",
  "nilai_buku": 3000000,
  "akumulasi_penyusutan": 1000000,
  "status": "aktif",
  "depreciation_schedules": [
    {
      "id": 1,
      "periode_mulai": "2022-11-02",
      "periode_akhir": "2022-11-30",
      "periode_bulan": 1,
      "nilai_awal": 4000000,
      "beban_penyusutan": 31250,
      "akumulasi_penyusutan": 31250,
      "nilai_buku": 3968750,
      "status": "draft"
    }
  ]
}
```

---

### 3. Create Aset
**POST** `/asets`

Membuat aset baru.

**Request Body:**
```json
{
  "nama_aset": "Kursi Salon",
  "kategori": "Furniture & Fixtures",
  "tanggal_perolehan": "2022-11-02",
  "harga_perolehan": 4000000,
  "nilai_sisa": 2500000,
  "umur_ekonomis_tahun": 4,
  "metode_penyusutan": "garis_lurus",
  "coa_id": null,
  "lokasi": "Salon Utama",
  "nomor_serial": "SL-001",
  "keterangan": "Kursi salon dari manual book"
}
```

**Response (201):**
```json
{
  "id": 1,
  "kode_aset": "AST-202511-0001",
  "nama_aset": "Kursi Salon",
  ...
}
```

---

### 4. Update Aset
**PUT** `/asets/{id}`

Update data aset.

**Request Body:**
```json
{
  "nama_aset": "Kursi Salon Premium",
  "lokasi": "Salon Cabang",
  "status": "tidak_aktif"
}
```

**Response (200):**
```json
{
  "id": 1,
  "kode_aset": "AST-202511-0001",
  "nama_aset": "Kursi Salon Premium",
  ...
}
```

---

### 5. Delete Aset
**DELETE** `/asets/{id}`

Menghapus aset. Hanya bisa dihapus jika akumulasi penyusutan = 0.

**Response (204):** No Content

**Error (422):**
```json
{
  "message": "Tidak bisa menghapus aset yang sudah memiliki akumulasi penyusutan"
}
```

---

### 6. Generate Depreciation Schedule
**POST** `/asets/{id}/generate-schedule`

Generate depreciation schedule untuk periode tertentu (preview, belum disimpan).

**Request Body:**
```json
{
  "tanggal_mulai": "2022-11-02",
  "tanggal_akhir": "2026-11-02",
  "periodisitas": "bulanan"
}
```

**Response (200):**
```json
[
  {
    "periode_mulai": "2022-11-02",
    "periode_akhir": "2022-11-30",
    "periode_bulan": 1,
    "nilai_awal": 4000000,
    "beban_penyusutan": 31250,
    "akumulasi_penyusutan": 31250,
    "nilai_buku": 3968750
  },
  {
    "periode_mulai": "2022-12-01",
    "periode_akhir": "2022-12-31",
    "periode_bulan": 2,
    "nilai_awal": 3968750,
    "beban_penyusutan": 31250,
    "akumulasi_penyusutan": 62500,
    "nilai_buku": 3937500
  }
]
```

---

### 7. Save Depreciation Schedule
**POST** `/asets/{id}/save-schedule`

Generate dan simpan depreciation schedule ke database.

**Request Body:**
```json
{
  "tanggal_mulai": "2022-11-02",
  "tanggal_akhir": "2026-11-02",
  "periodisitas": "bulanan"
}
```

**Response (200):**
```json
{
  "message": "Schedule berhasil disimpan",
  "count": 48
}
```

---

### 8. List Depreciation Schedules
**GET** `/asets/{id}/depreciation-schedules`

Menampilkan semua depreciation schedules untuk aset tertentu.

**Response (200):**
```json
[
  {
    "id": 1,
    "aset_id": 1,
    "periode_mulai": "2022-11-02",
    "periode_akhir": "2022-11-30",
    "periode_bulan": 1,
    "nilai_awal": 4000000,
    "beban_penyusutan": 31250,
    "akumulasi_penyusutan": 31250,
    "nilai_buku": 3968750,
    "status": "draft",
    "jurnal_id": null,
    "posted_by": null,
    "posted_at": null
  }
]
```

---

### 9. Post Depreciation Schedule
**POST** `/depreciation-schedules/{id}/post`

Post depreciation schedule dan generate jurnal otomatis.

**Response (200):**
```json
{
  "message": "Schedule berhasil di-post",
  "schedule": {
    "id": 1,
    "status": "posted",
    "jurnal_id": 1,
    "posted_by": 1,
    "posted_at": "2025-11-02T12:00:00Z"
  }
}
```

---

### 10. Reverse Depreciation Schedule
**POST** `/depreciation-schedules/{id}/reverse`

Reverse (unpost) depreciation schedule dan buat reverse journal.

**Request Body:**
```json
{
  "alasan": "Koreksi data penyusutan"
}
```

**Response (200):**
```json
{
  "message": "Schedule berhasil di-reverse",
  "schedule": {
    "id": 1,
    "status": "reversed",
    "reversed_by": 1,
    "reversed_at": "2025-11-02T12:00:00Z",
    "keterangan": "Koreksi data penyusutan"
  }
}
```

---

### 11. Get Kategori Options
**GET** `/aset/kategori?jenis_aset=Aset%20Tetap`

Menampilkan kategori aset berdasarkan jenis aset (public endpoint).

**Query Parameters:**
- `jenis_aset` (string) - Jenis aset: "Aset Tetap", "Aset Lancar", "Aset Tak Berwujud"

**Response (200):**
```json
[
  "Kendaraan Operasional",
  "Peralatan Kantor",
  "Peralatan Produksi",
  "Furniture & Fixtures",
  "Gedung & Bangunan",
  "Tanah"
]
```

---

## Depreciation Methods

### 1. Garis Lurus (Straight Line)
Beban penyusutan = (Harga Perolehan - Nilai Sisa) / Umur Ekonomis

**Contoh:**
- Harga Perolehan: Rp 4.000.000
- Nilai Sisa: Rp 2.500.000
- Umur Ekonomis: 4 tahun
- Beban Penyusutan per Tahun: (4.000.000 - 2.500.000) / 4 = Rp 375.000
- Beban Penyusutan per Bulan: Rp 375.000 / 12 = Rp 31.250

### 2. Saldo Menurun (Declining Balance)
Beban penyusutan = Nilai Buku Awal × Persentase Penyusutan

Persentase dihitung otomatis jika tidak ada:
Persentase = (1 - (Nilai Sisa / Harga Perolehan)^(1/Umur)) × 100%

### 3. Sum of Years Digits
Beban penyusutan = (Sisa Umur / Total Digit Tahun) × (Harga Perolehan - Nilai Sisa)

Total Digit Tahun = n × (n + 1) / 2, dimana n = umur ekonomis

---

## Error Responses

### 400 Bad Request
```json
{
  "message": "Validation failed",
  "errors": {
    "nama_aset": ["The nama_aset field is required."],
    "harga_perolehan": ["The harga_perolehan must be a number."]
  }
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 404 Not Found
```json
{
  "message": "Not found"
}
```

### 422 Unprocessable Entity
```json
{
  "message": "Tidak bisa menghapus aset yang sudah memiliki akumulasi penyusutan"
}
```

---

## Example Usage

### Create Aset dan Generate Schedule

```bash
# 1. Create aset
curl -X POST http://localhost:8000/api/asets \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "nama_aset": "Kursi Salon",
    "kategori": "Furniture & Fixtures",
    "tanggal_perolehan": "2022-11-02",
    "harga_perolehan": 4000000,
    "nilai_sisa": 2500000,
    "umur_ekonomis_tahun": 4,
    "metode_penyusutan": "garis_lurus",
    "lokasi": "Salon Utama"
  }'

# 2. Generate schedule
curl -X POST http://localhost:8000/api/asets/1/generate-schedule \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "tanggal_mulai": "2022-11-02",
    "tanggal_akhir": "2026-11-02",
    "periodisitas": "bulanan"
  }'

# 3. Save schedule
curl -X POST http://localhost:8000/api/asets/1/save-schedule \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "tanggal_mulai": "2022-11-02",
    "tanggal_akhir": "2026-11-02",
    "periodisitas": "bulanan"
  }'

# 4. Post schedule
curl -X POST http://localhost:8000/api/depreciation-schedules/1/post \
  -H "Authorization: Bearer {token}"
```

---

## Contoh Data dari Manual Book

### 1. Kursi Salon
- Harga Perolehan: Rp 4.000.000
- Nilai Sisa: Rp 2.500.000
- Umur Ekonomis: 4 tahun
- Metode: Garis Lurus
- Beban per Bulan: Rp 31.250
- Beban per Tahun: Rp 375.000

### 2. Kursi Cuci Rambut
- Harga Perolehan: Rp 2.000.000
- Nilai Sisa: Rp 1.000.000
- Umur Ekonomis: 4 tahun
- Metode: Garis Lurus
- Beban per Bulan: Rp 20.833
- Beban per Tahun: Rp 250.000

### 3. Gedung
- Harga Perolehan: Rp 30.000.000
- Nilai Sisa: Rp 20.000.000
- Umur Ekonomis: 4 tahun
- Metode: Garis Lurus
- Beban per Bulan: Rp 208.333
- Beban per Tahun: Rp 2.500.000

---

## Notes

- Semua nilai monetary dalam Rupiah (IDR)
- Tanggal format: YYYY-MM-DD
- Kode aset auto-generated dengan format: AST-YYYYMM-XXXX
- Nilai buku selalu = Harga Perolehan - Akumulasi Penyusutan
- Aset tidak bisa dihapus jika sudah memiliki akumulasi penyusutan > 0
- Jurnal otomatis di-post saat schedule di-post
- Reverse schedule akan membuat reverse journal otomatis
