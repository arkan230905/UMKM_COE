<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// ============================================================
// TEMPORARY ROUTE — HAPUS SETELAH DIPAKAI!
// Reset saldo_awal semua COA menjadi 0
// URL: /reset-coa-saldo-awal          → dry-run (lihat jumlah dulu)
// URL: /reset-coa-saldo-awal?confirm=yes → eksekusi reset
// ============================================================
Route::get('/reset-coa-saldo-awal', function () {

    $affected = DB::table('coas')
        ->whereNotNull('saldo_awal')
        ->where('saldo_awal', '!=', 0)
        ->get(['id', 'user_id', 'kode_akun', 'nama_akun', 'saldo_awal']);

    $count = $affected->count();

    // ── EKSEKUSI jika ?confirm=yes ──────────────────────────
    if (request('confirm') === 'yes') {
        $updated = DB::table('coas')
            ->whereNotNull('saldo_awal')
            ->where('saldo_awal', '!=', 0)
            ->update(['saldo_awal' => 0]);

        return response("<h1 style='color:green;font-family:sans-serif'>✅ BERHASIL!</h1>
            <p style='font-family:sans-serif'>Berhasil mereset <strong>{$updated}</strong> baris saldo_awal menjadi 0.</p>
            <p><a href='/master-data/coa' style='background:#28a745;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;font-family:sans-serif'>🔙 Kembali ke COA</a></p>
            <hr><p style='color:red;font-size:12px;font-family:sans-serif'>⚠️ Jangan lupa hapus file <code>routes/web-reset-coa.php</code> dan push ulang!</p>");
    }

    // ── DRY-RUN: Tampilkan daftar yang akan terdampak ───────
    if ($count === 0) {
        return response("<h1 style='color:green;font-family:sans-serif'>✅ Semua saldo_awal sudah 0!</h1>
            <p style='font-family:sans-serif'>Tidak ada data yang perlu direset.</p>
            <p><a href='/master-data/coa' style='background:#007bff;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;font-family:sans-serif'>🔙 Kembali ke COA</a></p>");
    }

    $rows = '';
    foreach ($affected as $coa) {
        $saldo = number_format($coa->saldo_awal, 0, ',', '.');
        $rows .= "<tr>
            <td style='padding:8px;border:1px solid #ddd'>{$coa->user_id}</td>
            <td style='padding:8px;border:1px solid #ddd'>{$coa->kode_akun}</td>
            <td style='padding:8px;border:1px solid #ddd'>{$coa->nama_akun}</td>
            <td style='padding:8px;border:1px solid #ddd;text-align:right'>Rp {$saldo}</td>
        </tr>";
    }

    return response("
        <h2 style='font-family:sans-serif'>🔍 Dry-Run: COA dengan saldo_awal &gt; 0</h2>
        <p style='font-family:sans-serif'>Ditemukan <strong>{$count} baris</strong> yang akan direset ke 0:</p>
        <table style='border-collapse:collapse;width:100%;font-family:sans-serif;font-size:14px'>
            <thead>
                <tr style='background:#f0f0f0'>
                    <th style='padding:8px;border:1px solid #ddd'>User ID</th>
                    <th style='padding:8px;border:1px solid #ddd'>Kode Akun</th>
                    <th style='padding:8px;border:1px solid #ddd'>Nama Akun</th>
                    <th style='padding:8px;border:1px solid #ddd'>Saldo Awal Saat Ini</th>
                </tr>
            </thead>
            <tbody>{$rows}</tbody>
        </table>
        <br>
        <a href='/reset-coa-saldo-awal?confirm=yes'
           style='background:#dc3545;color:white;padding:12px 24px;border-radius:5px;text-decoration:none;font-family:sans-serif;font-size:16px'
           onclick=\"return confirm('Yakin reset SEMUA {$count} baris ini ke 0?')\">
           ⚡ Eksekusi Reset Sekarang
        </a>
        &nbsp;
        <a href='/master-data/coa' style='background:#6c757d;color:white;padding:12px 24px;border-radius:5px;text-decoration:none;font-family:sans-serif;font-size:16px'>
            Batal
        </a>
    ");
});
