# ✅ FITUR EXPORT BUKU BESAR KE EXCEL

## Fitur Baru

### Export Buku Besar ke Excel
Perusahaan sekarang bisa export semua data Buku Besar ke file Excel untuk arsip hardfile.

**Format Export:**
- ✅ **Multi-Sheet Excel** - Setiap akun punya sheet sendiri
- ✅ **Data Lengkap** - Saldo awal, transaksi, saldo akhir
- ✅ **Format Profesional** - Header berwarna, border rapi, format angka
- ✅ **Filter Periode** - Bisa pilih tanggal mulai dan akhir

---

## File yang Diubah

### 1. `app/Exports/BukuBesarExport.php`
**Perubahan:** Dari PhpSpreadsheet manual → Laravel Excel (Multi-Sheet)

```php
// Menggunakan WithMultipleSheets
class BukuBesarExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];
        
        // Setiap akun jadi 1 sheet
        foreach ($accounts as $account) {
            $sheets[] = new BukuBesarSheetExport($account, ...);
        }
        
        return $sheets;
    }
}

// Class untuk setiap sheet
class BukuBesarSheetExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    // Data per akun
    public function collection() { ... }
    
    // Header kolom
    public function headings() { ... }
    
    // Styling
    public function styles(Worksheet $sheet) { ... }
    
    // Nama sheet
    public function title() { ... }
}
```

### 2. `app/Http/Controllers/AkuntansiController.php`
**Tambahan:** Import Excel facade dan update method export

```php
use Maatwebsite\Excel\Facades\Excel;

public function bukuBesarExportExcel(Request $request)
{
    $from = $request->get('from');
    $to   = $request->get('to');

    return Excel::download(
        new BukuBesarExport($from, $to),
        'buku-besar-'.date('Y-m-d').'.xlsx'
    );
}
```

### 3. `resources/views/akuntansi/buku-besar.blade.php`
**Perubahan:** Aktifkan button export yang sebelumnya dinonaktifkan

```blade
<div class="d-flex gap-2">
  <a href="{{ route('akuntansi.buku-besar.export-excel', ['from' => $from, 'to' => $to]) }}" 
     class="btn btn-success">
    <i class="bi bi-file-earmark-excel"></i> Export Excel (Semua Akun)
  </a>
</div>
```

---

## Hasil Export Excel

### Struktur File
```
buku-besar-2025-11-11.xlsx
├── Sheet 1: 101 Kas
│   ├── Header: Tanggal | Ref Type | Ref ID | Keterangan | Debit | Kredit | Saldo
│   ├── Saldo Awal
│   ├── Transaksi 1
│   ├── Transaksi 2
│   └── Saldo Akhir
├── Sheet 2: 102 Bank
│   └── ...
├── Sheet 3: 1101 Kas Kecil
│   └── ...
└── Sheet N: Akun lainnya
```

### Fitur Excel
✅ **Multi-Sheet** - Setiap akun punya tab sendiri  
✅ **Header Berwarna** - Biru dengan teks putih  
✅ **Format Angka** - Separator ribuan (1.000.000)  
✅ **Bold Text** - Saldo awal dan saldo akhir  
✅ **Background** - Saldo akhir dengan background abu-abu  
✅ **Column Width** - Otomatis disesuaikan  
✅ **Border** - Semua cell punya border  

---

## Cara Menggunakan

### 1. Buka Halaman Buku Besar
```
http://127.0.0.1:8000/akuntansi/buku-besar
```

### 2. Pilih Filter (Opsional)
- **Akun:** Pilih akun tertentu atau kosongkan untuk semua
- **Dari:** Tanggal mulai periode
- **Sampai:** Tanggal akhir periode

### 3. Klik Export Excel
- Klik tombol hijau **"Export Excel (Semua Akun)"**
- File akan terdownload: `buku-besar-2025-11-11.xlsx`

### 4. Buka File Excel
- Setiap akun ada di sheet terpisah
- Nama sheet: Kode Akun + Nama Akun (max 31 karakter)
- Data lengkap dengan saldo awal dan akhir

---

## Keuntungan

### Untuk Perusahaan
✅ **Arsip Digital** - Punya hardfile untuk backup  
✅ **Audit Trail** - Semua transaksi tercatat lengkap  
✅ **Analisis Mudah** - Bisa diolah di Excel  
✅ **Profesional** - Format rapi dan terstruktur  

### Untuk Akuntan
✅ **Cepat** - Export semua akun sekaligus  
✅ **Lengkap** - Saldo awal, transaksi, saldo akhir  
✅ **Terpisah** - Setiap akun di sheet sendiri  
✅ **Siap Print** - Format sudah rapi  

---

## Testing

### Test Export Semua Akun
```bash
# 1. Buka browser
http://127.0.0.1:8000/akuntansi/buku-besar

# 2. Klik "Export Excel (Semua Akun)"

# 3. Cek file yang terdownload
# Nama: buku-besar-2025-11-11.xlsx

# 4. Buka file Excel
# Cek:
✓ Ada banyak sheet (1 sheet per akun)
✓ Setiap sheet punya data lengkap
✓ Format rapi dengan warna dan border
✓ Angka pakai separator ribuan
```

### Test Export dengan Filter Periode
```bash
# 1. Pilih tanggal
Dari: 01/11/2025
Sampai: 30/11/2025

# 2. Klik "Export Excel"

# 3. Cek hasil
✓ Hanya transaksi di periode tersebut
✓ Saldo awal tetap akurat (termasuk mutasi sebelum periode)
✓ Saldo akhir sesuai dengan periode
```

---

## Contoh Output Excel

### Sheet: 101 Kas

| Tanggal | Ref Type | Ref ID | Keterangan | Debit | Kredit | Saldo |
|---------|----------|--------|------------|-------|--------|-------|
| | | | **Saldo Awal** | | | 5.000.000 |
| 01/11/2025 | sale | 1 | Penjualan Tunai | 2.000.000 | | 7.000.000 |
| 05/11/2025 | purchase | 3 | Pembelian Bahan Baku | | 1.500.000 | 5.500.000 |
| 10/11/2025 | expense | 2 | Bayar Listrik | | 500.000 | 5.000.000 |
| | | | **Saldo Akhir** | | | **5.000.000** |

---

## Keamanan

✅ **Tidak Merusak Data** - Hanya export, tidak ubah database  
✅ **Tidak Install Package Baru** - Pakai Laravel Excel yang sudah ada  
✅ **Tidak Ada Konflik** - Kompatibel dengan semua fitur existing  
✅ **Tested** - Sudah dicek tidak ada error  

---

## 🎉 SELESAI!

**Status:** ✅ BERHASIL  
**Fitur:** ✅ Export Buku Besar ke Excel  
**Format:** ✅ Multi-Sheet Profesional  
**Aman:** ✅ Tidak Merusak Apapun  

Perusahaan sekarang punya fitur export Buku Besar untuk arsip hardfile! 🚀
