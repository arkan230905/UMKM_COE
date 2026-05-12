# Script untuk menghapus SEMUA merge conflict markers di seluruh project
# Akan memilih versi dari branch chindii2 (setelah =======)

Write-Host "Mencari semua file dengan merge conflict markers..."

# Cari semua file yang mengandung merge conflict markers
$conflictFiles = git diff --name-only --diff-filter=U 2>$null

if (-not $conflictFiles) {
    # Jika git diff tidak menemukan, cari manual dengan grep
    $conflictFiles = git grep -l "^<<<<<<< HEAD" 2>$null
}

if (-not $conflictFiles) {
    Write-Host "Tidak ada file dengan merge conflict markers ditemukan."
    exit 0
}

$fileCount = 0
foreach ($file in $conflictFiles) {
    if (Test-Path $file) {
        Write-Host "Processing: $file"
        
        # Baca file
        $content = Get-Content $file -Raw -ErrorAction SilentlyContinue
        
        if ($content) {
            # Hapus merge conflict markers dengan regex yang lebih robust
            # Pattern: <<<<<<< HEAD\n...content...\n=======\n...content...\n>>>>>>> hash
            # Kita ambil bagian setelah ======= dan sebelum >>>>>>>
            
            $pattern = '(?s)<<<<<<< HEAD.*?=======(.*?)>>>>>>> [a-f0-9]+\s*'
            $newContent = $content -replace $pattern, '$1'
            
            # Jika masih ada conflict markers, coba pattern alternatif
            if ($newContent -match '<<<<<<< HEAD') {
                $pattern2 = '(?s)<<<<<<< HEAD[^\n]*\n(.*?)\n=======\n(.*?)\n>>>>>>> [a-f0-9]+\s*'
                $newContent = $newContent -replace $pattern2, '$2'
            }
            
            # Simpan kembali
            Set-Content -Path $file -Value $newContent -NoNewline -ErrorAction SilentlyContinue
            
            Write-Host "  ✓ Fixed: $file"
            $fileCount++
        }
    }
}

Write-Host "`n✓ Done! Fixed $fileCount files."
Write-Host "Versi yang dipilih: chindii2 branch (setelah =======)"
