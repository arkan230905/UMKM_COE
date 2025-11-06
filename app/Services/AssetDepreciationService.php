<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\JurnalUmum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssetDepreciationService
{
    /**
     * Generate straight-line depreciation schedule per year and post journals.
     */
    public function computeAndPost(Asset $asset): void
    {
        if (empty($asset->umur_ekonomis) || $asset->umur_ekonomis <= 0) {
            throw new \InvalidArgumentException('Umur ekonomis harus lebih dari 0.');
        }
        if (!$asset->expense_coa_id || !$asset->accum_depr_coa_id) {
            throw new \InvalidArgumentException('Akun beban dan akumulasi penyusutan harus diisi.');
        }

        DB::transaction(function () use ($asset) {
            // Clear old schedule and journals for this asset
            AssetDepreciation::where('asset_id', $asset->id)->delete();
            JurnalUmum::where('tipe_referensi', 'asset_depreciation')
                ->where('referensi', 'like', 'asset:' . $asset->id . ':%')
                ->delete();

            $cost = (float)$asset->harga_perolehan;
            $residual = (float)$asset->nilai_sisa;
            $life = (int)$asset->umur_ekonomis;
            $base = max($cost - $residual, 0);
            $perYear = $life > 0 ? round($base / $life, 2) : 0.0;

            $startYear = Carbon::parse($asset->tanggal_perolehan)->year;
            $acc = 0.0; $book = $cost;

            for ($i = 0; $i < $life; $i++) {
                $year = $startYear + $i;
                $depr = min($perYear, max($book - $residual, 0));
                $acc += $depr;
                $book -= $depr;

                AssetDepreciation::create([
                    'asset_id' => $asset->id,
                    'tahun' => $year,
                    'beban_penyusutan' => $depr,
                    'akumulasi_penyusutan' => $acc,
                    'nilai_buku_akhir' => $book,
                ]);

                // Post journal lines (two rows in jurnal_umum)
                $tanggal = Carbon::create($year, 12, 31)->toDateString();
                $referensi = 'asset:' . $asset->id . ':' . $year;
                $memo = 'Penyusutan aset ' . $asset->nama_aset . ' tahun ' . $year;
                $userId = auth()->id();

                // Debit: Beban Penyusutan
                JurnalUmum::create([
                    'coa_id' => $asset->expense_coa_id,
                    'tanggal' => $tanggal,
                    'keterangan' => $memo,
                    'debit' => $depr,
                    'kredit' => 0,
                    'referensi' => $referensi,
                    'tipe_referensi' => 'asset_depreciation',
                    'created_by' => $userId,
                ]);

                // Kredit: Akumulasi Penyusutan
                JurnalUmum::create([
                    'coa_id' => $asset->accum_depr_coa_id,
                    'tanggal' => $tanggal,
                    'keterangan' => $memo,
                    'debit' => 0,
                    'kredit' => $depr,
                    'referensi' => $referensi,
                    'tipe_referensi' => 'asset_depreciation',
                    'created_by' => $userId,
                ]);
            }

            // Lock asset from deletion after depreciation generated
            $asset->locked = true;
            $asset->save();
        });
    }
}
