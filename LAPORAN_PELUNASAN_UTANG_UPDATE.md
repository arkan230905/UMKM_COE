# Laporan Pelunasan Utang - Update

## Summary
Menambahkan kolom "Total Refund" di laporan pelunasan utang untuk menampilkan total refund dari setiap pembelian.

## Changes Made

### 1. View - index.blade.php
**Added**: Kolom "Total Refund" di tabel laporan

**Features**:
- Menampilkan total refund dari pembelian terkait
- Warna hijau jika ada refund (> 0)
- Warna abu-abu jika tidak ada refund (= 0)
- Total refund dihitung di footer

**Column Order**:
1. No
2. Tanggal
3. No. Pelunasan
4. Vendor
5. No. Faktur
6. Total Tagihan
7. **Total Refund** (NEW)
8. Dibayar
9. Status

### 2. View - pdf.blade.php
**Added**: Kolom "Total Refund" di PDF export

**Features**:
- Sama dengan tampilan web
- Format rupiah menggunakan helper `format_rupiah()`
- Total refund dihitung di footer

### 3. Data Source
**Source**: `$item->pembelian->total_refund`

**Calculation**: Menggunakan accessor di model Pembelian
```php
public function getTotalRefundAttribute()
{
    return $this->purchaseReturns()
        ->where('jenis_retur', 'refund')
        ->whereIn('status', ['disetujui', 'dikirim', 'selesai'])
        ->get()
        ->sum('total_with_ppn');
}
```

## How to Use

### 1. View Laporan
- Buka menu: **Laporan → Laporan Pelunasan Utang**
- Pilih bulan untuk filter data
- Klik "Filter" untuk menampilkan data
- Kolom "Total Refund" akan menampilkan refund dari setiap pembelian

### 2. Export PDF
- Pilih bulan terlebih dahulu
- Klik tombol **"Export PDF"** di kanan atas
- PDF akan otomatis terdownload
- PDF berisi semua kolom termasuk "Total Refund"

## Example Data

### Laporan Pelunasan Utang - May 2026

| No | Tanggal | No. Pelunasan | Vendor | No. Faktur | Total Tagihan | Total Refund | Dibayar | Status |
|----|---------|---------------|--------|------------|---------------|--------------|---------|--------|
| 1 | 18/05/2026 | PU-20260515-0003 | Tel-Mart | 10220250003 | Rp 375.000 | Rp 0 | Rp 375.000 | Lunas |
| 2 | 18/05/2026 | PU-20260515-0004 | Sukbir mart | 10220260004 | Rp 354.000 | Rp 0 | Rp 354.000 | Lunas |
| 3 | 17/05/2026 | PU-20260515-0001 | Tel-Mart | 10220280001 | Rp 570.000 | **Rp 75.000** | Rp 495.000 | Lunas |
| 4 | 17/05/2026 | PU-20260515-0002 | Sukbir mart | 10220280002 | Rp 290.000 | **Rp 24.000** | Rp 266.000 | Lunas |
| **Total** | | | | | **Rp 1.589.000** | **Rp 99.000** | **Rp 1.490.000** | |

### Explanation:
- **Row 1 & 2**: Tidak ada refund (Rp 0)
- **Row 3**: Ada refund Rp 75.000 dari retur yang disetujui
- **Row 4**: Ada refund Rp 24.000 dari retur yang disetujui
- **Total Refund**: Rp 99.000 (75.000 + 24.000)

## Notes

### Refund Criteria
Hanya retur dengan kriteria berikut yang dihitung:
- **Jenis Retur**: `refund` (bukan `tukar_barang`)
- **Status**: `disetujui`, `dikirim`, atau `selesai`

### Display Logic
- **Web View**: 
  - Refund > 0: Tampil hijau dengan nilai
  - Refund = 0: Tampil abu-abu "Rp 0"
  
- **PDF View**: 
  - Semua nilai ditampilkan dengan format rupiah
  - Tidak ada pewarnaan khusus

### Total Calculation
Footer menampilkan total dari semua kolom:
- Total Tagihan: Sum of all `total_harga`
- Total Refund: Sum of all `total_refund`
- Total Dibayar: Sum of all `jumlah`

## Files Modified

1. `resources/views/laporan/pelunasan-utang/index.blade.php`
   - Added "Total Refund" column in table header
   - Added refund display in table body
   - Added refund total in footer

2. `resources/views/laporan/pelunasan-utang/pdf.blade.php`
   - Added "Total Refund" column in table header
   - Added refund display in table body
   - Added refund total in footer

## Testing

- [x] Kolom "Total Refund" muncul di web view
- [x] Kolom "Total Refund" muncul di PDF export
- [x] Nilai refund sesuai dengan data retur
- [x] Total refund dihitung dengan benar
- [x] PDF dapat didownload
- [x] Format tampilan sesuai

## Conclusion

Laporan pelunasan utang sekarang menampilkan kolom "Total Refund" yang menunjukkan total refund dari setiap pembelian. Fitur export PDF juga sudah include kolom ini.
