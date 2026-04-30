# Fitur Tampilan COA di Detail BOP Proses

## Status: ✅ SELESAI

## Ringkasan
Menambahkan kolom COA Debit dan COA Kredit pada modal detail BOP Proses agar user dapat melihat akun yang akan digunakan dalam jurnal produksi.

---

## Perubahan yang Dilakukan

### 1. Update View Modal Detail BOP
**File**: `resources/views/master-data/bop/show-proses-modal.blade.php`

**Perubahan**:
- Menambahkan kolom "COA Debit" dan "COA Kredit" pada tabel komponen BOP
- Menampilkan kode akun dan nama akun untuk setiap komponen
- Menambahkan info alert yang menjelaskan bahwa COA ini akan digunakan dalam jurnal produksi

**Struktur Tabel**:
```
| No | Komponen | Rp/produk | COA Debit | COA Kredit | Keterangan |
```

**Format Tampilan COA**:
```
1173
Pers. Barang Dalam Proses - BOP
```

### 2. Logika Pengambilan COA
**Proses**:
1. Ambil `coa_debit` dan `coa_kredit` dari JSON `komponen_bop`
2. Query database untuk mendapatkan nama akun berdasarkan kode
3. Tampilkan kode dan nama akun di modal

**Kode PHP**:
```php
$coaDebit = $komponen['coa_debit'] ?? '1173';
$coaKredit = $komponen['coa_kredit'] ?? '210';

$coaDebitObj = \App\Models\Coa::withoutGlobalScopes()
    ->where('user_id', auth()->id())
    ->where('kode_akun', $coaDebit)
    ->first();

$coaKreditObj = \App\Models\Coa::withoutGlobalScopes()
    ->where('user_id', auth()->id())
    ->where('kode_akun', $coaKredit)
    ->first();
```

---

## Hasil Testing

### Test Data BOP Proses

#### BOP Proses: Pengukusan
| No | Komponen | Rp/produk | COA Debit | COA Kredit |
|----|----------|-----------|-----------|------------|
| 1 | Gas / BBM | Rp 67 | 1173 - Pers. Barang Dalam Proses - BOP | 210 - Hutang Usaha |
| 2 | Air & Kebersihan | Rp 28 | 1173 - Pers. Barang Dalam Proses - BOP | 210 - Hutang Usaha |

#### BOP Proses: Pengemasan Dan Pengtopingan
| No | Komponen | Rp/produk | COA Debit | COA Kredit |
|----|----------|-----------|-----------|------------|
| 1 | Listrik | Rp 278 | 1173 - Pers. Barang Dalam Proses - BOP | 210 - Hutang Usaha |
| 2 | Susu | Rp 649 | 1173 - Pers. Barang Dalam Proses - BOP | 1151 - Pers. Bahan Pendukung Susu |
| 3 | Keju | Rp 1.000 | 1173 - Pers. Barang Dalam Proses - BOP | 1152 - Pers. Bahan Pendukung Keju |
| 4 | Cup | Rp 400 | 1173 - Pers. Barang Dalam Proses - BOP | 1153 - Pers. Bahan Pendukung Kemasan |

### Verifikasi
✅ COA Debit dan COA Kredit tersimpan di JSON `komponen_bop`  
✅ COA dapat diambil dari database  
✅ Modal menampilkan COA dengan benar  
✅ Info alert menjelaskan penggunaan COA di jurnal produksi

---

## Integrasi dengan Jurnal Produksi

### Alur Penggunaan COA
1. **Input COA** → User input COA Debit dan COA Kredit di form BOP Proses
2. **Simpan ke JSON** → COA disimpan dalam struktur JSON `komponen_bop`
3. **Tampil di Detail** → COA ditampilkan di modal detail BOP
4. **Digunakan di Produksi** → Saat produksi, sistem membaca COA dari `komponen_bop` dan membuat jurnal

### Kode Integrasi di ProduksiController
```php
// PRIORITAS 1: Ambil COA dari BopProses komponen_bop
if ($bomJobBOP->bopProses && $bomJobBOP->bopProses->komponen_bop) {
    $komponenBop = is_array($bomJobBOP->bopProses->komponen_bop) 
        ? $bomJobBOP->bopProses->komponen_bop 
        : json_decode($bomJobBOP->bopProses->komponen_bop, true);
    
    if (is_array($komponenBop)) {
        foreach ($komponenBop as $komponen) {
            $componentName = $komponen['component'] ?? '';
            if (stripos($componentName, $namaKomponen) !== false) {
                // Gunakan coa_debit dan coa_kredit dari komponen
                $debitKode = $komponen['coa_debit'];
                $kreditKode = $komponen['coa_kredit'];
                break;
            }
        }
    }
}
```

---

## Cara Menggunakan

### 1. Lihat Detail BOP Proses
1. Buka halaman `/master-data/bop`
2. Klik tombol **"Detail"** pada BOP Proses yang ingin dilihat
3. Modal akan menampilkan detail BOP termasuk kolom COA

### 2. Verifikasi COA
- Periksa kolom **COA Debit** untuk setiap komponen
- Periksa kolom **COA Kredit** untuk setiap komponen
- Pastikan COA sesuai dengan yang diinput saat setup BOP

### 3. Konfirmasi Penggunaan di Jurnal
- Baca info alert di bagian bawah tabel
- COA yang ditampilkan adalah COA yang akan digunakan saat produksi
- Setiap komponen akan membuat jurnal dengan COA yang sudah ditentukan

---

## Manfaat Fitur

1. **Transparansi** → User dapat melihat COA yang akan digunakan sebelum produksi
2. **Verifikasi** → User dapat memverifikasi bahwa COA sudah benar
3. **Dokumentasi** → Detail BOP menjadi dokumentasi lengkap termasuk akun jurnal
4. **Konsistensi** → Memastikan COA yang ditampilkan sama dengan yang digunakan di jurnal

---

## File Terkait

### View
- `resources/views/master-data/bop/show-proses-modal.blade.php` - Modal detail BOP dengan COA

### Controller
- `app/Http/Controllers/MasterData/BopController.php` - Method `showProsesModal()`
- `app/Http/Controllers/ProduksiController.php` - Integrasi COA ke jurnal produksi

### Model
- `app/Models/BopProses.php` - Model dengan field `komponen_bop` (JSON)
- `app/Models/Coa.php` - Model Chart of Accounts

### Test
- `test_bop_coa_display.php` - Script testing tampilan COA

---

## Catatan Penting

1. **Default COA**:
   - COA Debit default: `1173` (BDP - BOP)
   - COA Kredit default: `210` (Hutang Usaha)

2. **Fallback**:
   - Jika COA tidak ditemukan di database, akan menampilkan nama default
   - Sistem tetap menggunakan kode COA yang tersimpan di JSON

3. **Multi-tenant**:
   - Query COA menggunakan `withoutGlobalScopes()` dan filter `user_id`
   - Memastikan setiap user hanya melihat COA miliknya

---

## Testing Checklist

- [x] COA Debit ditampilkan dengan benar
- [x] COA Kredit ditampilkan dengan benar
- [x] Kode dan nama akun sesuai dengan database
- [x] Info alert menjelaskan penggunaan COA
- [x] Modal dapat dibuka tanpa error
- [x] Data COA sesuai dengan yang digunakan di jurnal produksi

---

**Dibuat**: 30 April 2026  
**Status**: Selesai dan Siap Digunakan  
**Testing**: Berhasil
