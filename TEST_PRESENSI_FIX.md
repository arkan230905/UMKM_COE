# Perbaikan Error Presensi

## Masalah
Error: `SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'jam_masuk' cannot be null`

Terjadi saat membuat presensi dengan status "Izin", "Sakit", atau "Absen" karena kolom `jam_masuk` di database tidak mengizinkan nilai NULL.

## Penyebab
Struktur tabel `presensis` memiliki kolom `jam_masuk` dengan constraint NOT NULL, padahal untuk status selain "Hadir", jam masuk dan keluar tidak diperlukan.

## Solusi yang Diterapkan

### 1. Migration untuk Mengubah Kolom
**File**: `database/migrations/2025_11_10_133636_make_jam_masuk_nullable_in_presensis_table.php`

Mengubah kolom `jam_masuk` menjadi nullable:
```php
$table->time('jam_masuk')->nullable()->change();
```

### 2. Logika di Controller
**File**: `app/Http/Controllers/PresensiController.php`

Sudah ada logika yang benar di method `store()`:
```php
if (($validated['status'] ?? '') === 'Hadir' && !empty($validated['jam_masuk']) && !empty($validated['jam_keluar'])) {
    $jamMasuk = Carbon::parse($validated['jam_masuk']);
    $jamKeluar = Carbon::parse($validated['jam_keluar']);
    $validated['jumlah_jam'] = $jamKeluar->diffInHours($jamMasuk, true);
} else {
    $validated['jam_masuk'] = null;
    $validated['jam_keluar'] = null;
    $validated['jumlah_jam'] = 0;
}
```

Logika ini memastikan:
- Jika status = "Hadir" → jam_masuk dan jam_keluar harus diisi, jumlah_jam dihitung
- Jika status = "Izin", "Sakit", atau "Absen" → jam_masuk dan jam_keluar di-set NULL, jumlah_jam = 0

### 3. Validasi Form
Validasi sudah benar dengan `required_if:status,Hadir`:
```php
'jam_masuk' => 'required_if:status,Hadir|nullable|date_format:H:i',
'jam_keluar' => 'required_if:status,Hadir|nullable|date_format:H:i|after:jam_masuk',
```

## Hasil Setelah Perbaikan

Sekarang presensi dapat dibuat dengan berbagai status:
- **Hadir**: Wajib isi jam masuk dan jam keluar
- **Izin**: Jam masuk dan keluar NULL, hanya isi keterangan
- **Sakit**: Jam masuk dan keluar NULL, hanya isi keterangan
- **Absen**: Jam masuk dan keluar NULL, hanya isi keterangan

## Perbaikan UI Form (Update Terbaru)

### View Create & Edit Presensi
**File**: 
- `resources/views/master-data/presensi/create.blade.php`
- `resources/views/master-data/presensi/edit.blade.php`

### Perubahan yang Dilakukan:

1. **Menambahkan ID unik untuk field jam**:
   - `jamMasukField` untuk div jam masuk
   - `jamKeluarField` untuk div jam keluar

2. **Menambahkan inline style untuk default display**:
   ```php
   style="display: {{ old('status', 'Hadir') == 'Hadir' ? 'block' : 'none' }};"
   ```

3. **Menambahkan conditional required attribute**:
   ```php
   {{ old('status', 'Hadir') == 'Hadir' ? 'required' : '' }}
   ```

4. **Memperbaiki JavaScript dengan selector yang lebih spesifik**:

```javascript
// Menggunakan ID spesifik, bukan querySelectorAll
const jamMasukField = document.getElementById('jamMasukField');
const jamKeluarField = document.getElementById('jamKeluarField');

function toggleJamFields() {
    console.log('Toggle jam fields, status:', statusSelect.value);
    
    if (statusSelect.value !== 'Hadir') {
        // Sembunyikan field jam
        if (jamMasukField) jamMasukField.style.display = 'none';
        if (jamKeluarField) jamKeluarField.style.display = 'none';
        
        // Hapus required dan kosongkan nilai
        if (jamMasuk) {
            jamMasuk.removeAttribute('required');
            jamMasuk.value = '';
        }
        if (jamKeluar) {
            jamKeluar.removeAttribute('required');
            jamKeluar.value = '';
        }
    } else {
        // Tampilkan field jam
        if (jamMasukField) jamMasukField.style.display = 'block';
        if (jamKeluarField) jamKeluarField.style.display = 'block';
        
        // Tambah required dan set default value
        if (jamMasuk) {
            jamMasuk.setAttribute('required', 'required');
            if (!jamMasuk.value) jamMasuk.value = '08:00';
        }
        if (jamKeluar) {
            jamKeluar.setAttribute('required', 'required');
            if (!jamKeluar.value) jamKeluar.value = '17:00';
        }
    }
}
```

### Perilaku Form

**Status "Hadir":**
- Field jam masuk dan jam keluar ditampilkan
- Kedua field wajib diisi (required)
- Default jam masuk: 08:00
- Default jam keluar: 17:00
- Jam keluar otomatis +9 jam dari jam masuk

**Status "Izin", "Sakit", atau "Alpa":**
- Field jam masuk dan jam keluar disembunyikan
- Tidak ada validasi required untuk jam
- Nilai jam dikosongkan
- Hanya perlu isi keterangan (opsional)

## Testing

### Test Status Hadir
1. Buka halaman tambah presensi
2. Pilih pegawai
3. Pilih tanggal
4. Pilih status "Hadir"
5. Field jam masuk dan jam keluar muncul
6. Isi jam masuk dan jam keluar
7. Klik simpan
8. Data presensi berhasil disimpan dengan jam kerja

### Test Status Izin/Sakit/Alpa
1. Buka halaman tambah presensi
2. Pilih pegawai
3. Pilih tanggal
4. Pilih status "Izin" (atau Sakit/Alpa)
5. Field jam masuk dan jam keluar hilang
6. Isi keterangan (opsional)
7. Klik simpan
8. Data presensi berhasil disimpan tanpa jam masuk/keluar

### Test Perubahan Status
1. Pilih status "Hadir" → Field jam muncul
2. Ubah ke status "Izin" → Field jam hilang
3. Ubah kembali ke "Hadir" → Field jam muncul lagi dengan default value

## Catatan
- Kolom `jam_masuk` sudah diubah menjadi nullable di database
- Kolom `jam_keluar` sudah nullable dari awal
- Kolom `jumlah_jam` memiliki default value 0
- Status yang tersedia: Hadir, Izin, Sakit, Alpa
- Field jam otomatis dikosongkan saat status bukan "Hadir"
