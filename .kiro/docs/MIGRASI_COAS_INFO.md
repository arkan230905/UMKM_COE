# Informasi Migrasi Tabel COAS

## Struktur Tabel

Tabel `coas` (Chart of Accounts) adalah tabel utama untuk akuntansi.

**File Migrasi Utama:** `2026_05_15_000001_create_coas_table.php`

### Kolom-Kolom Penting

```
- id (primary key)
- user_id (multi-tenant)
- company_id (multi-tenant, FK ke tabel 'perusahaan')
- kode_akun (unique per company)
- nama_akun
- tipe_akun (Asset, Liability, Equity, Revenue, Expense, Beban)
- kategori_akun
- is_akun_header (untuk hierarchy)
- kode_induk (FK ke coas.kode_akun untuk hierarchy)
- saldo_normal (debit/kredit)
- saldo_awal (MANUAL, default 0)
- tanggal_saldo_awal
- posted_saldo_awal
- keterangan
- nomor_rekening (untuk akun bank)
- atas_nama (untuk akun bank)
- timestamps
```

## Catatan Penting

1. **Nama Tabel:** Gunakan `coas`, BUKAN `accounts`
2. **Multi-tenant:** Kode akun unique per company (constraint: kode_akun + company_id)
3. **Saldo Awal:** Bersifat manual, tidak otomatis
4. **Hierarchy:** Menggunakan kode_induk yang merujuk ke kode_akun

## Model

Model: `App\Models\Coa`

```php
protected $table = 'coas';
```

## Sebelum Membuat Migrasi Baru

Selalu cek apakah kolom sudah ada:

```php
if (!Schema::hasColumn('coas', 'nama_kolom')) {
    Schema::table('coas', function (Blueprint $table) {
        $table->string('nama_kolom')->nullable();
    });
}
```

---

**Terakhir diperbarui:** 14 Mei 2026
