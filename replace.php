<?php
$f = 'app/Http/Controllers/PenggajianController.php';
$c = file_get_contents($f);
$c = str_replace('\App\Models\Jabatan', '\App\Models\Kualifikasi', $c);
$c = str_replace("['jabatanRelasi', 'kualifikasiRelasi']", "'kualifikasiRelasi'", $c);
$c = str_replace("['kualifikasiRelasi', 'jabatanRelasi']", "'kualifikasiRelasi'", $c);
$c = str_replace("'jabatanRelasi'", "'kualifikasiRelasi'", $c);
$c = str_replace('resolvePegawaiJabatan', 'resolvePegawaiKualifikasi', $c);
file_put_contents($f, $c);
echo 'OK';
