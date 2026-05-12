#!/bin/bash

echo "=== RESTORING GOOD DESIGN FROM COMMIT 96c240b ==="
echo "Keeping: Controllers, Models, Migrations (multi-tenant security)"
echo "Restoring: Views, CSS, JS (good design)"
echo ""

# Restore VIEW files only (desain bagus)
echo "Restoring view files..."
git checkout 96c240b -- resources/views/laporan/pembelian/index.blade.php
git checkout 96c240b -- resources/views/laporan/kas-bank/index.blade.php
git checkout 96c240b -- resources/views/laporan/penjualan/index.blade.php
git checkout 96c240b -- resources/views/pegawai/dashboard.blade.php
git checkout 96c240b -- resources/views/auth/register.blade.php

# Restore CSS (desain bagus)
echo "Restoring CSS files..."
git checkout 96c240b -- public/css/modern-dashboard.css

echo ""
echo "✅ DONE! Good design restored while keeping multi-tenant security."
echo ""
echo "Files restored:"
echo "  - resources/views/laporan/pembelian/index.blade.php"
echo "  - resources/views/laporan/kas-bank/index.blade.php"
echo "  - resources/views/laporan/penjualan/index.blade.php"
echo "  - resources/views/pegawai/dashboard.blade.php"
echo "  - resources/views/auth/register.blade.php"
echo "  - public/css/modern-dashboard.css"
echo ""
echo "Controllers and Models: UNCHANGED (multi-tenant security preserved)"
