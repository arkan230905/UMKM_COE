<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Validator untuk COA Saldo Awal
 * 
 * Jalankan untuk verifikasi keseimbangan dan integritas data:
 * php artisan db:seed --class=CoaSaldoAwalValidator
 */
class CoaSaldoAwalValidator extends Seeder
{
    public function run(): void
    {
        $this->command->info("\n════════════════════════════════════════");
        $this->command->info("  COA Saldo Awal Validation Report");
        $this->command->info("════════════════════════════════════════\n");

        // 1. Check total accounts
        $totalAccounts = DB::table('coas')->count();
        $this->command->info("✓ Total COA Accounts: {$totalAccounts}");

        // 2. Check header accounts
        $headerAccounts = DB::table('coas')->where('is_akun_header', 1)->count();
        $this->command->info("✓ Header Accounts: {$headerAccounts}");

        // 3. Check leaf accounts
        $leafAccounts = DB::table('coas')->where('is_akun_header', 0)->count();
        $this->command->info("✓ Leaf Accounts: {$leafAccounts}");

        $this->command->info("\n────────────────────────────────────────");
        $this->command->info("SALDO AWAL VERIFICATION\n");

        // 4. Check accounts with opening balance
        $accountsWithBalance = DB::table('coas')
            ->where('saldo_awal', '>', 0)
            ->count();
        $this->command->info("✓ Accounts with Saldo Awal: {$accountsWithBalance}");

        // 5. Detailed opening balance
        $this->command->info("\n📊 Opening Balance Details:\n");

        $debitAccounts = DB::table('coas')
            ->where('saldo_normal', 'debit')
            ->where('saldo_awal', '>', 0)
            ->get(['kode_akun', 'nama_akun', 'saldo_awal', 'saldo_normal']);

        if ($debitAccounts->isNotEmpty()) {
            $this->command->info("DEBIT (Aset):");
            $totalDebit = 0;
            foreach ($debitAccounts as $account) {
                $formatted = number_format($account->saldo_awal, 0, ',', '.');
                $this->command->info("  {$account->kode_akun} | {$account->nama_akun} | Rp {$formatted}");
                $totalDebit += $account->saldo_awal;
            }
            $formattedTotal = number_format($totalDebit, 0, ',', '.');
            $this->command->info("  ─────────────────────────────────────────");
            $this->command->info("  Total Debit: Rp {$formattedTotal}\n");
        }

        $creditAccounts = DB::table('coas')
            ->where('saldo_normal', 'kredit')
            ->where('saldo_awal', '>', 0)
            ->get(['kode_akun', 'nama_akun', 'saldo_awal', 'saldo_normal']);

        if ($creditAccounts->isNotEmpty()) {
            $this->command->info("KREDIT (Kewajiban + Modal):");
            $totalKredit = 0;
            foreach ($creditAccounts as $account) {
                $formatted = number_format($account->saldo_awal, 0, ',', '.');
                $this->command->info("  {$account->kode_akun} | {$account->nama_akun} | Rp {$formatted}");
                $totalKredit += $account->saldo_awal;
            }
            $formattedTotal = number_format($totalKredit, 0, ',', '.');
            $this->command->info("  ─────────────────────────────────────────");
            $this->command->info("  Total Kredit: Rp {$formattedTotal}\n");
        }

        // 6. Verify balance
        $this->command->info("────────────────────────────────────────");
        $this->command->info("KESEIMBANGAN (Balance Check)\n");

        $totalDebit = DB::table('coas')
            ->where('saldo_normal', 'debit')
            ->sum('saldo_awal');

        $totalKredit = DB::table('coas')
            ->where('saldo_normal', 'kredit')
            ->sum('saldo_awal');

        $formattedDebit = number_format($totalDebit, 0, ',', '.');
        $formattedKredit = number_format($totalKredit, 0, ',', '.');

        $this->command->info("Total Debit:  Rp {$formattedDebit}");
        $this->command->info("Total Kredit: Rp {$formattedKredit}");

        if ($totalDebit == $totalKredit) {
            $this->command->info("\n✅ BALANCED! (Debit = Kredit)\n");
        } else {
            $difference = abs($totalDebit - $totalKredit);
            $formattedDiff = number_format($difference, 0, ',', '.');
            $this->command->error("\n❌ NOT BALANCED!");
            $this->command->error("Difference: Rp {$formattedDiff}\n");
        }

        // 7. Check hierarchy
        $this->command->info("────────────────────────────────────────");
        $this->command->info("HIERARCHY CHECK\n");

        $hierarchyCheck = [
            '1' => 'Aset',
            '11' => 'Aset Lancar',
            '111' => 'Kas Bank',
            '1111' => 'Bank BRI',
            '2' => 'Kewajiban',
            '21' => 'Kewajiban Jangka Pendek',
            '3' => 'Modal',
            '31' => 'Modal',
            '4' => 'Pendapatan',
            '5' => 'Biaya',
        ];

        $allExists = true;
        foreach ($hierarchyCheck as $code => $name) {
            $exists = DB::table('coas')->where('kode_akun', $code)->exists();
            $status = $exists ? '✓' : '✗';
            $this->command->info("{$status} {$code} - {$name}");
            if (!$exists) {
                $allExists = false;
            }
        }

        if ($allExists) {
            $this->command->info("\n✅ All hierarchy levels exist\n");
        } else {
            $this->command->error("\n❌ Some hierarchy levels missing\n");
        }

        // 8. Check date consistency
        $this->command->info("────────────────────────────────────────");
        $this->command->info("DATE CONSISTENCY\n");

        $accountsWithDate = DB::table('coas')
            ->where('saldo_awal', '>', 0)
            ->whereNotNull('tanggal_saldo_awal')
            ->count();

        $this->command->info("Accounts with tanggal_saldo_awal: {$accountsWithDate}/{$accountsWithBalance}");

        $dates = DB::table('coas')
            ->where('saldo_awal', '>', 0)
            ->distinct('tanggal_saldo_awal')
            ->pluck('tanggal_saldo_awal');

        foreach ($dates as $date) {
            $count = DB::table('coas')
                ->where('tanggal_saldo_awal', $date)
                ->count();
            $this->command->info("  Date: {$date} ({$count} accounts)");
        }

        if ($accountsWithDate == $accountsWithBalance) {
            $this->command->info("\n✅ Date consistency OK\n");
        } else {
            $this->command->warning("\n⚠️  Some accounts missing tanggal_saldo_awal\n");
        }

        // 9. Summary Report
        $this->command->info("════════════════════════════════════════");
        $this->command->info("SUMMARY REPORT\n");

        $this->command->info("✓ Total Accounts: {$totalAccounts}");
        $this->command->info("✓ Accounts with Saldo Awal: {$accountsWithBalance}");
        $this->command->info("✓ Total Aset (Debit): Rp {$formattedDebit}");
        $this->command->info("✓ Total Liab+Modal (Kredit): Rp {$formattedKredit}");
        $this->command->info("✓ Balance Status: " . ($totalDebit == $totalKredit ? "✅ OK" : "❌ MISMATCH"));

        $this->command->info("\n════════════════════════════════════════");
        $this->command->info("  ✅ Validation Complete");
        $this->command->info("════════════════════════════════════════\n");
    }
}
