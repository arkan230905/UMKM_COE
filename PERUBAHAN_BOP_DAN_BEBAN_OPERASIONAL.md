# Perubahan BOP dan Beban Operasional

## Ringkasan Perubahan

### 1. Halaman BOP (`master-data/bop`)
- **Hanya untuk BOP Proses**
- **Dihapus**: Tab Beban Operasional
- **Dihapus**: Input BTKL (Biaya Tenaga Kerja Langsung)
- **Disederhanakan**: Form hanya berisi:
  - Nama BOP Proses
  - Komponen BOP (nama, nilai per produk, keterangan)
  - Total BOP per produk (otomatis dihitung)

### 2. Halaman Beban Operasional Baru (`master-data/beban-operasional`)
- **Route baru**: `/master-data/beban-operasional`
- **Controller baru**: `BebanOperasionalController`
- **Form sederhana**:
  - Nama Beban
  - Nominal Budget
  - Status (otomatis aktif)

## File yang Dibuat

1. **Controller**:
   - `app/Http/Controllers/MasterData/BebanOperasionalController.php`

2. **View**:
   - `resources/views/master-data/beban-operasional/index.blade.php`

3. **Routes** (ditambahkan di `routes/web.php`):
   ```php
   Route::prefix('beban-operasional')->name('beban-operasional.')->group(function () {
       Route::get('/', [BebanOperasionalController::class, 'index'])->name('index');
       Route::post('/', [BebanOperasionalController::class, 'store'])->name('store');
       Route::get('/{id}', [BebanOperasionalController::class, 'show'])->name('show');
       Route::put('/{id}', [BebanOperasionalController::class, 'update'])->name('update');
       Route::delete('/{id}', [BebanOperasionalController::class, 'destroy'])->name('destroy');
   });
   ```

## File yang Dimodifikasi

1. **`resources/views/master-data/bop/index.blade.php`**:
   - Dihapus tab Beban Operasional
   - Disederhanakan tampilan BOP Proses
   - Dihapus bagian BTKL

2. **`routes/web.php`**:
   - Ditambahkan route group untuk Beban Operasional

## Cara Mengakses

1. **BOP Proses**: `http://127.0.0.1:8000/master-data/bop`
2. **Beban Operasional**: `http://127.0.0.1:8000/master-data/beban-operasional`

## Fitur Beban Operasional

- ✅ Tambah beban operasional (nama + budget)
- ✅ Edit beban operasional
- ✅ Hapus beban operasional (dengan validasi penggunaan)
- ✅ Filter berdasarkan nama dan status
- ✅ Kode otomatis (BO001, BO002, dst)
- ✅ Status otomatis aktif

## Langkah Selanjutnya

Untuk menyelesaikan penyederhanaan BOP Proses:
1. Hapus field BTKL dari form tambah/edit BOP Proses
2. Hapus kolom "Biaya/Produk" dari tabel
3. Ubah kolom "Nama Proses" menjadi "Nama BOP Proses" dengan input text bebas
4. Hapus ketergantungan dengan tabel `proses_produksis`
