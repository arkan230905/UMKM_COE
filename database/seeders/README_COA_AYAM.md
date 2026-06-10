# COA Ayam Seeder

Seeder ini khusus untuk bisnis **AYAM** (bukan jagung), berisi struktur Chart of Accounts (COA) lengkap.

## Struktur COA

### 1️⃣ ASET (1xxx)
- **Kas & Bank** (111, 1111, 1112, 112, 113)
- **Persediaan Bahan Baku AYAM** (114, 1141-1144)
  - Ayam Potong
  - Ayam Kampung
  - Bebek
  - Ayam Lainnya
- **Persediaan Bahan Pendukung** (115, 1150-1157)
  - Air, Minyak, Tepung, Bumbu, Kemasan
- **Persediaan Barang Jadi** (116, 1161-1162)
  - Ayam Crispy Macdi
  - Ayam Goreng Bundo
- **Persediaan WIP** (117, 1174-1176)
  - WIP BBB, BTKL, BOP
- **Aset Tetap** (119-126)
  - Peralatan, Gedung, Kendaraan, Mesin + Akumulasi Penyusutan

### 2️⃣ KEWAJIBAN (2xxx)
- Hutang Usaha, Hutang Gaji, PPN Keluaran

### 3️⃣ MODAL (3xxx)
- Modal Usaha, Prive

### 4️⃣ PENDAPATAN (4xxx)
- Penjualan Produk Ayam (Crispy Macdi, Goreng Bundo)
- Retur Penjualan
- Pendapatan Lain-Lain

### 5️⃣ BIAYA PRODUKSI (5xxx)
- **BBB** (51x) - Bahan Baku Ayam
- **BTKL** (52x) - Tenaga Kerja Langsung (Perbumbuan, Penggorengan, Pengemasan)
- **BOP** (53x-55x) - Overhead Pabrik
- **HPP** (56) - Harga Pokok Penjualan

---

## Cara Menggunakan

### **Opsi 1: Jalankan Seeder Langsung**

```bash
# Jalankan seeder
php artisan db:seed --class=CoaAyamSeeder
```

### **Opsi 2: Jalankan via Tinker (Test dulu)**

```bash
php artisan tinker

# Di dalam tinker:
$seeder = new \Database\Seeders\CoaAyamSeeder();
$seeder->run();
exit
```

### **Opsi 3: Tambahkan ke DatabaseSeeder**

Edit file `database/seeders/DatabaseSeeder.php`:

```php
public function run()
{
    $this->call([
        CoaAyamSeeder::class,
        // ... seeder lainnya
    ]);
}
```

Lalu jalankan:
```bash
php artisan db:seed
```

---

## Kustomisasi

### **Untuk User Spesifik (Multi-Tenant)**

Edit file `CoaAyamSeeder.php` baris 149:

```php
// SEBELUM (Template Global):
'user_id' => null,

// SESUDAH (User Spesifik):
'user_id' => 1, // atau auth()->id() jika via web
```

### **Tambah Produk Ayam Baru**

Tambahkan di section Pendapatan (4xxx):

```php
['kode_akun' => '412', 'nama_akun' => 'Penjualan - Ayam Geprek Sambal Matah', 'tipe_akun' => 'Pendapatan', 'saldo_normal' => 'kredit'],
```

Dan Persediaan Barang Jadi (116x):

```php
['kode_akun' => '1163', 'nama_akun' => 'Pers. Barang Jadi Ayam Geprek Sambal Matah', 'tipe_akun' => 'Aset', 'saldo_normal' => 'debit'],
```

---

## Verifikasi Setelah Seeding

```bash
# Via Tinker
php artisan tinker
DB::table('coas')->where('user_id', null)->count(); // Cek jumlah COA template

# Via SQL
SELECT kode_akun, nama_akun FROM coas WHERE user_id IS NULL ORDER BY kode_akun;
```

---

## Catatan Penting

✅ **Aman untuk Multi-Tenant** - Seeder ini menggunakan `user_id = null` sebagai template global
✅ **Tidak ada COA Jagung** - Hanya untuk bisnis AYAM
✅ **Struktur Lengkap** - BBB, BTKL, BOP, WIP sudah include
✅ **Beban Penyusutan** - Sudah pakai kode 5xx (benar)

---

## Troubleshooting

### Error: Duplicate Entry

Jika ada error duplicate key:

```bash
# Hapus dulu data lama
php artisan tinker
DB::table('coas')->where('user_id', null)->delete();
exit

# Jalankan seeder lagi
php artisan db:seed --class=CoaAyamSeeder
```

### Ingin Reset Database

```bash
php artisan migrate:fresh --seed
```

⚠️ **HATI-HATI:** Command ini akan **HAPUS SEMUA DATA**!

---

## Support

Seeder ini sudah disesuaikan dengan struktur database production.
Jika ada pertanyaan, hubungi developer team.
