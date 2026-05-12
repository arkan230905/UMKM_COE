#!/bin/bash

# Start containers
docker-compose up -d --build

# Install PHP dependencies
docker-compose exec app composer install

# Generate application key
docker-compose exec app php artisan key:generate

# Run database migrations
docker-compose exec app php artisan migrate --seed

# Install NPM dependencies
docker-compose exec app npm install
docker-compose exec app npm run dev

echo "\nAplikasi berjalan di http://localhost:8000"
