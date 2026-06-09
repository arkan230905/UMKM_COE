<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\Coa;
use App\Models\JurnalUmum;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class JurnalPenyesuaianAsetController extends Controller
{
    public function index(Request $request)
    {
        // Get filter params
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);
        
        // Format periode untuk query
        $periode = Carbon::createFromDate($tahun, $bulan, 1);
        $periodeStr = $periode->format('Y-m');
        
        // Get all active assets yang sudah posted dengan COA lengkap
        $asets = Aset::where('user_id', auth()->id())
            ->where('status', 'aktif')
            ->whereNotNull('expense_coa_id')
            ->whereNotNull('accum_depr_coa_id')
            ->with(['expenseCoa', 'accumDepreciationCoa', 'kategori'])
            ->get();
        
        // Calculate depreciation for each asset
        $jurnalEntries = [];
        $totalDebit = 0;
        $totalKredit = 0;
        
        foreach ($asets as $aset) {
            // Get penyusutan per bulan
            $penyusutanPerBulan = (float)($aset->penyusutan_per_bulan ?? 0);
            
            // Skip jika penyusutan 0
            if ($penyusutanPerBulan <= 0) {
                continue;
            }
            
            // Create journal entry
            $jurnalEntries[] = [
                'tanggal' => $periode->endOfMonth()->format('Y-m-d'),
                'keterangan_debit' => "Beban Penyusutan {$aset->nama_aset}",
                'keterangan_kredit' => "Akumulasi Penyusutan {$aset->nama_aset}",
                'ref_debit' => $aset->expenseCoa->kode_akun ?? '-',
                'ref_kredit' => $aset->accumDepreciationCoa->kode_akun ?? '-',
                'coa_debit' => $aset->expenseCoa,
                'coa_kredit' => $aset->accumDepreciationCoa,
                'debit' => $penyusutanPerBulan,
                'kredit' => $penyusutanPerBulan,
                'aset_id' => $aset->id,
                'aset_nama' => $aset->nama_aset,
                'kategori' => $aset->kategori->nama ?? '-'
            ];
            
            $totalDebit += $penyusutanPerBulan;
            $totalKredit += $penyusutanPerBulan;
        }
        
        // Check if already posted
        $isPosted = JurnalUmum::where('user_id', auth()->id())
            ->where('tipe_referensi', 'adjustment_depreciation')
            ->where('tanggal', 'LIKE', $periodeStr . '%')
            ->exists();
        
        // Get company info
        $perusahaan = Perusahaan::where('user_id', auth()->id())->first();
        
        return view('laporan.jurnal-penyesuaian-aset', compact(
            'jurnalEntries',
            'totalDebit',
            'totalKredit',
            'bulan',
            'tahun',
            'periode',
            'isPosted',
            'perusahaan'
        ));
    }
    
    public function postToJurnal(Request $request)
    {
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        
        if (!$bulan || !$tahun) {
            return response()->json([
                'success' => false,
                'message' => 'Bulan dan tahun harus diisi'
            ]);
        }
        
        // Format periode
        $periode = Carbon::createFromDate($tahun, $bulan, 1);
        $periodeStr = $periode->format('Y-m');
        $tanggalPosting = $periode->endOfMonth()->format('Y-m-d');
        
        // Check if already posted
        $existingJournal = JurnalUmum::where('user_id', auth()->id())
            ->where('tipe_referensi', 'adjustment_depreciation')
            ->where('tanggal', 'LIKE', $periodeStr . '%')
            ->exists();
        
        if ($existingJournal) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal penyesuaian periode ini sudah diposting sebelumnya'
            ]);
        }
        
        DB::beginTransaction();
        try {
            // Get all active assets
            $asets = Aset::where('user_id', auth()->id())
                ->where('status', 'aktif')
                ->whereNotNull('expense_coa_id')
                ->whereNotNull('accum_depr_coa_id')
                ->with(['expenseCoa', 'accumDepreciationCoa'])
                ->get();
            
            $jurnalCount = 0;
            
            foreach ($asets as $aset) {
                $penyusutanPerBulan = (float)($aset->penyusutan_per_bulan ?? 0);
                
                if ($penyusutanPerBulan <= 0) {
                    continue;
                }
                
                // Create Jurnal Umum Entry
                $jurnalEntry = JurnalUmum::create([
                    'user_id' => auth()->id(),
                    'tanggal' => $tanggalPosting,
                    'nomor_jurnal' => $this->generateNomorJurnal($tanggalPosting),
                    'keterangan' => "Penyusutan {$aset->nama_aset} - " . $periode->isoFormat('MMMM YYYY'),
                    'tipe_referensi' => 'adjustment_depreciation',
                    'referensi' => 'ASET-' . $aset->id . '-' . $periodeStr,
                    'total_debit' => $penyusutanPerBulan,
                    'total_kredit' => $penyusutanPerBulan,
                ]);
                
                // Create Debit Line (Beban Penyusutan)
                DB::table('jurnal_detail')->insert([
                    'jurnal_umum_id' => $jurnalEntry->id,
                    'coa_id' => $aset->expense_coa_id,
                    'debit' => $penyusutanPerBulan,
                    'kredit' => 0,
                    'keterangan' => "Beban Penyusutan {$aset->nama_aset}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Create Kredit Line (Akumulasi Penyusutan)
                DB::table('jurnal_detail')->insert([
                    'jurnal_umum_id' => $jurnalEntry->id,
                    'coa_id' => $aset->accum_depr_coa_id,
                    'debit' => 0,
                    'kredit' => $penyusutanPerBulan,
                    'keterangan' => "Akumulasi Penyusutan {$aset->nama_aset}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $jurnalCount++;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil memposting {$jurnalCount} jurnal penyesuaian aset"
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error posting jurnal penyesuaian: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memposting jurnal: ' . $e->getMessage()
            ]);
        }
    }
    
    public function cetakPdf(Request $request)
    {
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);
        
        $periode = Carbon::createFromDate($tahun, $bulan, 1);
        $periodeStr = $periode->format('Y-m');
        
        // Get all active assets
        $asets = Aset::where('user_id', auth()->id())
            ->where('status', 'aktif')
            ->whereNotNull('expense_coa_id')
            ->whereNotNull('accum_depr_coa_id')
            ->with(['expenseCoa', 'accumDepreciationCoa', 'kategori'])
            ->get();
        
        $jurnalEntries = [];
        $totalDebit = 0;
        $totalKredit = 0;
        
        foreach ($asets as $aset) {
            $penyusutanPerBulan = (float)($aset->penyusutan_per_bulan ?? 0);
            
            if ($penyusutanPerBulan <= 0) {
                continue;
            }
            
            $jurnalEntries[] = [
                'tanggal' => $periode->endOfMonth()->format('Y-m-d'),
                'keterangan_debit' => "Beban Penyusutan {$aset->nama_aset}",
                'keterangan_kredit' => "Akumulasi Penyusutan {$aset->nama_aset}",
                'ref_debit' => $aset->expenseCoa->kode_akun ?? '-',
                'ref_kredit' => $aset->accumDepreciationCoa->kode_akun ?? '-',
                'debit' => $penyusutanPerBulan,
                'kredit' => $penyusutanPerBulan,
                'kategori' => $aset->kategori->nama ?? '-'
            ];
            
            $totalDebit += $penyusutanPerBulan;
            $totalKredit += $penyusutanPerBulan;
        }
        
        $isPosted = JurnalUmum::where('user_id', auth()->id())
            ->where('tipe_referensi', 'adjustment_depreciation')
            ->where('tanggal', 'LIKE', $periodeStr . '%')
            ->exists();
        
        $perusahaan = Perusahaan::where('user_id', auth()->id())->first();
        
        $pdf = PDF::loadView('laporan.jurnal-penyesuaian-aset-pdf', compact(
            'jurnalEntries',
            'totalDebit',
            'totalKredit',
            'periode',
            'isPosted',
            'perusahaan'
        ));
        
        $filename = 'Jurnal_Penyesuaian_Aset_' . $periode->isoFormat('MMMM_YYYY') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    private function generateNomorJurnal($tanggal)
    {
        $date = Carbon::parse($tanggal);
        $prefix = 'JPA-' . $date->format('Ym') . '-';
        
        $lastJurnal = JurnalUmum::where('user_id', auth()->id())
            ->where('nomor_jurnal', 'LIKE', $prefix . '%')
            ->orderBy('nomor_jurnal', 'desc')
            ->first();
        
        if ($lastJurnal) {
            $lastNumber = (int) substr($lastJurnal->nomor_jurnal, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
