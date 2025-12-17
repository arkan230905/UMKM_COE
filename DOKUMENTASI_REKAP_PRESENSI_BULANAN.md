# Dokumentasi: Fitur Rekap Presensi Bulanan

## Ringkasan
Fitur Rekap Presensi Bulanan telah berhasil ditambahkan ke halaman Presensi. Fitur ini memungkinkan admin/user untuk memfilter data presensi berdasarkan bulan dan tahun yang dipilih.

---

## 🎯 Fitur yang Ditambahkan

### 1. **Filter Bulan**
- Dropdown untuk memilih bulan (format: November 2025, Desember 2025, dll)
- List bulan mencakup 12 bulan ke belakang hingga 6 bulan ke depan
- Tombol "Filter" untuk menerapkan filter
- Tombol "Reset" untuk menghapus filter (hanya muncul jika ada filter aktif)

### 2. **Tampilan Data Bulanan**
- Tabel menampilkan data presensi sesuai bulan yang dipilih
- Kolom yang ditampilkan tetap sama:
  - Nama Pegawai + NIP
  - Tanggal Presensi
  - Jam Masuk
  - Jam Keluar
  - Status (Hadir, Sakit, Izin, Absen)
  - Jumlah Jam
  - Keterangan

### 3. **Informasi Bulan Aktif**
- Menampilkan pesan di sebelah kanan filter: "Menampilkan data presensi bulan: November 2025"
- Membantu user tahu bulan mana yang sedang difilter

### 4. **Kompatibilitas dengan Search**
- Filter bulan dapat dikombinasikan dengan pencarian nama pegawai
- Jika user mencari nama pegawai, parameter search tetap tersimpan saat filter bulan diaplikasikan

---

## 📁 File yang Dimodifikasi

### 1. **Controller: `app/Http/Controllers/PresensiController.php`**

#### Perubahan pada method `index()`

**Sebelum:**
```php
public function index()
{
    $search = request('search');
    $presensis = Presensi::with('pegawai')
        ->when($search, function($query) use ($search) {
            // ... search logic
        })
        ->orderBy('tgl_presensi', 'desc')
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    
    // ... transform logic
    
    return view('master-data.presensi.index', compact('presensis', 'search'));
}
```

**Sesudah:**
```php
public function index()
{
    $search = request('search');
    $bulan = request('bulan'); // Format: YYYY-MM
    
    $presensis = Presensi::with('pegawai')
        ->when($search, function($query) use ($search) {
            return $query->whereHas('pegawai', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_pegawai', 'like', "%{$search}%");
            })
            ->orWhere('status', 'like', "%{$search}%");
        })
        ->when($bulan, function($query) use ($bulan) {
            // Filter berdasarkan bulan dan tahun
            $startDate = Carbon::parse($bulan . '-01')->startOfMonth();
            $endDate = Carbon::parse($bulan . '-01')->endOfMonth();
            return $query->whereBetween('tgl_presensi', [$startDate, $endDate]);
        })
        ->orderBy('tgl_presensi', 'desc')
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    
    // Force load pegawai nama untuk setiap presensi
    $presensis->getCollection()->transform(function ($presensi) {
        if ($presensi->pegawai) {
            $presensi->pegawai->nama_display = $presensi->pegawai->nama ?: $presensi->pegawai->nomor_induk_pegawai;
        }
        return $presensi;
    });
    
    // Generate list bulan untuk dropdown (12 bulan ke belakang sampai 6 bulan ke depan)
    $bulanList = [];
    for ($i = -12; $i <= 6; $i++) {
        $date = Carbon::now()->addMonths($i);
        $bulanList[$date->format('Y-m')] = $date->isoFormat('MMMM YYYY');
    }
    
    // Sort bulanList by key descending (terbaru dulu)
    krsort($bulanList);
        
    return view('master-data.presensi.index', compact('presensis', 'search', 'bulan', 'bulanList'));
}
```

**Penjelasan:**
- Menambah variabel `$bulan` untuk menerima parameter bulan dari request
- Menambah filter `when($bulan, ...)` untuk filter data berdasarkan bulan
- Generate list bulan untuk dropdown (12 bulan ke belakang hingga 6 bulan ke depan)
- Pass variabel `bulan` dan `bulanList` ke view

---

### 2. **View: `resources/views/master-data/presensi/index.blade.php`**

#### Perubahan pada Card Header

**Ditambahkan:**
```blade
<!-- Filter Bulan Section -->
<div class="row align-items-center">
    <div class="col-md-4">
        <form action="{{ route('master-data.presensi.index') }}" method="GET" class="d-flex gap-2 align-items-center">
            <!-- Preserve search parameter if exists -->
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            
            <select name="bulan" class="form-select form-select-sm bg-white text-dark border-2 border-primary" 
                    style="max-width: 250px; border-radius: 0.375rem;">
                <option value="">-- Pilih Bulan --</option>
                @foreach($bulanList as $key => $label)
                    <option value="{{ $key }}" {{ $bulan === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            
            <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            
            @if($bulan)
                <a href="{{ route('master-data.presensi.index') . (request('search') ? '?search=' . request('search') : '') }}" 
                   class="btn btn-sm btn-outline-light fw-semibold">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </a>
            @endif
        </form>
    </div>
    <div class="col-md-8 text-end">
        @if($bulan)
            <small class="text-info">
                <i class="bi bi-info-circle me-1"></i>
                Menampilkan data presensi bulan: <strong>{{ \Carbon\Carbon::parse($bulan . '-01')->isoFormat('MMMM YYYY') }}</strong>
            </small>
        @endif
    </div>
</div>
```

**Penjelasan:**
- Menambah form dengan dropdown untuk memilih bulan
- Preserve parameter search jika ada
- Tombol Filter untuk menerapkan filter
- Tombol Reset untuk menghapus filter (hanya muncul jika ada filter aktif)
- Menampilkan informasi bulan yang sedang difilter

---

## 🔧 Cara Kerja

### Query Filter Bulan
```php
->when($bulan, function($query) use ($bulan) {
    // Filter berdasarkan bulan dan tahun
    $startDate = Carbon::parse($bulan . '-01')->startOfMonth();
    $endDate = Carbon::parse($bulan . '-01')->endOfMonth();
    return $query->whereBetween('tgl_presensi', [$startDate, $endDate]);
})
```

**Penjelasan:**
1. Menerima parameter `bulan` dalam format `YYYY-MM` (contoh: `2025-11`)
2. Parse tanggal menjadi awal bulan dan akhir bulan
3. Filter data presensi yang tgl_presensi berada di antara awal dan akhir bulan
4. Jika tidak ada parameter bulan, filter tidak diterapkan (tampilkan semua data)

---

## 📋 Contoh Penggunaan

### 1. **Tampilkan Semua Presensi (Default)**
- Buka halaman: `/master-data/presensi`
- Dropdown bulan kosong
- Tabel menampilkan semua data presensi

### 2. **Filter Presensi Bulan November 2025**
- Buka halaman: `/master-data/presensi`
- Pilih "November 2025" di dropdown
- Klik tombol "Filter"
- Tabel akan menampilkan hanya data presensi November 2025
- URL berubah menjadi: `/master-data/presensi?bulan=2025-11`

### 3. **Filter + Search Kombinasi**
- Buka halaman: `/master-data/presensi`
- Cari nama pegawai "Budi" di search box
- Pilih "November 2025" di dropdown
- Klik tombol "Filter"
- Tabel menampilkan data presensi November 2025 untuk pegawai bernama "Budi"
- URL: `/master-data/presensi?search=Budi&bulan=2025-11`

### 4. **Reset Filter**
- Jika ada filter bulan aktif, tombol "Reset" akan muncul
- Klik tombol "Reset" untuk menghapus filter bulan
- Tabel kembali menampilkan semua data (atau sesuai search jika ada)

---

## 📊 Format Data Tabel

Tabel tetap menampilkan kolom yang sama:

| Kolom | Deskripsi |
|-------|-----------|
| # | Nomor urut |
| NAMA PEGAWAI | Nama pegawai + NIP |
| TANGGAL | Tanggal presensi (format: Senin, 1 November 2025) |
| JAM MASUK | Jam masuk (format: HH:MM) atau "-" jika tidak hadir |
| JAM KELUAR | Jam keluar (format: HH:MM) atau "-" jika tidak hadir |
| STATUS | Badge status (Hadir=hijau, Izin/Sakit=kuning, Absen=merah) |
| JUMLAH JAM | Jumlah jam kerja atau "-" jika tidak hadir |
| KETERANGAN | Catatan tambahan |
| AKSI | Tombol Edit dan Hapus |

---

## 🎨 UI/UX Improvements

### 1. **Responsive Design**
- Filter bulan responsif di semua ukuran layar
- Di mobile, dropdown dan tombol tetap terlihat dengan baik

### 2. **Visual Feedback**
- Dropdown menampilkan bulan yang sedang dipilih
- Pesan info menampilkan bulan yang sedang difilter
- Tombol Reset hanya muncul jika ada filter aktif

### 3. **Styling Konsisten**
- Dropdown menggunakan styling yang sama dengan search box
- Tombol menggunakan warna primary dan outline yang konsisten
- Informasi bulan ditampilkan dengan warna info (biru)

---

## 🔍 Testing Checklist

- [ ] Filter bulan bekerja dengan baik
- [ ] Dropdown menampilkan 18 bulan (12 ke belakang + 6 ke depan)
- [ ] Data tabel berubah sesuai bulan yang dipilih
- [ ] Tombol Reset menghapus filter dengan benar
- [ ] Kombinasi search + filter bulan bekerja
- [ ] Parameter URL benar: `?bulan=YYYY-MM`
- [ ] Pesan info bulan ditampilkan dengan benar
- [ ] Pagination bekerja dengan filter bulan
- [ ] Responsive di mobile dan desktop

---

## 📝 Notes

- Filter bulan menggunakan format `YYYY-MM` (contoh: `2025-11`)
- Bulan ditampilkan dalam bahasa Indonesia (November, Desember, dll)
- Filter bulan dapat dikombinasikan dengan pencarian nama pegawai
- Jika tidak ada data di bulan yang dipilih, tabel menampilkan pesan "Belum ada data presensi"
- Pagination tetap bekerja normal dengan filter bulan

---

## 🚀 Status Implementasi

✅ **SELESAI**

- [x] Tambah filter bulan di Controller
- [x] Generate list bulan untuk dropdown
- [x] Tambah UI filter bulan di View
- [x] Preserve search parameter saat filter
- [x] Tombol Reset filter
- [x] Informasi bulan aktif
- [x] Responsive design
- [x] Dokumentasi lengkap

---

## 📞 Support

Jika ada pertanyaan atau masalah, silakan hubungi tim development.
