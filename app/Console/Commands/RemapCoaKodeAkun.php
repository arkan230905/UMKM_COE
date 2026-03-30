<?php

namespace App\Console\Commands;

use App\Models\Coa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemapCoaKodeAkun extends Command
{
    protected $signature = 'coa:remap-kode
        {--apply : Terapkan perubahan ke database}
        {--dry-run : Tampilkan mapping tanpa mengubah database}
        {--limit= : Batasi jumlah akun untuk diuji (opsional)}
        {--path= : Simpan mapping ke file CSV (opsional)}';

    protected $description = 'Remap kode_akun COA ke format sederhana 1xxx-5xxx dan update referensi kode_akun di tabel lain secara aman.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $dryRun = (bool) $this->option('dry-run');

        if (!$apply && !$dryRun) {
            $dryRun = true;
        }

        $limit = $this->option('limit');
        $path = $this->option('path');

        $typeToGroup = [
            'Asset' => 1,
            'Aset' => 1,
            'Liability' => 2,
            'Kewajiban' => 2,
            'Equity' => 3,
            'Ekuitas' => 3,
            'Revenue' => 4,
            'Pendapatan' => 4,
            'Expense' => 5,
            'Beban' => 5,
        ];

        $baseStart = [
            1 => 1101,
            2 => 2101,
            3 => 3101,
            4 => 4101,
            5 => 5101,
        ];

        $coasQuery = Coa::query()
            ->select(['id', 'kode_akun', 'nama_akun', 'tipe_akun'])
            ->orderByRaw('CAST(kode_akun AS UNSIGNED) ASC, kode_akun ASC');

        if ($limit) {
            $coasQuery->limit((int) $limit);
        }

        $coas = $coasQuery->get();

        if ($coas->isEmpty()) {
            $this->warn('Tidak ada data COA.');
            return self::SUCCESS;
        }

        $existingCodes = Coa::query()->pluck('kode_akun')->filter()->all();
        $existingSet = array_fill_keys($existingCodes, true);

        $nextByGroup = [];
        foreach ($baseStart as $g => $start) {
            $nextByGroup[$g] = $start;
        }

        $mapping = [];

        foreach ($coas as $coa) {
            $group = $typeToGroup[$coa->tipe_akun] ?? 1;

            $old = (string) $coa->kode_akun;

            if (strlen($old) === 4 && ctype_digit($old) && (int) $old >= ($group * 1000) && (int) $old < (($group + 1) * 1000)) {
                $mapping[] = [
                    'id' => $coa->id,
                    'old' => $old,
                    'new' => $old,
                    'tipe' => $coa->tipe_akun,
                    'nama' => $coa->nama_akun,
                ];
                continue;
            }

            $candidate = $nextByGroup[$group];
            while (isset($existingSet[(string) $candidate])) {
                $candidate++;
            }

            $new = (string) $candidate;
            $existingSet[$new] = true;
            $nextByGroup[$group] = $candidate + 1;

            $mapping[] = [
                'id' => $coa->id,
                'old' => $old,
                'new' => $new,
                'tipe' => $coa->tipe_akun,
                'nama' => $coa->nama_akun,
            ];
        }

        $this->table(
            ['id', 'kode_lama', 'kode_baru', 'tipe_akun', 'nama_akun'],
            array_map(fn ($m) => [$m['id'], $m['old'], $m['new'], $m['tipe'], $m['nama']], $mapping)
        );

        if ($path) {
            $dir = dirname($path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            $fp = fopen($path, 'w');
            if ($fp) {
                fputcsv($fp, ['id', 'kode_lama', 'kode_baru', 'tipe_akun', 'nama_akun']);
                foreach ($mapping as $m) {
                    fputcsv($fp, [$m['id'], $m['old'], $m['new'], $m['tipe'], $m['nama']]);
                }
                fclose($fp);
                $this->info('Mapping disimpan ke: ' . $path);
            }
        }

        if (!$apply) {
            $this->info('DRY RUN selesai. Jalankan dengan --apply untuk menerapkan perubahan.');
            return self::SUCCESS;
        }

        $this->warn('Mode APPLY: akan mengubah kode_akun dan referensi kode_akun di tabel terkait.');

        // Some legacy tables still have FK constraints referencing coas.kode_akun.
        // To keep this operation safe and atomic, we temporarily disable FK checks (MySQL/MariaDB)
        // and re-enable them after updates are complete.
        DB::beginTransaction();
        $fkDisabled = false;
        try {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                $fkDisabled = true;
            } catch (\Throwable $e) {
                // ignore if not supported by driver
            }

            foreach ($mapping as $m) {
                if ($m['old'] === $m['new']) {
                    continue;
                }
                DB::table('coas')->where('id', $m['id'])->update([
                    'kode_akun' => 'TMP' . $m['id'],
                ]);
            }

            foreach ($mapping as $m) {
                if ($m['old'] === $m['new']) {
                    continue;
                }

                $old = $m['old'];
                $new = $m['new'];

                DB::table('coas')->where('id', $m['id'])->update(['kode_akun' => $new]);

                if (Schema::hasTable('bops') && Schema::hasColumn('bops', 'kode_akun')) {
                    DB::table('bops')->where('kode_akun', $old)->update(['kode_akun' => $new]);
                }

                if (Schema::hasTable('coa_period_balances') && Schema::hasColumn('coa_period_balances', 'kode_akun')) {
                    DB::table('coa_period_balances')->where('kode_akun', $old)->update(['kode_akun' => $new]);
                }

                if (Schema::hasTable('bahan_bakus')) {
                    foreach (['coa_pembelian_id', 'coa_persediaan_id', 'coa_hpp_id'] as $col) {
                        if (Schema::hasColumn('bahan_bakus', $col)) {
                            DB::table('bahan_bakus')->where($col, $old)->update([$col => $new]);
                        }
                    }
                }

                if (Schema::hasTable('bahan_pendukungs')) {
                    foreach (['coa_pembelian_id', 'coa_persediaan_id', 'coa_hpp_id'] as $col) {
                        if (Schema::hasColumn('bahan_pendukungs', $col)) {
                            DB::table('bahan_pendukungs')->where($col, $old)->update([$col => $new]);
                        }
                    }
                }

                foreach ([
                    ['expense_payments', 'coa_kasbank'],
                    ['ap_settlements', 'coa_kasbank'],
                    ['penggajians', 'coa_kasbank'],
                    ['pelunasan_utangs', 'coa_kasbank'],
                ] as $pair) {
                    [$table, $col] = $pair;
                    if (Schema::hasTable($table) && Schema::hasColumn($table, $col)) {
                        DB::table($table)->where($col, $old)->update([$col => $new]);
                    }
                }
            }

            if ($fkDisabled) {
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            if ($fkDisabled) {
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                } catch (\Throwable $ignored) {
                    // ignore
                }
            }
            DB::rollBack();
            throw $e;
        }

        $this->info('Selesai APPLY remap kode_akun.');

        return self::SUCCESS;
    }
}
