# Perbaikan Laporan Kas dan Bank

## 🔍 Masalah yang Ditemukan

1. **Saldo Awal = 0** - Tidak mengambil saldo awal dari COA
2. **Transaksi Masuk/Keluar = 0** - Tidak ada mapping antara COA dan Accounts
3. **Filter Akun Tidak Tepat** - Mengambil semua akun aset, bukan hanya Kas & Bank

## ✅ Solusi yang Diterapkan

### 1. Perbaikan Filter Akun Kas & Bank

**Sebelum:**
```php
$akunKasBank = Coa::where('kode_akun', 'like', '1%')->get();
```

**Sesudah:**
```php
$akunKasBank = Coa::where(function($query) {
    $query->where('kategori', 'Kas & Bank')
          ->orWhere('nama_akun', 'like', '%Kas%')
          ->orWhere('nama_akun', 'like', '%Bank%')
          ->orWhere('kode_akun', 'like', '1-11%') // Kas
          ->orWhere('kode_akun', 'like', '1-12%') // Bank
          ->orWhere('kode_akun', 'like', '101%')
          ->orWhere('kode_akun', 'like', '102%');
})
->where('is_akun_header', false) // Hanya akun detail
->get();
```

### 2. Perbaikan Perhitungan Saldo Awal

**Formula Lengkap:**
```
Saldo Awal = Saldo Awal COA + Mutasi Sebelum Periode

Dimana:
- Saldo Awal COA = Dari kolom saldo_awal di tabel coas (Neraca Saldo)
- Mutasi Sebelum Periode = Total Debit - Total Credit sebelum start_date
```

**Implementasi:**
```php
private function getSaldoAwal($akun, $startDate)
{
    // 1. Ambil saldo awal dari COA (neraca saldo)
    $saldoAwalCoa = $akun->saldo_awal ?? 0;
    
    // 2. Cari account_id yang sesuai dengan kode_akun
    $account = DB::table('accounts')
        ->where('code', $akun->kode_akun)
        ->first();
    
    if (!$account) {
        return $saldoAwalCoa;
    }
    
    // 3. Hitung mutasi sebelum periode
    $mutasi = JournalLine::where('account_id', $account->id)
        ->whereHas('entry', function($query) use ($startDate) {
            $query->where('tanggal', '<', $startDate);
        })
        ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
        ->first();
    
    // 4. Untuk Kas & Bank (Aset): Saldo = Saldo Awal + Debit - Credit
    return $saldoAwalCoa + ($mutasi->total_debit ?? 0) - ($mutasi->total_credit ?? 0);
}
```

### 3. Perbaikan Perhitungan Transaksi Masuk/Keluar

**Transaksi Masuk (Debit):**
```php
private function getTransaksiMasuk($akun, $startDate, $endDate)
{
    // Cari account_id dari tabel accounts berdasarkan kode_akun
    $account = DB::table('accounts')
        ->where('code', $akun->kode_akun)
        ->first();
    
    if (!$account) return 0;
    
    // Hitung total debit dalam periode
    return JournalLine::where('account_id', $account->id)
        ->whereHas('entry', function($query) use ($startDate, $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        })
        ->sum('debit');
}
```

**Transaksi Keluar (Kredit):**
```php
private function getTransaksiKeluar($akun, $startDate, $endDate)
{
    $account = DB::table('accounts')
        ->where('code', $akun->kode_akun)
        ->first();
    
    if (!$account) return 0;
    
    return JournalLine::where('account_id', $account->id)
        ->whereHas('entry', function($query) use ($startDate, $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        })
        ->sum('credit');
}
```

### 4. Perbaikan Detail Transaksi

Menambahkan mapping jenis transaksi yang lebih informatif:

```php
private function getJenisTransaksi($entry)
{
    $jenisMap = [
        'penjualan' => 'Penjualan',
        'pembelian' => 'Pembelian',
        'expense_payment' => 'Pembayaran Beban',
        'pelunasan_utang' => 'Pelunasan Utang',
        'penggajian' => 'Penggajian',
        'retur' => 'Retur',
        'produksi' => 'Produksi',
        'ap_settlement' => 'Pelunasan AP',
        'saldo_awal' => 'Saldo Awal',
    ];
    
    return $jenisMap[$entry->reference_type ?? ''] ?? 'Jurnal Umum';
}
```

## 🔄 Alur Data Lengkap

```
┌─────────────────────────────────────────────────────────────┐
│                    LAPORAN KAS & BANK                       │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌─────────────────┐
                    │  Filter Akun    │
                    │  Kas & Bank     │
                    └─────────────────┘
                              │
                ┌─────────────┴─────────────┐
                │                           │
                ▼                           ▼
    ┌───────────────────┐       ┌───────────────────┐
    │   Tabel COAS      │       │  Tabel ACCOUNTS   │
    │─────────────────  │       │─────────────────  │
    │ • kode_akun       │◄─────►│ • code            │
    │ • nama_akun       │ Match │ • id              │
    │ • saldo_awal      │       │                   │
    │ • kategori        │       │                   │
    └───────────────────┘       └───────────────────┘
                │                           │
                │                           │
                ▼                           ▼
    ┌───────────────────┐       ┌───────────────────┐
    │  SALDO AWAL COA   │       │  JOURNAL_LINES    │
    │  (Neraca Saldo)   │       │─────────────────  │
    └───────────────────┘       │ • account_id      │
                │               │ • debit           │
                │               │ • credit          │
                │               │ • journal_entry_id│
                │               └───────────────────┘
                │                           │
                │                           │
                └─────────────┬─────────────┘
                              │
                              ▼
                    ┌─────────────────┐
                    │ PERHITUNGAN     │
                    │─────────────────│
                    │ Saldo Awal =    │
                    │   COA.saldo_awal│
                    │   + Σ(Debit)    │
                    │   - Σ(Credit)   │
                    │   (< start_date)│
                    │                 │
                    │ Transaksi Masuk=│
                    │   Σ(Debit)      │
                    │   (periode)     │
                    │                 │
                    │ Transaksi Keluar│
                    │   Σ(Credit)     │
                    │   (periode)     │
                    │                 │
                    │ Saldo Akhir =   │
                    │   Saldo Awal +  │
                    │   Masuk - Keluar│
                    └─────────────────┘
                              │
                              ▼
                    ┌─────────────────┐
                    │  TAMPILAN       │
                    │  LAPORAN        │
                    └─────────────────┘
```

## 📊 Contoh Perhitungan

### Akun: 101 - Kas

**Data:**
- Saldo Awal COA (Neraca Saldo): Rp 10.000.000
- Transaksi sebelum 01/11/2025:
  - Debit: Rp 5.000.000
  - Credit: Rp 2.000.000
- Transaksi periode 01/11 - 30/11/2025:
  - Debit: Rp 42.160.000
  - Credit: Rp 1.450.000

**Perhitungan:**
```
Saldo Awal (per 01/11/2025):
= Saldo Awal COA + Debit sebelum periode - Credit sebelum periode
= 10.000.000 + 5.000.000 - 2.000.000
= Rp 13.000.000

Transaksi Masuk (01/11 - 30/11):
= Rp 42.160.000

Transaksi Keluar (01/11 - 30/11):
= Rp 1.450.000

Saldo Akhir (per 30/11/2025):
= Saldo Awal + Transaksi Masuk - Transaksi Keluar
= 13.000.000 + 42.160.000 - 1.450.000
= Rp 53.710.000
```

## 🔧 Langkah Implementasi

### 1. Pastikan Data COA Lengkap

Cek apakah semua akun Kas & Bank sudah punya saldo_awal:

```sql
SELECT kode_akun, nama_akun, saldo_awal, kategori
FROM coas
WHERE kategori = 'Kas & Bank'
   OR nama_akun LIKE '%Kas%'
   OR nama_akun LIKE '%Bank%';
```

### 2. Pastikan Mapping COA ↔ Accounts

Cek apakah kode_akun di COA sama dengan code di Accounts:

```sql
SELECT c.kode_akun, c.nama_akun, a.code, a.name
FROM coas c
LEFT JOIN accounts a ON c.kode_akun = a.code
WHERE c.kategori = 'Kas & Bank';
```

Jika ada yang tidak match, buat/update data di accounts:

```sql
INSERT INTO accounts (code, name, type, created_at, updated_at)
SELECT kode_akun, nama_akun, 'asset', NOW(), NOW()
FROM coas
WHERE kategori = 'Kas & Bank'
  AND kode_akun NOT IN (SELECT code FROM accounts);
```

### 3. Pastikan Journal Entries Lengkap

Cek apakah semua transaksi sudah tercatat di journal_entries:

```sql
-- Penjualan
SELECT COUNT(*) FROM penjualans WHERE id NOT IN (
    SELECT reference_id FROM journal_entries WHERE reference_type = 'penjualan'
);

-- Pembelian
SELECT COUNT(*) FROM pembelians WHERE id NOT IN (
    SELECT reference_id FROM journal_entries WHERE reference_type = 'pembelian'
);

-- Expense Payment
SELECT COUNT(*) FROM expense_payments WHERE id NOT IN (
    SELECT reference_id FROM journal_entries WHERE reference_type = 'expense_payment'
);
```

### 4. Test Laporan

```bash
# Akses laporan
http://localhost:8000/laporan/kas-bank

# Test dengan filter periode
http://localhost:8000/laporan/kas-bank?start_date=2025-11-01&end_date=2025-11-30
```

## ✅ Checklist Validasi

- [ ] Saldo Awal tidak lagi 0 (kecuali memang belum ada transaksi)
- [ ] Transaksi Masuk menampilkan total debit yang benar
- [ ] Transaksi Keluar menampilkan total credit yang benar
- [ ] Saldo Akhir = Saldo Awal + Masuk - Keluar
- [ ] Total Kas dan Bank sesuai dengan jumlah saldo akhir semua akun
- [ ] Detail transaksi bisa dibuka dan menampilkan data yang benar
- [ ] Filter periode berfungsi dengan baik

## 🐛 Troubleshooting

### Masalah: Saldo Awal masih 0

**Penyebab:**
- Kolom saldo_awal di tabel coas belum diisi
- Belum ada transaksi sebelum periode

**Solusi:**
```sql
-- Update saldo awal untuk akun Kas
UPDATE coas SET saldo_awal = 10000000 WHERE kode_akun = '101';

-- Update saldo awal untuk akun Bank
UPDATE coas SET saldo_awal = 5000000 WHERE kode_akun = '102';
```

### Masalah: Transaksi Masuk/Keluar = 0

**Penyebab:**
- Tidak ada mapping antara COA dan Accounts
- Belum ada journal entries untuk periode tersebut

**Solusi:**
```sql
-- Cek mapping
SELECT c.kode_akun, a.code 
FROM coas c 
LEFT JOIN accounts a ON c.kode_akun = a.code 
WHERE c.kode_akun = '101';

-- Jika NULL, insert ke accounts
INSERT INTO accounts (code, name, type, created_at, updated_at)
VALUES ('101', 'Kas', 'asset', NOW(), NOW());
```

### Masalah: Angka tidak sesuai

**Penyebab:**
- Ada transaksi yang belum dijurnal
- Ada duplikasi jurnal

**Solusi:**
```sql
-- Cek transaksi yang belum dijurnal
SELECT * FROM penjualans 
WHERE id NOT IN (
    SELECT reference_id FROM journal_entries 
    WHERE reference_type = 'penjualan'
);

-- Cek duplikasi
SELECT reference_type, reference_id, COUNT(*) 
FROM journal_entries 
GROUP BY reference_type, reference_id 
HAVING COUNT(*) > 1;
```

## 📝 Catatan Penting

1. **Saldo Normal Kas & Bank = Debit**
   - Bertambah saat Debit (penerimaan)
   - Berkurang saat Credit (pengeluaran)

2. **Periode Laporan**
   - Saldo Awal: Sebelum start_date
   - Transaksi: Antara start_date dan end_date
   - Saldo Akhir: Hasil perhitungan

3. **Integrasi dengan Modul Lain**
   - Setiap transaksi harus membuat journal entry
   - Reference_type harus konsisten
   - Account_id harus sesuai dengan COA

---

**Status:** ✅ Perbaikan Complete
**Tested:** Menunggu testing dengan data real
