# Fitur COA dengan Periode

## Deskripsi
Halaman COA (Chart of Accounts) sekarang dilengkapi dengan dropdown pemilihan periode/bulan. Setiap periode memiliki saldo awal yang berbeda-beda untuk setiap akun COA.

## Fitur Utama

### 1. Dropdown Pemilihan Periode
- Dropdown di pojok kanan atas halaman COA
- Menampilkan daftar periode (bulan-tahun) yang tersedia
- Periode yang sudah ditutup ditandai dengan tanda ✓
- Otomatis memuat ulang halaman saat periode dipilih

### 2. Saldo Awal Per Periode
- Setiap COA menampilkan saldo awal sesuai periode yang dipilih
- Saldo yang berbeda dari saldo default ditampilkan dengan warna biru tebal
- Menampilkan saldo default di bawahnya untuk perbandingan

### 3. Logika Saldo Awal
Sistem menentukan saldo awal dengan prioritas berikut:
1. **Saldo Periode**: Jika ada saldo yang sudah di-post untuk periode tersebut
2. **Saldo Akhir Periode Sebelumnya**: Jika tidak ada saldo periode, ambil dari saldo akhir periode sebelumnya
3. **Saldo Default COA**: Jika tidak ada periode sebelumnya, gunakan saldo awal default dari master COA

### 4. Indikator Status Periode
- Badge hijau "Periode Ditutup" untuk periode yang sudah di-post
- Badge kuning "Periode Aktif" untuk periode yang masih terbuka
- Info box menampilkan periode yang sedang dipilih

## Cara Penggunaan

### Melihat Saldo COA Per Periode
1. Buka halaman **Master Data > COA**
2. Pilih periode dari dropdown di pojok kanan atas
3. Tabel akan menampilkan saldo awal sesuai periode yang dipilih
4. Saldo yang berbeda dari default akan ditampilkan dengan warna biru

### Memahami Tampilan Saldo
- **Saldo Normal (Hitam)**: Saldo sama dengan saldo default COA
- **Saldo Biru Tebal**: Saldo berbeda dari default (sudah ada posting dari periode sebelumnya)
- **Teks Kecil di Bawah**: Menampilkan saldo default untuk perbandingan

## Integrasi dengan Sistem Lain

### Neraca Saldo
Halaman Neraca Saldo juga menggunakan sistem periode yang sama:
- Dropdown pemilihan periode
- Tombol "Post Saldo Akhir" untuk menutup periode
- Tombol "Buka Periode" untuk membuka kembali periode yang sudah ditutup

### Posting Saldo
Ketika periode ditutup di Neraca Saldo:
1. Saldo akhir periode saat ini dihitung
2. Saldo akhir menjadi saldo awal periode berikutnya
3. Data disimpan di tabel `coa_period_balances`
4. Periode ditandai sebagai "closed"

## Database

### Tabel: coa_periods
Menyimpan informasi periode:
- `id`: ID periode
- `periode`: Format YYYY-MM (contoh: 2024-11)
- `tanggal_mulai`: Tanggal mulai periode
- `tanggal_selesai`: Tanggal selesai periode
- `is_closed`: Status periode (0=aktif, 1=ditutup)
- `closed_at`: Waktu penutupan periode
- `closed_by`: User yang menutup periode

### Tabel: coa_period_balances
Menyimpan saldo per periode per COA:
- `id`: ID record
- `kode_akun`: Kode akun COA
- `period_id`: ID periode
- `saldo_awal`: Saldo awal periode
- `saldo_akhir`: Saldo akhir periode
- `is_posted`: Status posting (0=belum, 1=sudah)

## Manfaat

1. **Akurasi Laporan**: Setiap periode memiliki saldo awal yang akurat
2. **Audit Trail**: Riwayat saldo per periode tersimpan dengan baik
3. **Fleksibilitas**: Bisa melihat saldo COA di periode manapun
4. **Konsistensi**: Saldo akhir periode otomatis menjadi saldo awal periode berikutnya
5. **Kontrol**: Periode yang sudah ditutup tidak bisa diubah (kecuali dibuka kembali)

## Tips

- Selalu tutup periode setelah selesai input transaksi bulanan
- Cek saldo COA sebelum menutup periode
- Periode hanya bisa dibuka kembali jika periode berikutnya belum ditutup
- Gunakan fitur ini untuk rekonsiliasi bulanan

## File Terkait

- **Controller**: `app/Http/Controllers/CoaController.php`
- **View**: `resources/views/master-data/coa/index.blade.php`
- **Model**: `app/Models/Coa.php`, `app/Models/CoaPeriod.php`, `app/Models/CoaPeriodBalance.php`
- **Migration**: 
  - `database/migrations/2024_01_15_000001_create_coa_periods_table.php`
  - `database/migrations/2024_01_15_000002_create_coa_period_balances_table.php`
