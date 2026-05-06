# Script untuk menghapus semua merge conflict markers
# Akan memilih versi dari branch chindii2 (setelah =======)

$files = @(
    "app/Http/Controllers/AkuntansiController.php",
    "app/Services/JournalService.php",
    "app/Http/Controllers/AsetController.php",
    "app/Http/Controllers/BahanBakuController.php",
    "app/Http/Controllers/BahanPendukungController.php",
    "app/Models/BopProses.php",
    "app/Models/Jabatan.php",
    "app/Http/Controllers/BomController.php",
    "app/Models/Pegawai.php",
    "app/Models/Pembelian.php",
    "app/Http/Controllers/BopController.php",
    "app/Models/Penggajian.php",
    "app/Http/Controllers/CoaController.php",
    "app/Http/Controllers/DashboardController.php",
    "app/Http/Controllers/JabatanController.php",
    "check_bop_data.php"
)

foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "Processing: $file"
        
        # Baca file
        $content = Get-Content $file -Raw
        
        # Hapus merge conflict markers dengan memilih versi chindii2
        # Pattern: <<<<<<< HEAD ... ======= ... >>>>>>> hash
        # Kita ambil bagian setelah ======= dan sebelum >>>>>>>
        
        $pattern = '<<<<<<< HEAD.*?=======(.*?)>>>>>>> [a-f0-9]+'
        $content = $content -replace $pattern, '$1'
        
        # Simpan kembali
        Set-Content -Path $file -Value $content -NoNewline
        
        Write-Host "Fixed: $file"
    } else {
        Write-Host "File not found: $file"
    }
}

Write-Host "`nDone! All merge conflicts resolved."
Write-Host "Versi yang dipilih: chindii2 branch (setelah =======)"
