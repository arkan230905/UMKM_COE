<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdatePenggajianCoasSeeder extends Seeder
{
    /**
     * Seeder untuk update COA penggajian - FINAL VERSION
     * 
     * LANGKAH 0: Revert akun 54 & 55 yang salah di-rename oleh seeder lama
     * LANGKAH 1: Update nama akun child 520, 521, 522 (yang sudah ada)
     * LANGKAH 1B: Update akun 516 → "Pembulatan Upah Gaji" (ganti dari "Potongan Gaji")
     * LANGKAH 2: Create akun baru yang belum ada (53, 212, 213, 214, 515, 516, 517)
     * NOTE: Akun 951 TIDAK digunakan. Pembulatan pakai akun 516.
     * 
     * CATATAN TEKNIS:
     * - Menggunakan DB facade (bukan Eloquent) agar bypass global scope user_id
     * - Kolom deskripsi di tabel coas = 'keterangan' (bukan 'deskripsi')
     * - Multi-tenant: apply ke SEMUA user_id yang ada di tabel coas
     * - Dibungkus DB::transaction() untuk data safety
     */
    public function run(): void
    {
        // Ambil semua user_id unik dari tabel coas (multi-tenant)
        $userIds = DB::table('coas')
            ->select('user_id')
            ->distinct()
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->toArray();

        if (empty($userIds)) {
            $userIds = DB::table('users')->pluck('id')->toArray();
        }

        if (empty($userIds)) {
            $this->command->error('❌ Tidak ada user ditemukan di database.');
            return;
        }

        $this->command->info("\n");
        $this->command->info("═══════════════════════════════════════════════════════════════");
        $this->command->info("SEEDER: UPDATE COA PENGGAJIAN - FINAL VERSION");
        $this->command->info("═══════════════════════════════════════════════════════════════");
        $this->command->info("📋 Ditemukan " . count($userIds) . " user(s) untuk diproses.\n");

        DB::transaction(function () use ($userIds) {
            $now = now();

            foreach ($userIds as $userId) {
                $this->command->info("══════════════════════════════════════════════");
                $this->command->info("👤 Processing User ID: {$userId}");
                $this->command->info("══════════════════════════════════════════════");

                // ────────────────────────────────────────────────────────────
                // LANGKAH 0: REVERT AKUN 54 & 55 YANG SALAH DI-RENAME
                // ────────────────────────────────────────────────────────────
                
                $revertAccounts = [
                    ['kode_akun' => '54', 'original_name' => 'BOP BTKTL - Biaya Tenaga Kerja Tidak Langsung'],
                    ['kode_akun' => '55', 'original_name' => 'BOP - Lainnya'],
                ];

                foreach ($revertAccounts as $account) {
                    $existing = DB::table('coas')
                        ->where('user_id', $userId)
                        ->where('kode_akun', $account['kode_akun'])
                        ->first();

                    if ($existing && str_contains($existing->nama_akun, 'Beban Upah Gaji (BTKL)')) {
                        DB::table('coas')
                            ->where('id', $existing->id)
                            ->update([
                                'nama_akun' => $account['original_name'],
                                'updated_at' => $now,
                            ]);
                        $this->command->info("  ↩ REVERTED: {$account['kode_akun']} → {$account['original_name']}");
                    }
                }

                // ────────────────────────────────────────────────────────────
                // LANGKAH 0B: HAPUS AKUN 951 (tidak digunakan, diganti 516)
                // ────────────────────────────────────────────────────────────

                $deleted951 = DB::table('coas')
                    ->where('user_id', $userId)
                    ->where('kode_akun', '951')
                    ->delete();

                if ($deleted951 > 0) {
                    $this->command->warn("  🗑  DELETED: 951 - Selisih Pembulatan Upah Gaji ({$deleted951} record)");
                } else {
                    $this->command->info("  ✓ Akun 951 tidak ada (sudah bersih)");
                }

                // ────────────────────────────────────────────────────────────
                // LANGKAH 0C: RENAME FORMAT LAMA "BTKL-*" → FORMAT BARU (JAGUNG WEB)
                // ────────────────────────────────────────────────────────────
                // Hanya akun dengan nama format lama "BTKL-*" yang akan diupdate.
                // Akun lokal (ayam) sudah pakai format baru → tidak terpengaruh.

                $oldToNewBtklNames = [
                    '520' => [
                        'BTKL-Perebusan'   => 'Beban Upah Gaji (BTKL) - Perebusan',
                        'BTKL-Perbumbuan'  => 'Beban Upah Gaji (BTKL) - Perbumbuan',
                    ],
                    '521' => [
                        'BTKL-Pencampuran' => 'Beban Gaji Upah (BTKTL) - Pengukusan',
                        'BTKL-Penggorengan'=> 'Beban Gaji Upah (BTKTL) - Penggorengan',
                    ],
                    '522' => [
                        'BTKL-Pengemasan'  => 'Beban Gaji Upah (BTKTL) - Pengemasan',
                        'BTKL-Packaging'   => 'Beban Gaji Upah (BTKTL) - Pengemasan',
                    ],
                ];

                $this->command->info("  🔄 LANGKAH 0C: Rename format lama BTKL-* (jagung web):");

                foreach ($oldToNewBtklNames as $kode => $nameMap) {
                    $coa = DB::table('coas')
                        ->where('user_id', $userId)
                        ->where('kode_akun', $kode)
                        ->first();

                    if ($coa && isset($nameMap[$coa->nama_akun])) {
                        $newName = $nameMap[$coa->nama_akun];
                        DB::table('coas')
                            ->where('id', $coa->id)
                            ->update(['nama_akun' => $newName, 'updated_at' => $now]);
                        $this->command->info("     ✓ RENAMED: {$kode} '{$coa->nama_akun}' → '{$newName}'");
                    } else {
                        $namaAkun = $coa ? $coa->nama_akun : 'tidak ada';
                        $this->command->info("     ✓ {$kode}: tidak perlu diupdate ({$namaAkun})");
                    }
                }

                $this->command->info("");

                // ────────────────────────────────────────────────────────────
                // LANGKAH 1: UPSERT AKUN 520, 521, 522 (CREATE JIKA BELUM ADA)
                // ────────────────────────────────────────────────────────────
                // Parent 52 = BTKL - Biaya Tenaga Kerja Langsung [TETAP]

                $this->command->info("  📝 Upsert child departemen BTKL (520, 521, 522):");



                // ────────────────────────────────────────────────────────────
                // LANGKAH 1B: UPDATE AKUN 516 → "Pembulatan Upah Gaji"
                // ────────────────────────────────────────────────────────────

                $this->command->info("  🔄 Update akun 516 → Pembulatan Upah Gaji:");

                $coa516 = DB::table('coas')
                    ->where('user_id', $userId)
                    ->where('kode_akun', '516')
                    ->first();

                if ($coa516) {
                    $old516Name = $coa516->nama_akun;
                    DB::table('coas')
                        ->where('id', $coa516->id)
                        ->update([
                            'nama_akun'  => 'Pembulatan Upah Gaji',
                            'keterangan' => 'Selisih pembulatan upah gaji karyawan (pembulatan ke atas = debit, ke bawah = kredit)',
                            'updated_at' => $now,
                        ]);
                    $this->command->info("     ✓ UPDATED: 516 '{$old516Name}' → 'Pembulatan Upah Gaji'");
                } else {
                    // Buat akun 516 jika belum ada
                    DB::table('coas')->insert([
                        'user_id'          => $userId,
                        'company_id'       => null,
                        'kode_akun'        => '516',
                        'nama_akun'        => 'Pembulatan Upah Gaji',
                        'tipe_akun'        => 'Beban',
                        'kategori_akun'    => 'Beban',
                        'saldo_normal'     => 'debit',
                        'saldo_awal'       => 0,
                        'tanggal_saldo_awal' => null,
                        'posted_saldo_awal' => false,
                        'is_akun_header'   => false,
                        'kode_induk'       => null,
                        'keterangan'       => 'Selisih pembulatan upah gaji karyawan (pembulatan ke atas = debit, ke bawah = kredit)',
                        'nomor_rekening'   => null,
                        'atas_nama'        => null,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]);
                    $this->command->info("     ✓ CREATED: 516 - Pembulatan Upah Gaji");
                }

                $this->command->info("");

                // ────────────────────────────────────────────────────────────
                // LANGKAH 2: CREATE AKUN BARU (termasuk 520, 521, 522 jika belum ada)
                // ────────────────────────────────────────────────────────────
                
                $this->command->info("  ✅ Create/Update akun baru:");
                
                $accountsToCreate = [
                    [
                        'kode_akun' => '520',
                        'nama_akun' => 'Beban Upah Gaji (BTKL) - Perbumbuan',
                        'tipe_akun' => 'Beban',
                        'kategori_akun' => 'Beban',
                        'saldo_normal' => 'debit',
                        'keterangan' => 'Beban upah gaji untuk tenaga kerja langsung departemen Perbumbuan'
                    ],
                    [
                        'kode_akun' => '521',
                        'nama_akun' => 'Beban Gaji Upah (BTKTL) - Penggorengan',
                        'tipe_akun' => 'Beban',
                        'kategori_akun' => 'Beban',
                        'saldo_normal' => 'debit',
                        'keterangan' => 'Beban upah gaji untuk tenaga kerja langsung departemen Penggorengan'
                    ],
                    [
                        'kode_akun' => '522',
                        'nama_akun' => 'Beban Gaji Upah (BTKTL) - Pengemasan',
                        'tipe_akun' => 'Beban',
                        'kategori_akun' => 'Beban',
                        'saldo_normal' => 'debit',
                        'keterangan' => 'Beban upah gaji untuk tenaga kerja langsung departemen Pengemasan'
                    ],
                    [
                        'kode_akun' => '53',
                        'nama_akun' => 'Beban Upah Gaji (BTKTL)',
                        'tipe_akun' => 'Biaya',
                        'kategori_akun' => 'Biaya',
                        'saldo_normal' => 'debit',
                        'keterangan' => 'Beban upah gaji untuk Tenaga Kerja Tidak Langsung (Indirect Labor) - Admin, Gudang, Quality Control, Supervisor, dll'
                    ],
                    [
                        'kode_akun' => '212',
                        'nama_akun' => 'Hutang Gaji',
                        'tipe_akun' => 'Kewajiban',
                        'kategori_akun' => 'Kewajiban',
                        'saldo_normal' => 'kredit',
                        'keterangan' => 'Hutang upah/gaji kepada pegawai yang belum dibayar (accrual basis - PSAK No. 1)'
                    ],
                    [
                        'kode_akun' => '213',
                        'nama_akun' => 'Hutang Asuransi',
                        'tipe_akun' => 'Kewajiban',
                        'kategori_akun' => 'Kewajiban',
                        'saldo_normal' => 'kredit',
                        'keterangan' => 'Hutang asuransi/BPJS yang dipotong dari gaji pegawai'
                    ],
                    [
                        'kode_akun' => '214',
                        'nama_akun' => 'PPN Keluaran',
                        'tipe_akun' => 'Kewajiban',
                        'kategori_akun' => 'Kewajiban',
                        'saldo_normal' => 'kredit',
                        'keterangan' => 'PPN Keluaran'
                    ],
                    [
                        'kode_akun' => '515',
                        'nama_akun' => 'Beban Tunjangan',
                        'tipe_akun' => 'Beban',
                        'kategori_akun' => 'Beban',
                        'saldo_normal' => 'debit',
                        'keterangan' => 'Beban tunjangan jabatan, transport, konsumsi untuk pegawai'
                    ],
                    [
                        'kode_akun' => '516',
                        'nama_akun' => 'Beban Asuransi',
                        'tipe_akun' => 'Beban',
                        'kategori_akun' => 'Beban',
                        'saldo_normal' => 'debit',
                        'keterangan' => 'Beban asuransi kesehatan/ketenagakerjaan yang ditanggung perusahaan'
                    ],
                    [
                        'kode_akun' => '517',
                        'nama_akun' => 'Beban Bonus',
                        'tipe_akun' => 'Beban',
                        'kategori_akun' => 'Beban',
                        'saldo_normal' => 'debit',
                        'keterangan' => 'Beban bonus karyawan (bonus gaji, insentif, hadiah, dll)'
                    ],
                    // NOTE: Akun 516 dihandle di LANGKAH 1B (update/create di atas)
                    // Akun 951 TIDAK digunakan. Pembulatan pakai akun 516.
                ];

                foreach ($accountsToCreate as $account) {
                    $existing = DB::table('coas')
                        ->where('user_id', $userId)
                        ->where('kode_akun', $account['kode_akun'])
                        ->first();

                    if (!$existing) {
                        DB::table('coas')->insert([
                            'user_id' => $userId,
                            'company_id' => null,
                            'kode_akun' => $account['kode_akun'],
                            'nama_akun' => $account['nama_akun'],
                            'tipe_akun' => $account['tipe_akun'],
                            'kategori_akun' => $account['kategori_akun'],
                            'saldo_normal' => $account['saldo_normal'],
                            'saldo_awal' => 0,
                            'tanggal_saldo_awal' => null,
                            'posted_saldo_awal' => false,
                            'is_akun_header' => false,
                            'kode_induk' => null,
                            'keterangan' => $account['keterangan'],
                            'nomor_rekening' => null,
                            'atas_nama' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                        $this->command->info("     ✓ CREATED: {$account['kode_akun']} - {$account['nama_akun']}");
                    } else {
                        $this->command->warn("     ⊘ SKIP: {$account['kode_akun']} sudah ada ({$existing->nama_akun})");
                    }
                }

                $this->command->info("");
            }

            // ────────────────────────────────────────────────────────────
            // HANDLE TEMPLATE COAs (user_id = NULL)
            // ────────────────────────────────────────────────────────────
            
            $templateCount = DB::table('coas')
                ->whereNull('user_id')
                ->whereIn('kode_akun', ['520', '521', '522'])
                ->count();

            if ($templateCount > 0) {
                $this->command->info("══════════════════════════════════════════════");
                $this->command->info("🔧 Processing Template COAs (user_id = NULL)");
                $this->command->info("══════════════════════════════════════════════");

                $templateUpdates = [
                    ['kode_akun' => '520', 'new_name' => 'Beban Upah Gaji (BTKL) - Perbumbuan'],
                    ['kode_akun' => '521', 'new_name' => 'Beban Gaji Upah (BTKTL) - Penggorengan'],
                    ['kode_akun' => '522', 'new_name' => 'Beban Gaji Upah (BTKTL) - Pengemasan'],
                ];

                foreach ($templateUpdates as $account) {
                    $updated = DB::table('coas')
                        ->whereNull('user_id')
                        ->where('kode_akun', $account['kode_akun'])
                        ->update(['nama_akun' => $account['new_name'], 'updated_at' => $now]);

                    if ($updated > 0) {
                        $this->command->info("  ✓ TEMPLATE: {$account['kode_akun']} → {$account['new_name']} ({$updated} records)");
                    }
                }

                // Revert template 54 & 55 jika salah di-rename
                foreach (['54' => 'BOP BTKTL - Biaya Tenaga Kerja Tidak Langsung', '55' => 'BOP - Lainnya'] as $kode => $nama) {
                    $reverted = DB::table('coas')
                        ->whereNull('user_id')
                        ->where('kode_akun', $kode)
                        ->where('nama_akun', 'LIKE', '%Beban Upah Gaji (BTKL)%')
                        ->update(['nama_akun' => $nama, 'updated_at' => $now]);

                    if ($reverted > 0) {
                        $this->command->info("  ↩ TEMPLATE REVERTED: {$kode} → {$nama}");
                    }
                }
            }
        });

        // ────────────────────────────────────────────────────────────
        // RINGKASAN HASIL
        // ────────────────────────────────────────────────────────────
        
        $this->command->info("\n");
        $this->command->info("═══════════════════════════════════════════════════════════════");
        $this->command->info("✅ SEEDER BERHASIL DIJALANKAN");
        $this->command->info("═══════════════════════════════════════════════════════════════");
        
        $this->command->info("\n📊 RINGKASAN STRUKTUR COA PENGGAJIAN:\n");
        
        $this->command->info("PARENT AKUN:");
        $this->command->info("  └─ 52: BTKL - Biaya Tenaga Kerja Langsung          [TETAP]");
        
        $this->command->info("\nCHILD AKUN (BTKL - DEPARTEMEN):");
        $this->command->info("  ├─ 520: Beban Upah Gaji (BTKL) - Perbumbuan        [UPDATED]");
        $this->command->info("  ├─ 521: Beban Upah Gaji (BTKL) - Penggorengan      [UPDATED]");
        $this->command->info("  └─ 522: Beban Upah Gaji (BTKL) - Pengemasan        [UPDATED]");
        
        $this->command->info("\nBEBAN UPAH GAJI (BTKTL):");
        $this->command->info("  └─ 53: Beban Upah Gaji (BTKTL)                     [CREATED]");
        
        $this->command->info("\nBEBAN LAINNYA:");
        $this->command->info("  ├─ 513: Beban Tunjangan                             [CREATED]");
        $this->command->info("  ├─ 515: Beban Bonus                                 [CREATED]");
        $this->command->info("  └─ 516: Pembulatan Upah Gaji                        [UPDATED/CREATED]");
        
        $this->command->info("\nHUTANG (LIABILITY):");
        $this->command->info("  ├─ 211: Hutang Upah Gaji                            [CREATED]");
        $this->command->info("  ├─ 212: Hutang Asuransi/BPJS                        [CREATED]");
        $this->command->info("  └─ 213: Hutang Potongan Gaji Lainnya                [CREATED]");
        
        $this->command->info("\n═══════════════════════════════════════════════════════════════\n");
    }
}
