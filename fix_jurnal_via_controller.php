<?php

/**
 * Script untuk memperbaiki jurnal umum melalui controller
 * Buat route sementara untuk menjalankan ini
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JurnalUmum;

class FixJurnalController extends Controller
{
    public function fixApril2026Depreciation()
    {
        try {
            // Mulai transaksi
            DB::beginTransaction();
            
            $results = [];
            
            // Data koreksi yang benar
            $corrections = [
                [
                    'asset_name' => 'Mesin Produksi',
                    'old_amount' => 1416667.00,
                    'new_amount' => 1333333.00,
                    'keyword' => 'Mesin'
                ],
                [
                    'asset_name' => 'Peralatan Produksi',
                    'old_amount' => 2833333.00,
                    'new_amount' => 659474.00,
                    'keyword' => 'Peralatan'
                ],
                [
                    'asset_name' => 'Kendaraan',
                    'old_amount' => 2361111.00,
                    'new_amount' => 888889.00,
                    'keyword' => 'Kendaraan'
                ]
            ];
            
            foreach ($corrections as $correction) {
                // Update debit (beban penyusutan)
                $debitUpdated = JurnalUmum::where('tanggal', '2026-04-30')
                    ->where('keterangan', 'like', '%Penyusutan%')
                    ->where('keterangan', 'like', "%{$correction['keyword']}%")
                    ->where('debit', $correction['old_amount'])
                    ->update(['debit' => $correction['new_amount']]);
                
                // Update kredit (akumulasi penyusutan)
                $kreditUpdated = JurnalUmum::where('tanggal', '2026-04-30')
                    ->where('keterangan', 'like', '%Penyusutan%')
                    ->where('keterangan', 'like', "%{$correction['keyword']}%")
                    ->where('kredit', $correction['old_amount'])
                    ->update(['kredit' => $correction['new_amount']]);
                
                $results[] = [
                    'asset' => $correction['asset_name'],
                    'debit_updated' => $debitUpdated,
                    'kredit_updated' => $kreditUpdated,
                    'old_amount' => number_format($correction['old_amount'], 0, ',', '.'),
                    'new_amount' => number_format($correction['new_amount'], 0, ',', '.')
                ];
            }
            
            // Commit transaksi
            DB::commit();
            
            // Validasi hasil
            $validationResults = JurnalUmum::where('tanggal', '2026-04-30')
                ->where('keterangan', 'like', '%Penyusutan%')
                ->orderBy('debit', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Jurnal penyusutan April 2026 berhasil diperbaiki',
                'corrections' => $results,
                'validation' => $validationResults->map(function($journal) {
                    return [
                        'id' => $journal->id,
                        'keterangan' => $journal->keterangan,
                        'debit' => number_format($journal->debit, 0, ',', '.'),
                        'kredit' => number_format($journal->kredit, 0, ',', '.')
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbaiki jurnal: ' . $e->getMessage()
            ]);
        }
    }
    
    public function checkJurnalStatus()
    {
        $journals = JurnalUmum::where('tanggal', '2026-04-30')
            ->where('keterangan', 'like', '%Penyusutan%')
            ->orderBy('debit', 'desc')
            ->get();
        
        $expectedValues = [1333333, 659474, 888889];
        $currentValues = [];
        
        foreach ($journals as $journal) {
            $amount = max($journal->debit, $journal->kredit);
            $currentValues[] = $amount;
        }
        
        $needsFix = !empty(array_diff([1416667, 2833333, 2361111], $currentValues));
        
        return response()->json([
            'needs_fix' => $needsFix,
            'current_journals' => $journals->map(function($journal) {
                return [
                    'id' => $journal->id,
                    'keterangan' => $journal->keterangan,
                    'debit' => number_format($journal->debit, 0, ',', '.'),
                    'kredit' => number_format($journal->kredit, 0, ',', '.'),
                    'amount' => max($journal->debit, $journal->kredit)
                ];
            }),
            'expected_values' => $expectedValues
        ]);
    }
}