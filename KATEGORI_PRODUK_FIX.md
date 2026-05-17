# Fix: Error Kategori Produk - Field kode_kategori

## Masalah
Saat membuat kategori produk baru, muncul error SQL:
```
SQLSTATE[HY000]: General error: 1364 Field 'kode_kategori' doesn't have a default value
```

## Root Cause
Field `kode_kategori` di tabel `kategori_produks` adalah:
- NOT NULL
- UNIQUE
- Tidak punya default value

Tapi method `store()` di `KategoriProdukController` tidak mengisi field ini saat membuat kategori baru.

## Solusi
Update method `store()` untuk auto-generate `kode_kategori`:

```php
// Generate kode_kategori otomatis dari nama (3 huruf pertama + random)
$kodeBase = strtoupper(substr($request->nama, 0, 3));
$kode = $kodeBase;
$counter = 1;

// Pastikan kode unik
while (KategoriProduk::where('kode_kategori', $kode)->exists()) {
    $kode = $kodeBase . $counter;
    $counter++;
}

$kategori = KategoriProduk::create([
    'user_id'       => auth()->id(),
    'kode_kategori' => $kode,  // ← Tambah ini
    'nama'          => $request->nama,
    'deskripsi'     => $request->deskripsi,
]);
```

## Cara Kerja

1. **Generate Kode**: Ambil 3 huruf pertama dari nama kategori
   - Contoh: "Makanan" → "MAK"

2. **Uppercase**: Konversi ke huruf besar
   - "mak" → "MAK"

3. **Unique Check**: Pastikan kode belum ada di database
   - Jika sudah ada, tambahkan counter: "MAK1", "MAK2", dst

4. **Create**: Simpan kategori dengan kode yang sudah di-generate

## Contoh

| Nama Kategori | Kode Generated |
|---|---|
| Makanan | MAK |
| Makanan Ringan | MAK1 |
| Minuman | MIN |
| Minuman Panas | MIN1 |
| Pakaian | PAK |

## File yang Diubah

- `app/Http/Controllers/KategoriProdukController.php`
  - Method `store()` - Tambah auto-generate kode_kategori

## Testing

Sekarang Anda bisa membuat kategori produk baru tanpa error:
1. Buka Master Data > Produk > Kategori Produk
2. Klik "Tambah Kategori"
3. Isi nama kategori
4. Klik "Simpan"
5. ✅ Kategori berhasil dibuat dengan kode otomatis

## Hasil

✅ Error SQL hilang
✅ Kategori bisa dibuat dengan mudah
✅ Kode kategori auto-generate dan unik
✅ User tidak perlu input kode manual

---

**Status:** ✅ Fixed
**Date:** 2026-05-17
