<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mapping Akun COA untuk Jurnal Penggajian
    |--------------------------------------------------------------------------
    |
    | Konfigurasi akun COA yang digunakan untuk pembuatan jurnal otomatis
    | saat proses penggajian diposting ke jurnal umum.
    |
    | Untuk mengubah mapping, update nilai kode_akun sesuai dengan COA yang ada.
    | Sistem akan mencari COA berdasarkan kode_akun yang diset di sini.
    */

    // Beban Gaji BTKL (Borongan Tenaga Kerja Lepas)
    'beban_gaji_btkl' => env('COA_BEBAN_GAJI_BTKL', '512'),

    // Beban Gaji BTKTL (Borongan Tenaga Kerja Tetap Lain)
    'beban_gaji_btklt' => env('COA_BEBAN_GAJI_BTKTL', '511'),

    // Beban Tunjangan (Total: jabatan + transport + konsumsi)
    'beban_tunjangan' => env('COA_BEBAN_TUNJANGAN', '513'),

    // Beban Asuransi / BPJS
    'beban_asuransi' => env('COA_BEBAN_ASURANSI', '514'),

    // Utang Gaji (jika penggajian disetujui tapi belum dibayar)
    'utang_gaji' => env('COA_UTANG_GAJI', '211'),

    // Kas/Bank (jika penggajian langsung dibayar)
    // Kosongkan null untuk menggunakan kas_bank dari penggajian record
    'kas_bank_default' => env('COA_KAS_BANK_DEFAULT', '111'),

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Jurnal
    |--------------------------------------------------------------------------
    */
    
    // Prefix nomor bukti jurnal penggajian
    'prefix_no_bukti' => 'PGJ',

    // Satu jurnal per pegawai (true) atau satu jurnal per periode (false)
    'one_journal_per_employee' => true,

    // Auto-posting saat penggajian disimpan (true) atau manual (false)
    'auto_post_on_save' => false,
];
