<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('format_rupiah')) {
    function format_rupiah($angka)
    {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

if (!function_exists('format_number_clean')) {
    /**
     * Format number without unnecessary decimal places
     * Removes ,00 but keeps meaningful decimals like ,50
     */
    function format_number_clean($number, $decimals = 2)
    {
        // If it's a whole number, show without decimals
        if ($number == floor($number)) {
            return number_format($number, 0, ',', '.');
        }
        
        // Format with decimals and remove trailing zeros
        $formatted = number_format($number, $decimals, ',', '.');
        
        // Remove trailing zeros after decimal point
        if (strpos($formatted, ',') !== false) {
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, ',');
        }
        
        return $formatted;
    }
}

if (!function_exists('format_rupiah_clean')) {
    /**
     * Format rupiah without unnecessary decimal places
     */
    function format_rupiah_clean($angka, $decimals = 2)
    {
        return 'Rp ' . format_number_clean($angka, $decimals);
    }
}

if (!function_exists('tanggal_indonesia')) {
    function tanggal_indonesia($tanggal, $tampil_hari = false)
    {
        $nama_hari  = array(
            'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'mat', 'Sabtu'
        );
        $nama_bulan = array(
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        );

        $tahun   = substr($tanggal, 0, 4);
        $bulan   = $nama_bulan[(int) substr($tanggal, 5, 2)];
        $tanggal = substr($tanggal, 8, 2);
        $text    = '';

        if ($tampil_hari) {
            $urutan_hari = date('w', mktime(0, 0, 0, substr($tanggal, 5, 2), $tanggal, $tahun));
            $hari        = $nama_hari[$urutan_hari];
            $text       .= $hari . ', ';
        }

        $text .= $tanggal . ' ' . $bulan . ' ' . $tahun;

        return $text;
    }
}
