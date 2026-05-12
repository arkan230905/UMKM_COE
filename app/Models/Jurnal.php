<?php

namespace App\Models;

class Jurnal extends JurnalUmum
{
    // Model ini menggunakan tabel yang sama dengan JurnalUmum
    // tapi dengan nama class yang lebih singkat untuk backward compatibility
    protected $table = 'jurnal_umum';
}
