# Quick Reference - Sistem Retur

## 🚀 Quick Start

### 1. Jalankan Migration
```bash
php artisan migrate
```

### 2. Seed COA (jika belum ada)
```php
// Tambahkan di CoaSeeder atau jalankan manual
Coa::create([
    'kode_akun' => '4-1200',
    'nama_akun' => 'Retur Penjualan',
    'kategori' => 'Pendapatan',
    'tipe' => 'Contra Revenue'
]);

Coa::create([
    'kode_akun' => '5-1200',
    'nama_akun' => 'Retur Pembelian',
    'kategori' => 'Beban',
    'tipe' => 'Contra Expense'
]);
```

### 3. Update Routes
Ganti `ReturController` dengan `ReturControllerNew` di routes

## 📝 Cheat Sheet

### Tipe Retur
| Tipe | Deskripsi | Arah Barang | Dampak Stok |
|------|-----------|-------------|-------------|
| `penjualan` | Dari pelanggan | Masuk ke kita | Stok (+) |
| `pembelian` | Ke vendor | Keluar dari kita | Stok (-) |

### Tipe Kompensasi
| Tipe | Deskripsi | Dampak |
|------|-----------|--------|
| `barang` | Ganti dengan barang baru | Stok berubah |
| `uang` | Ganti dengan uang | Kas/Bank berubah |

### Status Workflow
| Status | Deskripsi | Bisa Edit? | Bisa Delete? |
|--------|-----------|------------|--------------|
| `draft` | Baru dibuat | ✅ Ya | ✅ Ya |
| `diproses` | Sedang proses | ❌ Tidak | ❌ Tidak |
| `selesai` | Sudah selesai | ❌ Tidak | ❌ Tidak |

## 🔧 API Endpoints

### Get Data Penjualan
```javascript
GET /transaksi/retur/api/penjualan/{id}

Response:
{
    "success": true,
    "data": {
        "id": 1,
        "kode_penjualan": "PJ-20251110-001",
        "details": [...]
    }
}
```

### Get Data Pembelian
```javascript
GET /transaksi/retur/api/pembelian/{id}

Response:
{
    "success": true,
    "data": {
        "id": 1,
        "kode_pembelian": "PB-20251110-001",
        "details": [...]
    }
}
```

### Get Data Produk
```javascript
GET /transaksi/retur/api/produk/{id}

Response:
{
    "success": true,
    "data": {
        "id": 1,
        "nama": "Produk A",
        "satuan": "pcs",
        "harga": 100000,
        "stok": 50
    }
}
```

### Get Data Bahan Baku
```javascript
GET /transaksi/retur/api/bahan-baku/{id}

Response:
{
    "success": true,
    "data": {
        "id": 1,
        "nama": "Tepung",
        "satuan": "kg",
        "harga": 10000,
        "stok": 100
    }
}
```

## 💻 Code Examples

### Proses Retur Penjualan + Barang
```php
$data = [
    'tanggal' => '2025-11-10',
    'tipe_retur' => 'penjualan',
    'tipe_kompensasi' => 'barang',
    'referensi_id' => 1,
    'referensi_kode' => 'PJ-20251110-001',
    'keterangan' => 'Produk rusak',
    'items' => [
        [
            'item_type' => 'produk',
            'item_id' => 1,
            'item_nama' => 'Produk A',
            'qty' => 10,
            'satuan' => 'pcs',
            'harga_satuan' => 100000,
            'keterangan' => 'Rusak saat pengiriman'
        ]
    ],
    'kompensasi_items' => [
        [
            'item_id' => 2,
            'item_nama' => 'Produk B',
            'qty' => 8,
            'satuan' => 'pcs',
            'harga_satuan' => 125000
        ]
    ]
];

$returService = new ReturService();
$result = $returService->prosesReturPenjualanBarang($data);
```

### Proses Retur Penjualan + Uang
```php
$data = [
    'tanggal' => '2025-11-10',
    'tipe_retur' => 'penjualan',
    'tipe_kompensasi' => 'uang',
    'items' => [...],
    'kompensasi_uang' => [
        'nilai' => 1000000,
        'metode' => 'cash',
        'akun_id' => 1, // ID akun Kas
        'keterangan' => 'Refund cash'
    ]
];

$result = $returService->prosesReturPenjualanUang($data);
```

### Proses Retur Pembelian + Barang
```php
$data = [
    'tanggal' => '2025-11-10',
    'tipe_retur' => 'pembelian',
    'tipe_kompensasi' => 'barang',
    'items' => [
        [
            'item_type' => 'bahan_baku',
            'item_id' => 1,
            'item_nama' => 'Tepung',
            'qty' => 50,
            'satuan' => 'kg',
            'harga_satuan' => 10000
        ]
    ],
    'kompensasi_items' => [
        [
            'item_id' => 1,
            'item_nama' => 'Tepung',
            'qty' => 50,
            'satuan' => 'kg',
            'harga_satuan' => 10000
        ]
    ]
];

$result = $returService->prosesReturPembelianBarang($data);
```

### Proses Retur Pembelian + Uang
```php
$data = [
    'tanggal' => '2025-11-10',
    'tipe_retur' => 'pembelian',
    'tipe_kompensasi' => 'uang',
    'items' => [...],
    'kompensasi_uang' => [
        'nilai' => 500000,
        'metode' => 'transfer',
        'akun_id' => 2, // ID akun Bank
        'keterangan' => 'Refund dari vendor'
    ]
];

$result = $returService->prosesReturPembelianUang($data);
```

## 🎨 Blade Component Examples

### Display Retur Status Badge
```blade
@php
    $statusColors = [
        'draft' => 'bg-gray-500',
        'diproses' => 'bg-yellow-500',
        'selesai' => 'bg-green-500'
    ];
@endphp

<span class="px-2 py-1 text-xs rounded {{ $statusColors[$retur->status] ?? 'bg-gray-500' }} text-white">
    {{ ucfirst($retur->status) }}
</span>
```

### Display Tipe Retur Badge
```blade
@if($retur->tipe_retur === 'penjualan')
    <span class="px-2 py-1 text-xs rounded bg-blue-500 text-white">
        Retur Penjualan
    </span>
@else
    <span class="px-2 py-1 text-xs rounded bg-purple-500 text-white">
        Retur Pembelian
    </span>
@endif
```

### Display Kompensasi Info
```blade
@if($retur->tipe_kompensasi === 'barang')
    <div class="flex items-center">
        <svg class="w-4 h-4 mr-1">...</svg>
        <span>Kompensasi Barang</span>
    </div>
@else
    <div class="flex items-center">
        <svg class="w-4 h-4 mr-1">...</svg>
        <span>Kompensasi Uang</span>
    </div>
@endif
```

## 🔍 Query Examples

### Get Retur dengan Filter
```php
// Retur penjualan bulan ini
$returs = Retur::tipePenjualan()
    ->whereMonth('tanggal', now()->month)
    ->whereYear('tanggal', now()->year)
    ->get();

// Retur yang belum selesai
$pending = Retur::where('status', '!=', 'selesai')->get();

// Retur dengan kompensasi uang
$returUang = Retur::where('tipe_kompensasi', 'uang')->get();
```

### Get Total Nilai Retur
```php
// Total retur penjualan bulan ini
$totalReturPenjualan = Retur::tipePenjualan()
    ->whereMonth('tanggal', now()->month)
    ->sum('total_nilai_retur');

// Total retur pembelian tahun ini
$totalReturPembelian = Retur::tipePembelian()
    ->whereYear('tanggal', now()->year)
    ->sum('total_nilai_retur');
```

### Get Retur dengan Relasi
```php
// Dengan semua relasi
$retur = Retur::with([
    'details',
    'kompensasis',
    'jurnalEntries.journalLines.coa',
    'creator'
])->find($id);

// Hanya detail dan kompensasi
$retur = Retur::with(['details', 'kompensasis'])->find($id);
```

## 📊 Jurnal Entries Reference

### Retur Penjualan - Penerimaan Barang
```
Dr. Persediaan Produk Jadi (1-1400)    Rp XXX
Cr. Retur Penjualan (4-1200)           Rp XXX
```

### Retur Penjualan - Kompensasi Barang
```
Dr. Retur Penjualan (4-1200)           Rp XXX
Cr. Persediaan Produk Jadi (1-1400)    Rp XXX
```

### Retur Penjualan - Kompensasi Uang
```
Dr. Retur Penjualan (4-1200)           Rp XXX
Cr. Kas/Bank (1-1100/1-1200)           Rp XXX
```

### Retur Pembelian - Pengiriman Barang
```
Dr. Retur Pembelian (5-1200)           Rp XXX
Cr. Persediaan Bahan Baku (1-1300)     Rp XXX
```

### Retur Pembelian - Kompensasi Barang
```
Dr. Persediaan Bahan Baku (1-1300)     Rp XXX
Cr. Retur Pembelian (5-1200)           Rp XXX
```

### Retur Pembelian - Kompensasi Uang
```
Dr. Kas/Bank (1-1100/1-1200)           Rp XXX
Cr. Retur Pembelian (5-1200)           Rp XXX
```

## ⚠️ Common Errors & Solutions

### Error: "Stok tidak mencukupi"
**Penyebab:** Stok bahan baku tidak cukup untuk retur pembelian
**Solusi:** Cek stok tersedia sebelum proses retur

### Error: "Retur tidak dapat diubah"
**Penyebab:** Status retur bukan draft
**Solusi:** Hanya retur dengan status draft yang bisa diubah

### Error: "Akun COA tidak ditemukan"
**Penyebab:** Akun Retur Penjualan/Pembelian belum ada
**Solusi:** Seed COA terlebih dahulu

### Error: "Transaction rollback"
**Penyebab:** Ada error di tengah proses
**Solusi:** Cek log error untuk detail, semua perubahan akan di-rollback otomatis

## 🧪 Testing Commands

```bash
# Test create retur penjualan
php artisan tinker
>>> $service = new App\Services\ReturService();
>>> $result = $service->prosesReturPenjualanBarang([...]);

# Check stok after retur
>>> App\Models\Produk::find(1)->stok;

# Check jurnal entries
>>> App\Models\Retur::find(1)->jurnalEntries;

# Check stock movements
>>> App\Models\StockMovement::where('reference_type', 'retur')->get();
```

## 📱 Mobile-Friendly Tips

1. Gunakan responsive table untuk list retur
2. Tambahkan filter collapse untuk mobile
3. Gunakan modal untuk detail retur
4. Implementasi infinite scroll untuk list panjang

## 🔐 Security Checklist

- ✅ Validasi input di controller
- ✅ Authorization check (middleware)
- ✅ Transaction untuk data consistency
- ✅ Audit trail (created_by, timestamps)
- ✅ Soft delete untuk data historis
- ✅ Validasi stok sebelum proses

---

**Tips:** Bookmark halaman ini untuk referensi cepat saat development!
