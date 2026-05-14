# Perbaikan Jumlah Pegawai di Halaman BTKL

## Masalah
Di halaman `/master-data/btkldata`, kolom "Jumlah Pegawai" menampilkan **0 pegawai** padahal data pegawai sudah ada di database.

## Penyebab
1. **Relasi yang salah** di model `Jabatan`: Relasi `pegawais()` menggunakan `jabatan_id` sebagai foreign key, padahal tabel `pegawais` menggunakan kolom `jabatan` (string nama jabatan).

2. **Where clause yang bermasalah**: Menambahkan `where('pegawais.user_id', $this->user_id)` di dalam relasi menyebabkan konflik saat eager loading, menghasilkan query dengan kondisi `pegawais.user_id is null`.

## Solusi

### 1. Perbaikan Relasi di Model Jabatan (`app/Models/Jabatan.php`)

**Sebelum:**
```php
public function pegawais(): HasMany
{
    return $this->hasMany(Pegawai::class, 'jabatan_id')
        ->where('pegawais.user_id', $this->user_id);
}
```

**Sesudah:**
```php
public function pegawais(): HasMany
{
    // Match by jabatan name (string) instead of jabatan_id
    // Global scope on Pegawai model will automatically filter by user_id
    return $this->hasMany(Pegawai::class, 'jabatan', 'nama');
}
```

**Penjelasan:**
- Menggunakan `'jabatan'` (kolom di tabel pegawais) sebagai foreign key
- Menggunakan `'nama'` (kolom di tabel jabatans) sebagai local key
- Menghapus where clause manual karena global scope di model Pegawai sudah otomatis memfilter berdasarkan `user_id`

### 2. Perbaikan Query di Controller (`app/Http/Controllers/ProsesProduksiController.php`)

**Sebelum:**
```php
$prosesProduksis = ProsesProduksi::with(['jabatan' => function($query) {
        $query->where('user_id', auth()->id())
              ->with(['pegawais' => function($pegawaiQuery) {
                  $pegawaiQuery->where('user_id', auth()->id());
              }]);
    }])
    ->where('user_id', auth()->id())
    ->orderBy('kode_proses')
    ->paginate(10);
```

**Sesudah:**
```php
$prosesProduksis = ProsesProduksi::with(['jabatan.pegawais'])
    ->where('user_id', auth()->id())
    ->orderBy('kode_proses')
    ->paginate(10);
```

**Penjelasan:**
- Menghapus nested where clause yang redundan
- Global scope di model Jabatan dan Pegawai sudah otomatis memfilter berdasarkan `user_id`
- Query menjadi lebih sederhana dan efisien

## Hasil
Setelah perbaikan, halaman BTKL sekarang menampilkan jumlah pegawai dengan benar:
- **Pengukusan**: 1 pegawai @ Rp 20.000/jam
- **Pengemasan**: 1 pegawai @ Rp 17.000/jam

## Catatan Teknis
- Tabel `pegawais` memiliki kolom `jabatan` (string) yang berisi nama jabatan, bukan `jabatan_id` (foreign key)
- Global scope di model Pegawai otomatis memfilter data berdasarkan `user_id` yang sedang login
- Eager loading dengan custom foreign key dan local key bekerja dengan baik tanpa perlu where clause tambahan
