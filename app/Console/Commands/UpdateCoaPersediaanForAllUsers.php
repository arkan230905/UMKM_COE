<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class UpdateCoaPersediaanForAllUsers extends Command
{
    protected $signature = 'coa:update-persediaan-all';
    protected $description = 'Update coa_persediaan_id for all bahan baku and bahan pendukung across all users';

    public function handle()
    {
        $this->info('Starting COA Persediaan update for all users...');
        $this->newLine();

        $totalBBUpdated = 0;
        $totalBPUpdated = 0;

        // Get all unique user_ids
        $userIds = BahanBaku::select('user_id')->distinct()->pluck('user_id');

        foreach ($userIds as $userId) {
            $this->info("Processing user_id: {$userId}");

            // Update Bahan Baku
            $bbCount = $this->updateBahanBakuForUser($userId);
            $totalBBUpdated += $bbCount;
            $this->line("  ✓ Updated {$bbCount} bahan baku");

            // Update Bahan Pendukung
            $bpCount = $this->updateBahanPendukungForUser($userId);
            $totalBPUpdated += $bpCount;
            $this->line("  ✓ Updated {$bpCount} bahan pendukung");
        }

        $this->newLine();
        $this->info("✅ Complete!");
        $this->info("Total Bahan Baku updated: {$totalBBUpdated}");
        $this->info("Total Bahan Pendukung updated: {$totalBPUpdated}");

        return Command::SUCCESS;
    }

    private function updateBahanBakuForUser(int $userId): int
    {
        $updated = 0;

        // Ayam Potong
        $count = BahanBaku::where('user_id', $userId)
            ->where(function($q) {
                $q->whereRaw('LOWER(nama_bahan) LIKE ?', ['%ayam potong%'])
                  ->orWhere('coa_persediaan_id', null);
            })
            ->whereRaw('LOWER(nama_bahan) LIKE ?', ['%ayam potong%'])
            ->update(['coa_persediaan_id' => $this->findCoaKode($userId, '1141', '114')]);
        $updated += $count;

        // Ayam Kampung
        $count = BahanBaku::where('user_id', $userId)
            ->where(function($q) {
                $q->whereRaw('LOWER(nama_bahan) LIKE ?', ['%ayam kampung%'])
                  ->orWhere('coa_persediaan_id', null);
            })
            ->whereRaw('LOWER(nama_bahan) LIKE ?', ['%ayam kampung%'])
            ->update(['coa_persediaan_id' => $this->findCoaKode($userId, '1142', '114')]);
        $updated += $count;

        // Bebek
        $count = BahanBaku::where('user_id', $userId)
            ->where(function($q) {
                $q->whereRaw('LOWER(nama_bahan) LIKE ?', ['%bebek%'])
                  ->orWhere('coa_persediaan_id', null);
            })
            ->whereRaw('LOWER(nama_bahan) LIKE ?', ['%bebek%'])
            ->update(['coa_persediaan_id' => $this->findCoaKode($userId, '1143', '114')]);
        $updated += $count;

        // Ayam lainnya
        $count = BahanBaku::where('user_id', $userId)
            ->where(function($q) {
                $q->whereRaw('LOWER(nama_bahan) LIKE ?', ['%ayam%'])
                  ->whereRaw('LOWER(nama_bahan) NOT LIKE ?', ['%ayam potong%'])
                  ->whereRaw('LOWER(nama_bahan) NOT LIKE ?', ['%ayam kampung%']);
            })
            ->where(function($q) {
                $q->whereNull('coa_persediaan_id')
                  ->orWhere('coa_persediaan_id', '114');
            })
            ->update(['coa_persediaan_id' => $this->findCoaKode($userId, '1144', '114')]);
        $updated += $count;

        // Default untuk yang masih NULL
        $count = BahanBaku::where('user_id', $userId)
            ->whereNull('coa_persediaan_id')
            ->update(['coa_persediaan_id' => $this->findCoaKode($userId, '114', '114')]);
        $updated += $count;

        return $updated;
    }

    private function updateBahanPendukungForUser(int $userId): int
    {
        $updated = 0;

        $mappings = [
            ['pattern' => '%air%', 'coa' => '1150'],
            ['pattern' => '%minyak%', 'coa' => '1151'],
            ['pattern' => '%tepung terigu%', 'coa' => '1152'],
            ['pattern' => '%maizena%', 'coa' => '1153'],
            ['pattern' => '%lada%', 'coa' => '1154'],
            ['pattern' => '%kaldu%', 'coa' => '1155'],
            ['pattern' => '%bawang putih%', 'coa' => '1156'],
            ['pattern' => '%kemasan%', 'coa' => '1157'],
        ];

        foreach ($mappings as $mapping) {
            $count = BahanPendukung::where('user_id', $userId)
                ->whereRaw('LOWER(nama_bahan) LIKE ?', [$mapping['pattern']])
                ->where(function($q) {
                    $q->whereNull('coa_persediaan_id')
                      ->orWhere('coa_persediaan_id', '115');
                })
                ->update(['coa_persediaan_id' => $this->findCoaKode($userId, $mapping['coa'], '115')]);
            $updated += $count;
        }

        // Default untuk yang masih NULL
        $count = BahanPendukung::where('user_id', $userId)
            ->whereNull('coa_persediaan_id')
            ->update(['coa_persediaan_id' => $this->findCoaKode($userId, '115', '115')]);
        $updated += $count;

        return $updated;
    }

    private function findCoaKode(int $userId, string $preferredKode, string $fallbackKode): string
    {
        // Try preferred COA
        $coa = Coa::where('user_id', $userId)
            ->where('kode_akun', $preferredKode)
            ->first();

        if ($coa) {
            return $preferredKode;
        }

        // Fallback to generic COA
        $coa = Coa::where('user_id', $userId)
            ->where('kode_akun', $fallbackKode)
            ->first();

        return $coa ? $fallbackKode : $preferredKode;
    }
}
