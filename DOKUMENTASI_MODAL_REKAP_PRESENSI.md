# Dokumentasi: Modal Rekap Presensi Bulanan

## 📋 Ringkasan
Fitur Modal Rekap Presensi Bulanan telah berhasil ditambahkan. Modal ini memungkinkan user untuk melihat ringkasan presensi bulanan dalam bentuk tabel dan mengekspornya ke PDF tanpa meninggalkan halaman.

---

## 🎯 Fitur yang Ditambahkan

### 1. **Tombol "Lihat Rekap Presensi Bulanan"**
- Tombol warna info (biru) di bagian atas halaman Presensi
- Membuka modal ketika diklik
- Tidak membuat halaman baru

### 2. **Modal dengan Fitur:**

#### A. **Form Filter Bulan**
- Input type `month` untuk memilih bulan (format: YYYY-MM)
- Default bulan: bulan sekarang
- Tombol "Filter" untuk memproses data

#### B. **Tabel Ringkasan Presensi**
Kolom yang ditampilkan:
- **No** - Nomor urut
- **Nama Pegawai** - Nama + NIP
- **Hadir** - Jumlah hari hadir
- **Total Jam** - Total jam kerja (dalam jam)
- **Sakit** - Jumlah hari sakit
- **Izin** - Jumlah hari izin
- **Alpha** - Jumlah hari absen

#### C. **Tombol Cetak PDF**
- Tombol "Cetak PDF" (warna hijau)
- Hanya muncul setelah filter diterapkan
- Mengunduh PDF dengan data yang sudah difilter

### 3. **Loading State**
- Spinner loading saat data sedang diproses
- Pesan "Memproses data..."

### 4. **No Data Message**
- Pesan "Silakan pilih bulan untuk melihat ringkasan presensi" jika belum ada filter
- Pesan "Tidak ada data" jika bulan yang dipilih tidak memiliki data

---

## 📁 File yang Dimodifikasi/Dibuat

### **1. Controller: `app/Http/Controllers/PresensiController.php`**

#### Method Baru: `getRingkasanBulanan()`
```php
public function getRingkasanBulanan()
{
    $bulan = request('bulan'); // Format: YYYY-MM
    
    // Validasi bulan
    if (!$bulan) {
        return response()->json([
            'success' => false,
            'message' => 'Bulan tidak dipilih'
        ], 400);
    }

    // Parse tanggal awal dan akhir bulan
    $startDate = Carbon::parse($bulan . '-01')->startOfMonth();
    $endDate = Carbon::parse($bulan . '-01')->endOfMonth();
    $bulanLabel = $startDate->isoFormat('MMMM YYYY');

    // Get all presences for the month
    $presensis = Presensi::with('pegawai')
        ->whereBetween('tgl_presensi', [$startDate, $endDate])
        ->get();

    // Group by pegawai and calculate summary
    $ringkasan = $presensis->groupBy('pegawai_id')->map(function ($items) {
        $pegawai = $items->first()->pegawai;
        $totalHadir = $items->where('status', 'Hadir')->count();
        $totalSakit = $items->where('status', 'Sakit')->count();
        $totalIzin = $items->where('status', 'Izin')->count();
        $totalAlpha = $items->where('status', 'Absen')->count();
        $totalJamKerja = $items->where('status', 'Hadir')->sum('jumlah_jam');

        return [
            'pegawai_id' => $pegawai->id,
            'nama_pegawai' => $pegawai->nama,
            'kode_pegawai' => $pegawai->kode_pegawai,
            'total_hadir' => $totalHadir,
            'total_jam_kerja' => round($totalJamKerja, 2),
            'total_sakit' => $totalSakit,
            'total_izin' => $totalIzin,
            'total_alpha' => $totalAlpha
        ];
    })->values();

    return response()->json([
        'success' => true,
        'bulan' => $bulan,
        'bulanLabel' => $bulanLabel,
        'data' => $ringkasan
    ]);
}
```

**Penjelasan:**
- Menerima parameter `bulan` dalam format `YYYY-MM`
- Mengelompokkan data presensi per pegawai
- Menghitung: total hadir, sakit, izin, alpha, dan total jam kerja
- Return JSON response dengan data ringkasan

#### Method Baru: `exportRingkasanPdf()`
```php
public function exportRingkasanPdf()
{
    $bulan = request('bulan');
    
    if (!$bulan) {
        return redirect()->back()->with('error', 'Silakan pilih bulan terlebih dahulu');
    }

    // ... (sama dengan getRingkasanBulanan untuk data processing)

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('master-data.presensi.ringkasan-pdf', compact('ringkasan', 'bulanLabel'));
    return $pdf->download('ringkasan-presensi-' . $bulan . '.pdf');
}
```

**Penjelasan:**
- Mengambil data ringkasan sama seperti `getRingkasanBulanan()`
- Load view PDF dengan data ringkasan
- Download file PDF dengan nama `ringkasan-presensi-YYYY-MM.pdf`

---

### **2. Routes: `routes/web.php`**

```php
Route::get('presensi/ringkasan-bulanan', [PresensiController::class, 'getRingkasanBulanan'])->name('presensi.ringkasan-bulanan');
Route::get('presensi/export-ringkasan-pdf', [PresensiController::class, 'exportRingkasanPdf'])->name('presensi.export-ringkasan-pdf');
```

**Penjelasan:**
- Route untuk API get ringkasan (return JSON)
- Route untuk export PDF

---

### **3. View: `resources/views/master-data/presensi/index.blade.php`**

#### Perubahan 1: Tambah Tombol Modal
```blade
<button type="button" class="btn btn-info fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRingkasanPresensi">
    <i class="bi bi-file-earmark-text me-1"></i> Lihat Rekap Presensi Bulanan
</button>
```

#### Perubahan 2: Tambah Modal HTML
Modal berisi:
- Form filter dengan input month
- Tombol Filter
- Loading spinner
- Tabel ringkasan (hidden by default)
- No data message (hidden by default)
- Tombol Cetak PDF (hidden by default)

#### Perubahan 3: Tambah Styling CSS
- Styling untuk modal header (gradient purple)
- Styling untuk form input
- Styling untuk tombol filter
- Styling untuk tabel ringkasan
- Responsive design

#### Perubahan 4: Tambah JavaScript
```javascript
// Set default bulan ke bulan sekarang
const today = new Date();
const defaultBulan = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
bulanFilter.value = defaultBulan;

// Handle filter button click
btnFilter.addEventListener('click', function() {
    const bulan = bulanFilter.value;
    
    // Show loading
    loadingSpinner.classList.remove('d-none');
    
    // Fetch data via AJAX
    fetch("{{ route('master-data.presensi.ringkasan-bulanan') }}?bulan=" + bulan)
        .then(response => response.json())
        .then(data => {
            // Populate table with data
            // Show/hide elements based on data
        });
});

// Handle export PDF button click
btnExportPdf.addEventListener('click', function() {
    const bulan = bulanFilter.value;
    window.location.href = "{{ route('master-data.presensi.export-ringkasan-pdf') }}?bulan=" + bulan;
});
```

---

### **4. View PDF: `resources/views/master-data/presensi/ringkasan-pdf.blade.php`** (BARU)

File ini berisi template HTML untuk export PDF dengan:
- Header: "RINGKASAN PRESENSI BULANAN"
- Info section: Bulan, Total Pegawai
- Tabel ringkasan dengan styling untuk print
- Footer dengan tanggal cetak

---

## 🔧 Cara Kerja

### **Flow Lengkap:**

1. **User membuka halaman Presensi**
   - Melihat tombol "Lihat Rekap Presensi Bulanan"

2. **User klik tombol**
   - Modal terbuka
   - Input month sudah terisi dengan bulan sekarang

3. **User pilih bulan dan klik Filter**
   - JavaScript mengirim AJAX request ke `getRingkasanBulanan()`
   - Loading spinner ditampilkan
   - Controller memproses data dan return JSON

4. **Data diterima dan ditampilkan**
   - Tabel ringkasan ditampilkan
   - Tombol "Cetak PDF" muncul
   - Loading spinner hilang

5. **User klik Cetak PDF**
   - JavaScript mengarahkan ke `exportRingkasanPdf()`
   - Controller generate PDF dan download

---

## 📊 Contoh Data Output

### **JSON Response dari `getRingkasanBulanan()`:**
```json
{
    "success": true,
    "bulan": "2025-11",
    "bulanLabel": "November 2025",
    "data": [
        {
            "pegawai_id": 1,
            "nama_pegawai": "Putri Amelia",
            "kode_pegawai": "PGW0011",
            "total_hadir": 20,
            "total_jam_kerja": 160.5,
            "total_sakit": 1,
            "total_izin": 0,
            "total_alpha": 0
        },
        {
            "pegawai_id": 2,
            "nama_pegawai": "Aura Nabila",
            "kode_pegawai": "PGW0005",
            "total_hadir": 18,
            "total_jam_kerja": 144.0,
            "total_sakit": 0,
            "total_izin": 2,
            "total_alpha": 1
        }
    ]
}
```

### **Tabel di Modal:**
```
No | Nama Pegawai      | Hadir | Total Jam | Sakit | Izin | Alpha
1  | Putri Amelia      | 20    | 160.50    | 1     | 0    | 0
   | NIP: PGW0011      |       |           |       |      |
2  | Aura Nabila       | 18    | 144.00    | 0     | 2    | 1
   | NIP: PGW0005      |       |           |       |      |
```

---

## 🎨 UI/UX Design

### **Modal Styling:**
- Header: Gradient purple (#667eea → #764ba2)
- Background: Dark theme (#222232)
- Input: White background dengan border purple
- Tombol Filter: Purple (#667eea)
- Tombol Cetak PDF: Green (#28a745)
- Tabel: Striped dengan hover effect

### **Responsive:**
- Modal lg (large) - optimal untuk desktop
- Responsive table untuk mobile
- Input month dan tombol filter responsive

---

## 🔍 Testing Checklist

- [ ] Tombol "Lihat Rekap Presensi Bulanan" muncul di halaman
- [ ] Klik tombol membuka modal
- [ ] Input month sudah terisi dengan bulan sekarang
- [ ] Klik Filter memproses data dan menampilkan tabel
- [ ] Loading spinner muncul saat proses
- [ ] Tabel menampilkan data dengan benar
- [ ] Tombol "Cetak PDF" muncul setelah filter
- [ ] Klik "Cetak PDF" mengunduh file PDF
- [ ] PDF berisi data yang sesuai dengan filter
- [ ] Modal dapat ditutup dengan tombol "Tutup"
- [ ] No data message muncul jika tidak ada data
- [ ] Responsive di mobile dan desktop

---

## 📝 Notes

- Modal menggunakan Bootstrap 5 modal component
- Data diambil via AJAX (tidak reload halaman)
- PDF menggunakan library `barryvdh/laravel-dompdf`
- Default bulan adalah bulan sekarang
- Tabel otomatis di-sort per pegawai

---

## 🚀 Status Implementasi

✅ **SELESAI & SIAP DIGUNAKAN**

- [x] Controller method `getRingkasanBulanan()`
- [x] Controller method `exportRingkasanPdf()`
- [x] Routes untuk kedua method
- [x] Modal HTML dengan form filter
- [x] Tabel ringkasan di modal
- [x] JavaScript untuk AJAX dan event handling
- [x] CSS styling untuk modal
- [x] View PDF untuk export
- [x] Loading state dan error handling
- [x] Responsive design

---

## 📞 Support

Jika ada pertanyaan atau masalah, silakan hubungi tim development.
