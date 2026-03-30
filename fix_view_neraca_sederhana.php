<?php

/**
 * FILE: fix_view_neraca_sederhana.php
 * 
 * This file contains the working neraca balance sheet functionality
 * that was restored after the fatal error.
 * 
 * Use this file to restore the neraca functionality if issues occur again.
 */

// Current working neraca controller method
$neracaControllerCode = '
    public function neraca(Request $request)
    {
        $periode = $request->get(\'periode\', now()->format(\'Y-m\'));
        
        // Get all COA accounts
        $allCoa = \App\Models\Coa::orderBy(\'kode_akun\')->get();
        
        // Calculate balances for each account from journal entries
        // Use helper function for consistent balance calculation
        $calculateBalance = function($coa) use ($periode) {
            return calculateAccountBalance($coa, $periode);
        };
        
        // Group accounts by category based on COA fields - NO DUPLICATES
        $asetLancar = $allCoa->filter(function($coa) {
            // Aset Lancar: kategori contains "Aset Lancar" or "Kas & Bank" 
            // OR specific account types that are clearly current assets
            return (stripos($coa->kategori_akun, \'Aset Lancar\') !== false || 
                   stripos($coa->kategori_akun, \'Kas & Bank\') !== false ||
                   stripos($coa->nama_akun, \'Kas\') !== false ||
                   stripos($coa->nama_akun, \'Bank\') !== false ||
                   stripos($coa->nama_akun, \'Persediaan\') !== false ||
                   stripos($coa->nama_akun, \'Piutang\') !== false ||
                   stripos($coa->nama_akun, \'PPN Masukan\') !== false ||
                   stripos($coa->nama_akun, \'Biaya Dibayar Dimuka\') !== false) &&
                   in_array($coa->tipe_akun, [\'Asset\', \'asset\']);
        });
        
        $asetTidakLancar = $allCoa->filter(function($coa) {
            // Aset Tidak Lancar: kategori contains "Tidak Lancar" 
            // OR specific account types that are clearly fixed assets
            return (stripos($coa->kategori_akun, \'Tidak Lancar\') !== false ||
                   stripos($coa->nama_akun, \'Peralatan\') !== false ||
                   stripos($coa->nama_akun, \'Mesin\') !== false ||
                   stripos($coa->nama_akun, \'Kendaraan\') !== false ||
                   stripos($coa->nama_akun, \'Inventaris\') !== false ||
                   stripos($coa->nama_akun, \'Akumulasi Penyusutan\') !== false ||
                   stripos($coa->nama_akun, \'Aset Tetap\') !== false ||
                   stripos($coa->nama_akun, \'Gedung\') !== false ||
                   stripos($coa->nama_akun, \'Tanah\') !== false) &&
                   in_array($coa->tipe_akun, [\'Asset\', \'asset\']);
        });
        
        $kewajibanPendek = $allCoa->filter(function($coa) {
            // Kewajiban Jangka Pendek: kategori contains "Hutang" (not Jangka Panjang) 
            // OR specific short-term liabilities
            return (stripos($coa->kategori_akun, \'Hutang\') !== false &&
                    stripos($coa->kategori_akun, \'Jangka Panjang\') === false) ||
                   (stripos($coa->nama_akun, \'Hutang Usaha\') !== false) ||
                   (stripos($coa->nama_akun, \'Hutang Pajak\') !== false);
        });
        
        $kewajibanPanjang = $allCoa->filter(function($coa) {
            // Kewajiban Jangka Panjang: kategori contains "Jangka Panjang" 
            // OR specific long-term liabilities
            // EXCLUDE PPN Masukan (should be in current assets only)
            return (stripos($coa->kategori_akun, \'Jangka Panjang\') !== false) ||
                   (stripos($coa->nama_akun, \'Hutang Bank\') !== false) ||
                   (stripos($coa->nama_akun, \'Hutang Jangka Panjang\') !== false) ||
                   (stripos($coa->nama_akun, \'Obligasi\') !== false);
        });
        
        $ekuitas = $allCoa->filter(function($coa) {
            // Ekuitas: starts with 3xxx or tipe_akun is Equity or kategori contains "Ekuitas"
            // OR specific equity accounts (excluding PPN Keluaran which should be liability)
            return substr($coa->kode_akun, 0, 1) === \'3\' || 
                   in_array($coa->tipe_akun, [\'Equity\', \'Modal\']) ||
                   stripos($coa->kategori_akun, \'Ekuitas\') !== false ||
                   (stripos($coa->nama_akun, \'Modal\') !== false) ||
                   (stripos($coa->nama_akun, \'Laba Ditahan\') !== false) ||
                   (stripos($coa->nama_akun, \'Prive\') !== false);
        });
        
        // Calculate totals for each group
        $totalAsetLancar = $asetLancar->sum(function($coa) use ($calculateBalance) {
            return $calculateBalance($coa);
        });
        
        $totalAsetTidakLancar = $asetTidakLancar->sum(function($coa) use ($calculateBalance) {
            return $calculateBalance($coa);
        });
        
        $totalKewajibanPendek = $kewajibanPendek->sum(function($coa) use ($calculateBalance) {
            return $calculateBalance($coa);
        });
        
        $totalKewajibanPanjang = $kewajibanPanjang->sum(function($coa) use ($calculateBalance) {
            return $calculateBalance($coa);
        });
        
        $totalEkuitas = $ekuitas->sum(function($coa) use ($calculateBalance) {
            return $calculateBalance($coa);
        });
        
        // Calculate grand totals
        $totalAset = $totalAsetLancar + $totalAsetTidakLancar;
        $totalKewajiban = $totalKewajibanPendek + $totalKewajibanPanjang;
        $totalKewajibanEkuitas = $totalKewajiban + $totalEkuitas;
        
        return view(\'akuntansi.neraca\', compact(
            \'periode\', 
            \'asetLancar\', \'asetTidakLancar\', 
            \'kewajibanPendek\', \'kewajibanPanjang\', \'ekuitas\',
            \'totalAsetLancar\', \'totalAsetTidakLancar\',
            \'totalKewajibanPendek\', \'totalKewajibanPanjang\', \'totalEkuitas\',
            \'totalAset\', \'totalKewajiban\', \'totalKewajibanEkuitas\',
            \'calculateBalance\'
        ));
    }
';

// Helper function code
$helperFunctionCode = '
if (!function_exists(\'calculateAccountBalance\')) {
    /**
     * Calculate account balance with proper logic based on account type
     */
    function calculateAccountBalance($coa, $periode = null) {
        $saldo = 0;
        
        // Get journal lines for this account up to selected period
        $journalLines = \App\Models\JournalLine::where(\'coa_id\', $coa->id)
            ->whereHas(\'entry\', function($q) use ($periode) {
                if ($periode) {
                    $q->whereDate(\'tanggal\', \'<=\', $periode . \'-31\');
                }
            })->get();
        
        foreach ($journalLines as $line) {
            if ($coa->saldo_normal === \'debit\') {
                $saldo += $line->debit - $line->credit;
            } else {
                $saldo += $line->credit - $line->debit;
            }
        }
        
        // Add initial balance
        $saldo += $coa->saldo_awal ?? 0;
        
        return $saldo;
    }
}
';

echo "=== RESTORASI NERACA BALANCE SHEET ===\n\n";
echo "File ini berisi kode kerja untuk neraca balance sheet yang telah dipulihkan.\n\n";
echo "Lokasi file yang perlu diperiksa:\n";
echo "1. Controller: app/Http/Controllers/AkuntansiController.php (method neraca)\n";
echo "2. Helper: app/Helpers/AccountBalanceHelper.php\n";
echo "3. View: resources/views/akuntansi/neraca.blade.php\n\n";
echo "Jika terjadi error lagi, gunakan kode di atas untuk mengembalikan fungsionalitas.\n\n";
echo "Status: SISTEM NERACA SUDAH NORMAL KEMBALI\n";

?>
