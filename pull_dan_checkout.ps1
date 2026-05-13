# PULL DAN CHECKOUT KE BRANCH ARKANABIYYU

Write-Host "=== PULL & CHECKOUT ===" -ForegroundColor Cyan
Write-Host ""

# Kill vim
Get-Process | Where-Object {$_.Name -like "*vim*" -or $_.Name -like "*vi*"} | Stop-Process -Force -ErrorAction SilentlyContinue

# Remove merge files
Remove-Item -Path ".git/MERGE_HEAD",".git/MERGE_MODE",".git/MERGE_MSG",".git/index.lock" -Force -ErrorAction SilentlyContinue

# Reset
Write-Host "Resetting..." -ForegroundColor Yellow
git reset --hard HEAD

# Pull with rebase
Write-Host "Pulling latest changes..." -ForegroundColor Yellow
git pull origin main --rebase

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Pull berhasil!" -ForegroundColor Green
    Write-Host ""
    
    # Check if branch exists
    Write-Host "Checking branch arkanabiyyu..." -ForegroundColor Yellow
    $branchExists = git branch --list arkanabiyyu
    
    if ($branchExists) {
        Write-Host "Branch arkanabiyyu sudah ada, checkout..." -ForegroundColor Yellow
        git checkout arkanabiyyu
    } else {
        Write-Host "Branch arkanabiyyu belum ada, membuat baru..." -ForegroundColor Yellow
        git checkout -b arkanabiyyu
    }
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "✅ SUKSES!" -ForegroundColor Green
        Write-Host "Anda sekarang di branch: arkanabiyyu" -ForegroundColor Green
        Write-Host ""
        git status
    } else {
        Write-Host "❌ Checkout gagal" -ForegroundColor Red
    }
} else {
    Write-Host "❌ Pull gagal" -ForegroundColor Red
}

Write-Host ""
