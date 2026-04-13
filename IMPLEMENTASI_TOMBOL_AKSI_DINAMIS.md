# Implementasi Tombol Aksi Dinamis - Retur Pembelian

## 📋 Overview

Implementasi tombol aksi dinamis pada halaman List Retur Pembelian yang menampilkan **hanya 1 tombol next step** berdasarkan kombinasi `jenis_retur` dan `status` saat ini.

## 🔄 Workflow Logic

### Tukar Barang (`jenis_retur = 'tukar_barang'`)

| Status | Tombol | Action | Next Status |
|--------|--------|--------|-------------|
| `menunggu_acc` | **ACC Vendor** | Update status | `disetujui` |
| `disetujui` | **Kirim Barang** | Update status | `dikirim` |
| `dikirim` | **Diproses Vendor** | Update status | `diproses` |
| `diproses` | **Barang Diterima** | Update status | `selesai` |
| `selesai` | *Tidak ada tombol* | - | - |

### Refund (`jenis_retur = 'refund'`)

| Status | Tombol | Action | Next Status |
|--------|--------|--------|-------------|
| `menunggu_acc` | **ACC Vendor** | Update status | `disetujui` |
| `disetujui` | **Kirim Barang** | Update status | `dikirim` |
| `dikirim` | **Vendor Terima** | Update status | `diterima` |
| `diterima` | **Terima Uang** | Update status | `refund_selesai` |
| `refund_selesai` | *Tidak ada tombol* | - | - |

## 🎯 Fitur yang Diimplementasikan

✅ **Hanya 1 tombol aksi per baris** (next step)  
✅ **Tombol berubah otomatis** setelah status diupdate  
✅ **Tidak ada tombol** jika status sudah final  
✅ **Form POST** dengan CSRF protection  
✅ **Konfirmasi JavaScript** sebelum submit  
✅ **Icon yang sesuai** untuk setiap aksi  
✅ **Warna tombol berbeda** untuk setiap tahap  
✅ **Tombol hapus** hanya muncul jika belum selesai  

## 📁 File yang Dimodifikasi

### 1. View: `resources/views/transaksi/retur-pembelian/index.blade.php`

**Bagian yang diubah:** Kolom "Aksi" dalam tabel

```blade
{{-- Dynamic Action Button (Next Step Only) --}}
@if($retur->jenis_retur == 'tukar_barang')
    {{-- WORKFLOW TUKAR BARANG --}}
    @if($retur->status == 'menunggu_acc')
        <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm('Yakin ingin ACC retur ini?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-check me-1"></i>ACC Vendor
            </button>
        </form>
    @elseif($retur->status == 'disetujui')
        <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm('Yakin ingin kirim barang?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-warning">
                <i class="fas fa-shipping-fast me-1"></i>Kirim Barang
            </button>
        </form>
    @elseif($retur->status == 'dikirim')
        <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm('Yakin barang sudah diproses vendor?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-secondary">
                <i class="fas fa-cogs me-1"></i>Diproses Vendor
            </button>
        </form>
    @elseif($retur->status == 'diproses')
        <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm('Yakin barang sudah diterima?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-success">
                <i class="fas fa-check-circle me-1"></i>Barang Diterima
            </button>
        </form>
    @endif
@elseif($retur->jenis_retur == 'refund')
    {{-- WORKFLOW REFUND --}}
    @if($retur->status == 'menunggu_acc')
        <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm('Yakin ingin ACC retur ini?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-check me-1"></i>ACC Vendor
            </button>
        </form>
    @elseif($retur->status == 'disetujui')
        <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm('Yakin ingin kirim barang?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-warning">
                <i class="fas fa-shipping-fast me-1"></i>Kirim Barang
            </button>
        </form>
    @elseif($retur->status == 'dikirim')
        <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm('Yakin vendor sudah terima barang?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-info">
                <i class="fas fa-handshake me-1"></i>Vendor Terima
            </button>
        </form>
    @elseif($retur->status == 'diterima')
        <form action="{{ route('transaksi.retur-pembelian.update-status', $retur->id) }}" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm('Yakin sudah terima uang refund?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-success">
                <i class="fas fa-money-bill-wave me-1"></i>Terima Uang
            </button>
        </form>
    @endif
@endif
```

## 🛠️ Backend Support

### Model: `app/Models/PurchaseReturn.php`

Model sudah memiliki method yang mendukung implementasi ini:

- `getNextStatusAttribute()` - Menentukan status berikutnya
- `getActionButtonAttribute()` - Menentukan teks dan class tombol
- `getIsCompletedAttribute()` - Mengecek apakah status sudah final

### Controller: `app/Http/Controllers/ReturController.php`

Method yang digunakan:

- `updateStatus($id)` - Mengupdate status ke tahap berikutnya
- `destroyPembelian($id)` - Menghapus retur

### Routes: `routes/web.php`

Route yang digunakan:

```php
Route::post('/update-status/{id}', [ReturController::class, 'updateStatus'])
    ->name('transaksi.retur-pembelian.update-status');
Route::delete('/{id}', [ReturController::class, 'destroyPembelian'])
    ->name('transaksi.retur-pembelian.destroy');
```

## 🎨 Styling

### Warna Tombol

- **ACC Vendor**: `btn-primary` (Biru)
- **Kirim Barang**: `btn-warning` (Kuning)
- **Diproses Vendor**: `btn-secondary` (Abu-abu)
- **Vendor Terima**: `btn-info` (Cyan)
- **Barang Diterima**: `btn-success` (Hijau)
- **Terima Uang**: `btn-success` (Hijau)

### Icon

- **ACC**: `fas fa-check`
- **Kirim**: `fas fa-shipping-fast`
- **Proses**: `fas fa-cogs`
- **Terima**: `fas fa-handshake`
- **Selesai**: `fas fa-check-circle`
- **Uang**: `fas fa-money-bill-wave`

## 🧪 Testing

### Test Case 1: Tukar Barang

1. Buat retur dengan `jenis_retur = 'tukar_barang'`
2. Status awal: `menunggu_acc` → Tombol: **ACC Vendor**
3. Klik tombol → Status: `disetujui` → Tombol: **Kirim Barang**
4. Klik tombol → Status: `dikirim` → Tombol: **Diproses Vendor**
5. Klik tombol → Status: `diproses` → Tombol: **Barang Diterima**
6. Klik tombol → Status: `selesai` → **Tidak ada tombol**

### Test Case 2: Refund

1. Buat retur dengan `jenis_retur = 'refund'`
2. Status awal: `menunggu_acc` → Tombol: **ACC Vendor**
3. Klik tombol → Status: `disetujui` → Tombol: **Kirim Barang**
4. Klik tombol → Status: `dikirim` → Tombol: **Vendor Terima**
5. Klik tombol → Status: `diterima` → Tombol: **Terima Uang**
6. Klik tombol → Status: `refund_selesai` → **Tidak ada tombol**

## 🔒 Security

- ✅ CSRF Protection dengan `@csrf`
- ✅ Konfirmasi JavaScript sebelum submit
- ✅ Validasi di controller sebelum update status
- ✅ Transaction rollback jika terjadi error

## 📝 Notes

- Implementasi menggunakan conditional Blade `@if` sesuai permintaan
- Hanya menampilkan 1 tombol next step per baris
- Tombol otomatis hilang jika status sudah final
- Menggunakan method yang sudah ada di model dan controller
- Backward compatible dengan implementasi sebelumnya