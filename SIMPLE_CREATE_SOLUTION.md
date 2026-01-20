# Solusi Paling Sederhana - Copy dari EDIT

## Masalah
CREATE tidak berfungsi, terlalu banyak perubahan dari EDIT

## Solusi
Copy EXACT dari EDIT.blade.php yang sudah jalan, ubah MINIMAL:

### Yang Diubah (HANYA 5 hal):
1. Header: "Edit" → "Tambah"  
2. Form action: `update` → `store`
3. Form method: `PUT` → `POST`
4. Warna: `bg-primary` → `bg-success`
5. Button: "Simpan Perubahan" → "Simpan Biaya Bahan"

### Yang TIDAK Diubah:
- ✅ JavaScript SAMA PERSIS
- ✅ Template row SAMA PERSIS
- ✅ Event listener SAMA PERSIS
- ✅ Struktur HTML SAMA PERSIS

## Kenapa Ini Pasti Jalan?
Karena EDIT sudah jalan, dan CREATE cuma beda:
- Data awal kosong (pakai template row)
- Route berbeda (store vs update)

JavaScript-nya 100% SAMA!

## File Baru
Saya akan buat `create.blade.php` yang 99% copy dari `edit.blade.php`

Bedanya HANYA:
```php
// EDIT
<h2>Edit Perhitungan Biaya Bahan</h2>
<form action="{{ route('...update', $produk->id) }}" method="POST">
    @method('PUT')
    
    @foreach($bomDetails as $detail)
        <tr>...</tr>  // Ada data
    @endforeach

// CREATE  
<h2>Tambah Biaya Bahan</h2>
<form action="{{ route('...store', $produk->id) }}" method="POST">
    {{-- No @method('PUT') --}}
    
    {{-- No foreach, langsung template row --}}
```

JavaScript? SAMA PERSIS!

## Test
1. Hard refresh
2. Klik "Tambah Bahan Baku"
3. PASTI JALAN karena JavaScript sama dengan EDIT yang sudah jalan

Simpel kan? 😊
