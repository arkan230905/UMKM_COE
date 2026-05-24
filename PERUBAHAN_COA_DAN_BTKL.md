# Perubahan COA dan Tabel BTKL

## 📋 Ringkasan Perubahan

### 1. **COA Default untuk User Baru**
User baru yang mendaftar sekarang akan mendapatkan **COA Ayam Ketumbar** secara otomatis (bukan COA Jasuke).

### 2. **Tabel BTKL Disederhanakan**
Kolom yang tidak digunakan telah dihapus dari tabel `btkls`:
- ❌ `tarif_per_jam` (dihapus)
- ❌ `satuan` (dihapus)
- ❌ `kapasitas_per_jam` (dihapus)

Sekarang menggunakan **pembebanan per produk** dari tabel `jabatans` (kolom `tarif_produk`).

---

## 🔄 Perubahan Detail

### 1. COA Default (DefaultCoaSeeder.php)

**File yang Diubah:**
- `database/seeders/DefaultCoaSeeder.php`

**Perubahan:**
- Isi seeder diganti dari COA Jasuke ke COA Ayam Ketumbar
- Listener `CreateDefaultUserData` tetap memanggil `DefaultCoaSeeder`
- User baru otomatis mendapat COA Ayam Ketumbar saat registrasi

**COA Ayam Ketumbar Mencakup:**

#### Aset (11xxx)
- Kas & Bank (111-113)
- **Persediaan Bahan Baku Ayam** (1141-1144):
  - Ayam Potong
  - Ayam Kampung
  - Bebek
  - Ayam Lainnya
- **Persediaan Bahan Pendukung** (1150-1157):
  - Air, Minyak Goreng, Tepung Terigu, Tepung Maizena
  - Lada, Bubuk Kaldu, Bubuk Bawang Putih, Kemasan
- **Persediaan Barang Jadi** (1161-1162):
  - Ayam Crispy Macdi
  - Ayam Goreng Bundo
- Persediaan Barang dalam Proses (117)
- Piutang (118)
- Aset Tetap (119-126): Peralatan, Gedung, Kendaraan, Mesin

#### Kewajiban (21x)
- Hutang Usaha, Hutang Gaji, PPN Keluaran

#### Modal (31x)
- Modal Usaha, Prive

#### Pendapatan (41x)
- Penjualan Produk Ayam (410-411)
- Retur Penjualan (42)
- Pendapatan Lain-lain (43)

#### Biaya Produksi (51x-55x)
- **BBB** - Biaya Bahan Baku (510-512)
  - Ayam Potong, Ayam Kampung, Bebek
- **BTKL** - Biaya Tenaga Kerja Langsung (520-522)
  - Perbumbuan, Penggorengan, Pengemasan
- **BOP** - Biaya Overhead Pabrik (530-538)
  - Bahan Tidak Langsung, Air, Minyak, Tepung, Bumbu, Kemasan
- **BOP BTKTL** - Tenaga Kerja Tidak Langsung (540-546)
  - Pemasaran, Kemasan, Satpam, Cleaning Service, Mandor, Keuangan
- **BOP TL** - BOP Tidak Langsung Lainnya (550-559)
  - Listrik, Sewa, Penyusutan, Air, Transport, Diskon

---

### 2. Tabel BTKL Disederhanakan

**File yang Diubah:**
- `database/migrations/2026_05_25_050411_remove_unused_columns_from_btkls_table.php`

**Kolom yang Dihapus:**
```php
// ❌ Dihapus
- tarif_per_jam (atau tarif_btkl)
- satuan
- kapasitas_per_jam
```

**Struktur Tabel BTKL Sekarang:**
```php
Schema::table('btkls', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->string('kode_proses');
    $table->foreignId('jabatan_id')->constrained('jabatans')->onDelete('cascade');
    $table->text('deskripsi_proses')->nullable();
    $table->string('nama_btkl')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

**Alasan Perubahan:**
- Tarif BTKL sekarang diambil dari `jabatans.tarif_produk`
- Pembebanan langsung per produk, bukan per jam
- Lebih sederhana dan sesuai dengan kebutuhan bisnis

---

## 🚀 Langkah untuk Tim

### Setelah `git pull`:

```bash
# 1. Jalankan migrasi baru
php artisan migrate

# 2. (Opsional) Jika ingin reset database
php artisan migrate:fresh --seed
```

### Untuk User yang Sudah Ada:

Jika Anda sudah memiliki user dengan COA Jasuke dan ingin mengganti ke COA Ayam Ketumbar:

```bash
# Hapus COA lama
DELETE FROM coas WHERE user_id = YOUR_USER_ID;

# Jalankan seeder COA Ayam Ketumbar
php artisan db:seed --class=AyamKetumbarCoaSeeder
```

Atau gunakan command:
```bash
php artisan tinker
```

Kemudian:
```php
$userId = 1; // Ganti dengan user ID Anda
DB::table('coas')->where('user_id', $userId)->delete();
$seeder = new \Database\Seeders\AyamKetumbarCoaSeeder();
$seeder->run($userId);
```

---

## 📝 Catatan Penting

### COA
- ✅ User baru otomatis mendapat COA Ayam Ketumbar
- ✅ User lama tetap menggunakan COA mereka (tidak terpengaruh)
- ✅ Jika ingin ganti, hapus COA lama dan jalankan seeder

### BTKL
- ✅ Kolom yang dihapus tidak digunakan lagi di aplikasi
- ✅ Tarif BTKL sekarang dari `jabatans.tarif_produk`
- ✅ Migrasi aman dijalankan (tidak menghapus data penting)

---

## 🔍 Verifikasi

### Cek COA User Baru:
```sql
SELECT kode_akun, nama_akun, tipe_akun 
FROM coas 
WHERE user_id = YOUR_USER_ID 
ORDER BY kode_akun;
```

### Cek Struktur Tabel BTKL:
```sql
DESCRIBE btkls;
```

Pastikan kolom `tarif_per_jam`, `satuan`, dan `kapasitas_per_jam` sudah tidak ada.

---

**Terakhir diupdate:** 25 Mei 2026
**Versi:** 1.0
