# Start containers
Write-Host "Starting Docker containers..." -ForegroundColor Cyan
docker-compose up -d --build

# Install PHP dependencies
Write-Host "Installing PHP dependencies..." -ForegroundColor Cyan
docker-compose exec app composer install

# Generate application key
Write-Host "Generating application key..." -ForegroundColor Cyan
docker-compose exec app php artisan key:generate

# Run database migrations
Write-Host "Running database migrations..." -ForegroundColor Cyan
docker-compose exec app php artisan migrate --seed

# Install NPM dependencies
Write-Host "Installing NPM dependencies..." -ForegroundColor Cyan
docker-compose exec app npm install

# Build assets
Write-Host "Building assets..." -ForegroundColor Cyan
docker-compose exec app npm run dev

Write-Host "`nAplikasi berjalan di http://localhost:8000" -ForegroundColor Green
Write-Host "Gunakan Ctrl+C untuk menghentikan" -ForegroundColor Yellow

# Keep the window open
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
