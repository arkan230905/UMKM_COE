# ✅ Perbaikan Tampilan BOM Index

## 🎯 Masalah

Tampilan BOM menampilkan **detail per bahan** (setiap bahan = 1 row) padahal seharusnya **per produk** (1 produk = 1 row dengan ringkasan).

### ❌ Sebelum (Salah)
```
Ayam Sambal Hijau | Ayam Kampung  | 300g  | Rp 45.000 | Rp 13.500.000 | Detail Edit Hapus
Ayam Sambal Hijau | Cabe Hijau    | 10g   | Rp 30.000 | Rp 300.000    | Detail Edit Hapus
Ayam Sambal Hijau | Kemasan        | 1 pcs | Rp 2.000  | Rp 2.000      | Detail Edit Hapus
Ayam Pop          | Kemasan        | 1 pcs | Rp 2.000  | Rp 2.000      | Detail Edit Hapus
Ayam Pop          | Tepung Terigu  | 10g   | Rp 0      | Rp 0          | Detail Edit Hapus
...
```
**Masalah:**
- 1 produk muncul berkali-kali (sesuai jumlah bahan)
- Sulit melihat ringkasan per produk
- Tombol aksi duplikat untuk produk yang sama
- Tidak efisien

## ✅ Solusi

### Sesudah (Benar)
```
┌───┬──────────────────┬──────────────┬─────────────┬────────────────┬──────────────────┐
│ # │ Produk           │ Jumlah Bahan │ Total Biaya │ Status         │ Aksi             │
├───┼──────────────────┼──────────────┼─────────────┼────────────────┼──────────────────┤
│ 1 │ 🖼️ Ayam Sambal   │ 3 item       │ Rp 13.802K  │ ✅ Sudah Ada   │ Detail Edit Hapus│
│   │ Hijau            │ BBB:3 | BP:0 │             │ BOM            │                  │
├───┼──────────────────┼──────────────┼─────────────┼────────────────┼──────────────────┤
│ 2 │ 🖼️ Ayam Pop      │ 3 item       │ Rp 13.692K  │ ✅ Sudah Ada   │ Detail Edit Hapus│
│   │                  │ BBB:3 | BP:0 │             │ BOM            │                  │
├───┼──────────────────┼──────────────┼─────────────┼────────────────┼──────────────────┤
│ 3 │ 🖼️ Roti Tawar    │ 0 item       │ Rp 0        │ ⚠️ Belum ada   │ ⚠️ Isi Biaya     │
│   │                  │ BBB:0 | BP:0 │             │ biaya bahan    │ Bahan Dulu       │
└───┴──────────────────┴──────────────┴─────────────┴────────────────┴──────────────────┘
```

**Keuntungan:**
- 1 produk = 1 row (ringkasan)
- Mudah melihat status per produk
- Tombol aksi tidak duplikat
- Efisien dan jelas

## 🔧 Perubahan

### 1. View (resources/views/master-data/bom/index.blade.php)

#### Struktur Tabel Baru
```html
<thead>
    <tr>
        <th>#</th>
        <th>Produk</th>
        <th>Jumlah Bahan</th>      <!-- Ringkasan: BBB + BP -->
        <th>Total Biaya BOM</th>    <!-- Total biaya produksi -->
        <th>Status</th>              <!-- Sudah ada BOM / Belum -->
        <th>Aksi</th>                <!-- Detail/Edit/Hapus atau Tambah BOM -->
    </tr>
</thead>
```

#### Logic Per Produk
```php
@foreach($produks as $produk)
    @php
        // Cek BOM
        $bom = $produk->boms->first();
        $bomJobCosting = BomJobCosting::where('produk_id', $produk->id)->first();
        
        // Hitung jumlah bahan
        $jumlahBahanBaku = BomDetail::where('bom_id', $bom->id)->count();
        $jumlahBahanPendukung = BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)->count();
        $jumlahTotal = $jumlahBahanBaku + $jumlahBahanPendukung;
        
        // Cek status
        $hasBOM = $jumlahTotal > 0;
        $hasBiayaBahan = $produk->biaya_bahan > 0;
    @endphp
    
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $produk->nama_produk }}</td>
        <td>{{ $jumlahTotal }} item (BBB: {{ $jumlahBahanBaku }} | BP: {{ $jumlahBahanPendukung }})</td>
        <td>Rp {{ number_format($totalBiaya, 0, ',', '.') }}</td>
        <td>
            @if($hasBOM)
                <span class="badge bg-success">Sudah Ada BOM</span>
            @else
                <span class="badge bg-secondary">Belum Ada BOM</span>
            @endif
        </td>
        <td>
            @if($hasBOM)
                <!-- Detail, Edit, Hapus -->
            @else
                @if($hasBiayaBahan)
                    <!-- Tambah BOM -->
                @else
                    <!-- Isi Biaya Bahan Dulu -->
                @endif
            @endif
        </td>
    </tr>
@endforeach
```

### 2. Controller (app/Http/Controllers/BomController.php)

#### Method index() Baru
```php
public function index(Request $request)
{
    // Get all products with their BOM data
    $query = Produk::with(['boms', 'satuan']);
    
    // Filter by product name
    if ($request->filled('nama_produk')) {
        $query->where('nama_produk', 'like', '%' . $request->nama_produk . '%');
    }
    
    // Filter by BOM status
    if ($request->filled('status')) {
        if ($request->status == 'ada') {
            $query->whereHas('boms');
        } elseif ($request->status == 'belum') {
            $query->whereDoesntHave('boms');
        }
    }
    
    $produks = $query->orderBy('nama_produk')->paginate(15);
    
    return view('master-data.bom.index', compact('produks'));
}
```

**Perubahan:**
- ❌ Tidak lagi loop per detail bahan
- ✅ Loop per produk
- ✅ Hitung ringkasan di view
- ✅ Filter by nama produk & status BOM

## 🎯 Fitur Baru

### 1. Notifikasi Biaya Bahan Belum Ada

Jika produk belum punya biaya bahan, tampilkan modal:

```html
<button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#biayaBahanModal">
    <i class="fas fa-exclamation-triangle"></i> Isi Biaya Bahan Dulu
</button>

<!-- Modal -->
<div class="modal" id="biayaBahanModal">
    <div class="modal-content">
        <h5>Biaya Bahan Belum Ada</h5>
        <p>Produk <strong>{{ $produk->nama_produk }}</strong> belum memiliki data biaya bahan.</p>
        <p>Silakan isi biaya bahan terlebih dahulu sebelum membuat BOM.</p>
        <a href="{{ route('master-data.biaya-bahan.create', $produk->id) }}" class="btn btn-primary">
            Isi Biaya Bahan
        </a>
    </div>
</div>
```

### 2. Filter Status BOM

```html
<select name="status">
    <option value="">Semua</option>
    <option value="ada">Sudah Ada BOM</option>
    <option value="belum">Belum Ada BOM</option>
</select>
```

### 3. Badge Status

```php
@if($hasBOM)
    <span class="badge bg-success">
        <i class="fas fa-check-circle"></i> Sudah Ada BOM
    </span>
@else
    <span class="badge bg-secondary">
        <i class="fas fa-minus-circle"></i> Belum Ada BOM
    </span>
@endif

@if(!$hasBiayaBahan)
    <small class="badge bg-warning">
        <i class="fas fa-exclamation-triangle"></i> Belum ada biaya bahan
    </small>
@endif
```

## 📊 Perbandingan

| Aspek | Sebelum | Sesudah |
|-------|---------|---------|
| Tampilan | Per bahan (detail) | Per produk (ringkasan) |
| Jumlah Row | Banyak (duplikat produk) | Sedikit (1 produk = 1 row) |
| Tombol Aksi | Duplikat | Tidak duplikat |
| Informasi | Detail bahan | Ringkasan + status |
| Notifikasi Biaya Bahan | Tidak ada | Ada modal |
| Filter Status | Tidak ada | Ada (Sudah/Belum BOM) |
| Readability | ⭐⭐ | ⭐⭐⭐⭐⭐ |

## 🎯 Alur Penggunaan

### Scenario 1: Produk Sudah Ada BOM
```
1. User lihat list produk
2. Produk "Ayam Sambal Hijau" status: ✅ Sudah Ada BOM
3. Tombol: Detail | Edit | Hapus
4. User klik Detail → Lihat detail BOM lengkap
```

### Scenario 2: Produk Belum Ada BOM (Sudah Ada Biaya Bahan)
```
1. User lihat list produk
2. Produk "Roti Manis" status: ⚠️ Belum Ada BOM
3. Tombol: Tambah BOM
4. User klik Tambah BOM → Form create BOM
```

### Scenario 3: Produk Belum Ada BOM (Belum Ada Biaya Bahan)
```
1. User lihat list produk
2. Produk "Kue Kering" status: ⚠️ Belum Ada BOM + ⚠️ Belum ada biaya bahan
3. Tombol: ⚠️ Isi Biaya Bahan Dulu
4. User klik tombol → Modal muncul
5. User klik "Isi Biaya Bahan" → Form biaya bahan
6. Setelah isi biaya bahan → Baru bisa tambah BOM
```

## ✅ Hasil

### Keuntungan Tampilan Baru:

1. **Lebih Rapi** ✅
   - 1 produk = 1 row
   - Tidak ada duplikat
   - Layout konsisten

2. **Lebih Informatif** ✅
   - Ringkasan jumlah bahan (BBB + BP)
   - Status BOM jelas
   - Warning biaya bahan

3. **Lebih User-Friendly** ✅
   - Mudah scan produk mana yang sudah/belum ada BOM
   - Notifikasi jelas jika biaya bahan belum ada
   - Filter status memudahkan pencarian

4. **Lebih Efisien** ✅
   - Tidak perlu scroll banyak
   - Tombol aksi tidak duplikat
   - Query lebih efisien (per produk, bukan per detail)

## 🧪 Testing

### Checklist
- [ ] Tampilan per produk (bukan per bahan)
- [ ] Jumlah bahan tampil (BBB + BP)
- [ ] Total biaya BOM tampil
- [ ] Status BOM tampil (Sudah/Belum)
- [ ] Tombol Detail/Edit/Hapus untuk produk dengan BOM
- [ ] Tombol Tambah BOM untuk produk tanpa BOM (ada biaya bahan)
- [ ] Tombol "Isi Biaya Bahan Dulu" untuk produk tanpa biaya bahan
- [ ] Modal notifikasi muncul saat klik "Isi Biaya Bahan Dulu"
- [ ] Filter by nama produk bekerja
- [ ] Filter by status BOM bekerja
- [ ] Pagination bekerja

## 🎉 Kesimpulan

Tampilan BOM sudah **diperbaiki** dengan:
- ✅ Tampilan per produk (ringkasan)
- ✅ Tidak ada duplikat produk
- ✅ Notifikasi biaya bahan belum ada
- ✅ Filter status BOM
- ✅ UI lebih rapi dan user-friendly

**Sekarang user bisa dengan mudah melihat produk mana yang sudah/belum ada BOM!** 🎯
