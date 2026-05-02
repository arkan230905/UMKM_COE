<?php

$modelsToFix = [
    'KategoriProduk',
    'KategoriBahanPendukung',
    'JournalEntry',
    'ApSettlement',
    'SalesReturn',
    'BomJobCosting',
    'BomJobBahanPendukung',
    'BomJobBOP',
    'BomJobBTKL',
    'BomProses',
    'Bop',
    'BopLainnya',
    'KomponenBop',
];

// Jalankan script tambah_global_scope_otomatis.php dengan list ini
include 'tambah_global_scope_otomatis.php';
