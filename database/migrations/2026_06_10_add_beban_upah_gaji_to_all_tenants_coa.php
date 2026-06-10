<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // Ambil semua company_id dari tabel coas yang unique
            $companies = DB::table('coas')
                ->select('company_id')
                ->distinct()
                ->whereNotNull('company_id')
                ->pluck('company_id')
                ->toArray();

            // Jika tidak ada company_id di coas, ambil dari perusahaan table
            if (empty($companies)) {
                $companies = DB::table('perusahaan')
                    ->select('id')
                    ->pluck('id')
                    ->toArray();
            }

            // Ambil juga dari users yang memiliki coas
            $userIds = DB::table('coas')
                ->select('user_id')
                ->distinct()
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->toArray();

            // Daftar akun yang perlu ditambahkan
            $akunsToAdd = [
                [
                    'kode_akun' => '511',
                    'nama_akun' => 'Beban Upah Gaji',
                    'tipe_akun' => 'Beban',
                    'saldo_normal' => 'debit',
                ],
                [
                    'kode_akun' => '512',
                    'nama_akun' => 'Beban Tunjangan',
                    'tipe_akun' => 'Beban',
                    'saldo_normal' => 'debit',
                ],
                [
                    'kode_akun' => '513',
                    'nama_akun' => 'Beban Asuransi Tenaga Kerja',
                    'tipe_akun' => 'Beban',
                    'saldo_normal' => 'debit',
                ],
                [
                    'kode_akun' => '211',
                    'nama_akun' => 'Utang Gaji',
                    'tipe_akun' => 'Kewajiban',
                    'saldo_normal' => 'kredit',
                ],
            ];

            // Jika ada company_id, proses per company
            if (!empty($companies)) {
                foreach ($companies as $companyId) {
                    foreach ($akunsToAdd as $akun) {
                        // Cek apakah akun sudah ada untuk company ini
                        $exists = DB::table('coas')
                            ->where('company_id', $companyId)
                            ->where('kode_akun', $akun['kode_akun'])
                            ->exists();

                        if (!$exists) {
                            DB::table('coas')->insert([
                                'company_id' => $companyId,
                                'user_id' => null,
                                'kode_akun' => $akun['kode_akun'],
                                'nama_akun' => $akun['nama_akun'],
                                'tipe_akun' => $akun['tipe_akun'],
                                'kategori_akun' => null,
                                'is_akun_header' => false,
                                'kode_induk' => null,
                                'saldo_normal' => $akun['saldo_normal'],
                                'saldo_awal' => 0,
                                'tanggal_saldo_awal' => null,
                                'posted_saldo_awal' => false,
                                'keterangan' => null,
                                'nomor_rekening' => null,
                                'atas_nama' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            \Log::info("Added COA {$akun['kode_akun']} ({$akun['nama_akun']}) to company_id: {$companyId}");
                        }
                    }
                }
            }

            // Proses juga per user_id jika ada (untuk backward compatibility dengan sistem multi-tenant berbasis user)
            if (!empty($userIds)) {
                foreach ($userIds as $userId) {
                    // Cek apakah user ini sudah punya akun-akun tersebut
                    foreach ($akunsToAdd as $akun) {
                        $exists = DB::table('coas')
                            ->where('user_id', $userId)
                            ->where('kode_akun', $akun['kode_akun'])
                            ->whereNull('company_id')
                            ->exists();

                        if (!$exists) {
                            DB::table('coas')->insert([
                                'company_id' => null,
                                'user_id' => $userId,
                                'kode_akun' => $akun['kode_akun'],
                                'nama_akun' => $akun['nama_akun'],
                                'tipe_akun' => $akun['tipe_akun'],
                                'kategori_akun' => null,
                                'is_akun_header' => false,
                                'kode_induk' => null,
                                'saldo_normal' => $akun['saldo_normal'],
                                'saldo_awal' => 0,
                                'tanggal_saldo_awal' => null,
                                'posted_saldo_awal' => false,
                                'keterangan' => null,
                                'nomor_rekening' => null,
                                'atas_nama' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            \Log::info("Added COA {$akun['kode_akun']} ({$akun['nama_akun']}) to user_id: {$userId}");
                        }
                    }
                }
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            // Hapus akun-akun yang ditambahkan
            $kodesToDelete = ['511', '512', '513', '211'];

            foreach ($kodesToDelete as $kode) {
                DB::table('coas')
                    ->where('kode_akun', $kode)
                    ->delete();

                \Log::info("Deleted COA {$kode} from all tenants");
            }
        });
    }
};
