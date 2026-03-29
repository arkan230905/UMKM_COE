<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up()
    {
        $now = Carbon::now();
        
        // Check which columns exist in the coas table
        $hasIsAkunHeader = Schema::hasColumn('coas', 'is_akun_header');
        $hasKategoriAkun = Schema::hasColumn('coas', 'kategori_akun');
        $hasKodeInduk = Schema::hasColumn('coas', 'kode_induk');
        $hasSaldoNormal = Schema::hasColumn('coas', 'saldo_normal');
        $hasSaldoAwal = Schema::hasColumn('coas', 'saldo_awal');
        $hasTanggalSaldoAwal = Schema::hasColumn('coas', 'tanggal_saldo_awal');
        $hasPostedSaldoAwal = Schema::hasColumn('coas', 'posted_saldo_awal');
        $hasKeterangan = Schema::hasColumn('coas', 'keterangan');
        
        // Build accounts array based on available columns
        $hppAccounts = [];
        
        // HPP Header
        $headerAccount = [
            'kode_akun' => '1600',
            'nama_akun' => 'Harga Pokok Penjualan',
            'tipe_akun' => 'Expense',
            'created_at' => $now,
            'updated_at' => $now,
        ];
        
        if ($hasKategoriAkun) $headerAccount['kategori_akun'] = 'Harga Pokok Penjualan';
        if ($hasIsAkunHeader) $headerAccount['is_akun_header'] = 1;
        if ($hasKodeInduk) $headerAccount['kode_induk'] = null;
        if ($hasSaldoNormal) $headerAccount['saldo_normal'] = 'debit';
        if ($hasSaldoAwal) $headerAccount['saldo_awal'] = 0.00;
        if ($hasTanggalSaldoAwal) $headerAccount['tanggal_saldo_awal'] = '2026-03-01';
        if ($hasPostedSaldoAwal) $headerAccount['posted_saldo_awal'] = 0;
        if ($hasKeterangan) $headerAccount['keterangan'] = 'Header untuk harga pokok penjualan';
        
        $hppAccounts[] = $headerAccount;
        
        // HPP Detail Accounts
        $detailAccounts = [
            [
                'kode_akun' => '1601',
                'nama_akun' => 'HPP - Bahan Baku',
                'keterangan' => 'Harga pokok penjualan - bahan baku',
            ],
            [
                'kode_akun' => '1602',
                'nama_akun' => 'HPP - BTKL (Biaya Tenaga Kerja Langsung)',
                'keterangan' => 'Harga pokok penjualan - biaya tenaga kerja langsung',
            ],
            [
                'kode_akun' => '1603',
                'nama_akun' => 'HPP - Overhead Pabrik',
                'keterangan' => 'Harga pokok penjualan - overhead pabrik',
            ],
        ];
        
        foreach ($detailAccounts as $detail) {
            $account = [
                'kode_akun' => $detail['kode_akun'],
                'nama_akun' => $detail['nama_akun'],
                'tipe_akun' => 'Expense',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            
            if ($hasKategoriAkun) $account['kategori_akun'] = 'Harga Pokok Penjualan';
            if ($hasIsAkunHeader) $account['is_akun_header'] = 0;
            if ($hasKodeInduk) $account['kode_induk'] = '1600';
            if ($hasSaldoNormal) $account['saldo_normal'] = 'debit';
            if ($hasSaldoAwal) $account['saldo_awal'] = 0.00;
            if ($hasTanggalSaldoAwal) $account['tanggal_saldo_awal'] = '2026-03-01';
            if ($hasPostedSaldoAwal) $account['posted_saldo_awal'] = 0;
            if ($hasKeterangan) $account['keterangan'] = $detail['keterangan'];
            
            $hppAccounts[] = $account;
        }

        // Insert HPP accounts
        DB::table('coas')->insert($hppAccounts);
    }

    public function down()
    {
        // Remove HPP accounts
        DB::table('coas')->whereIn('kode_akun', ['1600', '1601', '1602', '1603'])->delete();
    }
};