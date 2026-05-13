# SCRIPT UNTUK SELESAIKAN GIT MERGE STUCK
# Jalankan dengan: powershell -ExecutionPolicy Bypass -File SELESAIKAN_GIT.ps1

Write-Host "=== SELESAIKAN GIT MERGE STUCK ===" -ForegroundColor Cyan
Write-Host ""

# Step 1: Kill vim
Write-Host "Step 1: Killing vim processes..." -ForegroundColor Yellow
Get-Process | Where-Object {$_.Name -like "*vim*" -or $_.Name -like "*vi*"} | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 1

# Step 2: Set git editor
Write-Host "Step 2: Setting git editor to notepad..." -ForegroundColor Yellow
git config --global core.editor "notepad"

# Step 3: Remove merge state files
Write-Host "Step 3: Removing merge state files..." -ForegroundColor Yellow
Remove-Item -Path ".git/MERGE_HEAD" -Force -ErrorAction SilentlyContinue
Remove-Item -Path ".git/MERGE_MODE" -Force -ErrorAction SilentlyContinue
Remove-Item -Path ".git/MERGE_MSG" -Force -ErrorAction SilentlyContinue
Remove-Item -Path ".git/index.lock" -Force -ErrorAction SilentlyContinue

# Step 4: Reset to clean state
Write-Host "Step 4: Resetting to clean state..." -ForegroundColor Yellow
git reset --hard HEAD

# Step 5: Check status
Write-Host ""
Write-Host "Step 5: Checking git status..." -ForegroundColor Yellow
git status

Write-Host ""
Write-Host "=== GIT SUDAH BERSIH ===" -ForegroundColor Green
Write-Host ""
Write-Host "Sekarang jalankan:" -ForegroundColor Cyan
Write-Host "  git pull origin main --rebase" -ForegroundColor White
Write-Host "  git push origin main" -ForegroundColor White
Write-Host ""

# Tanya user apakah mau lanjut otomatis
$response = Read-Host "Mau saya lanjutkan pull dan push otomatis? (y/n)"

if ($response -eq "y") {
    Write-Host ""
    Write-Host "Pulling from remote..." -ForegroundColor Yellow
    git pull origin main --rebase
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "Pushing to remote..." -ForegroundColor Yellow
        git push origin main
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host ""
            Write-Host "=== SUKSES! ===" -ForegroundColor Green
            Write-Host "Semua perubahan sudah di-push ke GitHub!" -ForegroundColor Green
        } else {
            Write-Host ""
            Write-Host "Push gagal. Coba manual:" -ForegroundColor Red
            Write-Host "  git push origin main --force" -ForegroundColor White
        }
    } else {
        Write-Host ""
        Write-Host "Pull gagal. Ada conflict yang perlu diselesaikan manual." -ForegroundColor Red
    }
} else {
    Write-Host ""
    Write-Host "OK, silakan jalankan manual." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
