# Cara Menjalankan COA Ayam Seeder

## Masalah yang Terjadi
Setelah menjalankan seeder, hanya 57 dari 87 COA yang masuk ke database karena:
- Model `Coa` memiliki global scope yang filter berdasarkan `user_id`
- Tabel `coas` memiliki unique constraint pada `['kode_akun', 'company_id']`
- Seeder tidak menyertakan `company_id` saat insert COA
- Ada kemungkinan COA lama yang conflict

## Solusi
Seeder sudah diperbaiki untuk:
1. Mengambil `user_id` dan `company_id` dari user yang sedang login
2. Menangani unique constraint `kode_akun` + `company_id`
3. Menyediakan command artisan khusus untuk menjalankan seeder dengan aman

## Cara Menjalankan Seeder

### Opsi 1: Menggunakan Command Artisan (RECOMMENDED)

Command ini akan membantu Anda memilih user dan menangani conflict dengan lebih baik:

```bash
php artisan coa:seed-ayam
```

Command akan menanyakan user mana yang akan digunakan. Pilih user Anda.

### Opsi 2: Dengan Opsi Clean (Hapus COA Lama)

Jika Anda ingin menghapus 57 COA lama dan insert ulang semua 87 COA:

```bash
php artisan coa:seed-ayam --clean
```

**PERINGATAN**: Opsi `--clean` akan menghapus semua COA lama untuk user tersebut!

### Opsi 3: Dengan User ID Spesifik

```bash
php artisan coa:seed-ayam --user-id=1
```

Atau dengan clean:

```bash
php artisan coa:seed-ayam --user-id=1 --clean
```

### Opsi 4: Cara Manual (Tidak Recommended)

Jika Anda tetap ingin menggunakan cara lama:

1. Login ke aplikasi sebagai user yang ingin diberi COA
2. Jalankan:
```bash
php artisan db:seed --class=CoaAyamSeeder
```

## Output yang Diharapkan

```
=== COA AYAM SEEDER ===
Total COA: 87
User: Nama User (ID: 1)
Company ID: 1

✓ Inserted: 11 - Aset
✓ Inserted: 111 - Kas Bank
✓ Updated: 112 - Kas
...

==================================================
🎉 SEEDER BERHASIL!
📊 Ringkasan:
   - COA Baru: 30
   - COA Diupdate: 57
   - Total: 87
==================================================
```

## Verifikasi

Setelah seeder berhasil, cek di aplikasi:
1. Login sebagai user yang Anda gunakan saat menjalankan seeder
2. Buka menu Master Data > Chart of Accounts
3. Pastikan semua 87 COA sudah muncul

## Troubleshooting

### Jika masih ada COA yang tidak masuk:

1. **Cek error message** saat menjalankan seeder
2. **Cek duplicate key error**: Kemungkinan ada COA dengan `kode_akun` yang sama tapi `company_id` berbeda
3. **Cek database langsung**:
   ```sql
   SELECT kode_akun, nama_akun, user_id, company_id 
   FROM coas 
   WHERE user_id = 1 
   ORDER BY kode_akun;
   ```

### Jika ingin melihat COA yang conflict:

```bash
php artisan tinker
```

```php
// Lihat semua COA untuk user tertentu
\App\Models\Coa::withoutGlobalScopes()->where('user_id', 1)->get(['kode_akun', 'nama_akun']);

// Lihat COA yang duplicate
DB::table('coas')
    ->select('kode_akun', DB::raw('COUNT(*) as count'))
    ->groupBy('kode_akun')
    ->having('count', '>', 1)
    ->get();
```

### Jika ingin reset dan jalankan ulang:

**HATI-HATI**: Ini akan menghapus semua COA untuk user tersebut!

```bash
php artisan coa:seed-ayam --user-id=1 --clean
```

Atau manual via tinker:

```bash
php artisan tinker
```

```php
// Hapus COA untuk user tertentu
\App\Models\Coa::withoutGlobalScopes()->where('user_id', 1)->delete();

// Atau hapus berdasarkan company_id
DB::table('coas')->where('company_id', 1)->delete();
```

Lalu jalankan seeder lagi:

```bash
php artisan coa:seed-ayam --user-id=1
```

## Catatan Penting

- Seeder ini aman dijalankan berkali-kali (idempotent)
- Jika COA sudah ada (berdasarkan `kode_akun` + `company_id`), akan di-update
- Jika COA belum ada, akan di-insert
- Seeder menggunakan transaction, jadi jika ada error, semua perubahan akan di-rollback
- Unique constraint: `kode_akun` + `company_id` harus unik
- Sistem ini multi-tenant, jadi setiap COA harus punya `user_id` dan `company_id`

## Daftar 87 COA yang Akan Di-insert

### Aset (27 COA)
- 11, 111, 112, 113
- 114, 1141, 1142, 1143, 1144 (Bahan Baku)
- 115, 1150-1157 (Bahan Pendukung)
- 116, 1161, 1162 (Barang Jadi)
- 117 (Barang dalam Proses)
- 118-127 (Aset Lainnya)

### Kewajiban (4 COA)
- 21, 210, 211, 212

### Modal (3 COA)
- 31, 310, 311

### Pendapatan (5 COA)
- 41, 410, 411, 42, 43

### Biaya (48 COA)
- 51, 510-512 (BBB)
- 52, 520-522 (BTKL)
- 53, 530-538 (BOP)
- 54, 540-546 (BOP BTKTL)
- 55, 550-559 (BOP TL)

**Total: 87 COA**
