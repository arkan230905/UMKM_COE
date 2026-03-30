<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aset;
use App\Services\DepreciationService;
use App\Services\JournalService;
use App\Models\JournalEntry;
use Carbon\Carbon;

class AsetDepreciationController extends Controller
{
    public function index()
    {
        $asets = Aset::orderBy('nama')->get();
        return view('laporan.penyusutan.index', compact('asets'));
    }

    public function show($id, DepreciationService $svc)
    {
        $aset = Aset::findOrFail($id);
        $assetArr = [
            'acquisition_cost' => $aset->acquisition_cost ?? $aset->harga,
            'residual_value' => $aset->residual_value ?? 0,
            'useful_life_years' => $aset->useful_life_years ?? 0,
            'depr_start_date' => $aset->depr_start_date ?? $aset->tanggal_beli,
            'units_capacity_total' => $aset->units_capacity_total,
        ];
        $straight = $svc->scheduleStraightLine($assetArr);
        $ddb      = $svc->scheduleDDB($assetArr);
        $syd      = $svc->scheduleSYD($assetArr);
        $uop      = $svc->scheduleUnitsOfProduction($assetArr);
        return view('laporan.penyusutan.show', compact('aset','straight','ddb','syd','uop'));
    }

    public function postMonthly(Request $request, JournalService $journal)
    {
        $period = $request->input('period'); // format YYYY-MM
        $dt = $period ? Carbon::createFromFormat('Y-m', $period)->endOfMonth() : Carbon::now()->endOfMonth();
        $from = $dt->copy()->startOfMonth()->toDateString();
        $to   = $dt->toDateString();

        $countPosted = 0; $skipped = 0;
        $asets = Aset::whereNotNull('metode_penyusutan')
                    ->whereNotNull('tanggal_akuisisi')
                    ->where('tanggal_akuisisi', '<=', $to)
                    ->get();
        foreach ($asets as $a) {
            $cost = (float)($a->harga_perolehan ?? 0);
            $res  = (float)($a->nilai_residu ?? 0);
            $life = (int)($a->umur_manfaat ?? 0);
            $start= $a->tanggal_akuisisi ?? $a->tanggal_beli;
            if ($life <= 0 || !$start || $cost <= 0) { $skipped++; continue; }
            
            // Cek sudah habis disusutkan
            $currentAccumulated = $a->akumulasi_penyusutan ?? 0;
            $maxDepreciation = $cost - $res;
            if ($currentAccumulated >= $maxDepreciation) { $skipped++; continue; }
            
            // Cek sudah dipost bulan ini?
            $exists = JournalEntry::where('ref_type','depr')
                ->where('ref_id', $a->id)
                ->whereBetween('tanggal', [$from, $to])
                ->exists();
            if ($exists) { $skipped++; continue; }

            // AMBIL NILAI PENYUSUTAN DI DALAM LOOP - LANGSUNG DARI KOLOM DB (bypass accessor)
            $penyusutan = (float)($a->getAttributes()['penyusutan_per_bulan'] ?? 0);
            
            // DEBUG: Print nama aset dan penyusutan_per_bulan
            \Log::info("DEBUG PENYUSUTAN - Aset: {$a->nama_aset} (ID: {$a->id}), Metode: {$a->metode_penyusutan}, Penyusutan/bulan (DB): Rp " . number_format($penyusutan, 0, ',', '.'));
            
            // Jika tidak ada penyusutan_per_bulan, hitung manual sebagai fallback
            if ($penyusutan <= 0) {
                $months = $life * 12;
                $base = max($cost - $res, 0);
                $penyusutan = $months > 0 ? ($base / $months) : 0;
                \Log::info("DEBUG PENYUSUTAN - HITUNG MANUAL untuk {$a->nama_aset}: Rp " . number_format($penyusutan, 0, ',', '.'));
            }
            
            if ($penyusutan <= 0) { $skipped++; continue; }

            // Post jurnal: Dr Beban Penyusutan (5103); Cr Akumulasi Penyusutan (120401)
            $methodLabel = match($a->metode_penyusutan) {
                'garis_lurus' => 'GL',
                'saldo_menurun' => 'SM',
                'sum_of_years_digits' => 'SYD',
                default => $a->metode_penyusutan
            };
            
            \Log::info("DEBUG PENYUSUTAN - POSTING JURNAL untuk {$a->nama_aset}: Rp " . number_format($penyusutan, 0, ',', '.'));
            
            $journal->post($dt->toDateString(), 'depr', (int)$a->id, 'Penyusutan Aset '.$a->nama_aset.' ('.$methodLabel.') '.$dt->format('Y-m'), [
                ['code' => '5103', 'debit' => (float)$penyusutan, 'credit' => 0],  // Beban Penyusutan
                ['code' => '120401', 'debit' => 0, 'credit' => (float)$penyusutan],  // Akumulasi Penyusutan Peralatan
            ]);
            $countPosted++;
        }

        return back()->with('success', "Penyusutan bulan {$dt->format('Y-m')} diposting: {$countPosted} aset, dilewati: {$skipped}.");
    }

    /**
     * Hitung penyusutan per bulan sesuai metode aset
     */
    private function calculateMonthlyDepreciation($aset, $period)
    {
        // PRIORITAS UTAMA: Gunakan penyusutan_per_bulan dari masing-masing aset
        if (isset($aset->penyusutan_per_bulan) && $aset->penyusutan_per_bulan > 0) {
            return (float)$aset->penyusutan_per_bulan;
        }
        
        // Jika tidak ada penyusutan_per_bulan, hitung manual berdasarkan metode
        $cost = (float)($aset->harga_perolehan ?? 0);
        $res  = (float)($aset->nilai_residu ?? 0);
        $life = (int)($aset->umur_manfaat ?? 0);
        
        if ($life <= 0) return 0;
        
        // Hitung manual berdasarkan metode (sebagai fallback)
        switch ($aset->metode_penyusutan) {
            case 'garis_lurus':
                $months = $life * 12;
                $base = max($cost - $res, 0);
                return $months > 0 ? ($base / $months) : 0;
                
            case 'saldo_menurun':
                // Double Declining Balance
                $currentAccumulated = $aset->akumulasi_penyusutan ?? 0;
                $bookValue = max($cost - $currentAccumulated, $res);
                $rate = 2 / $life;
                $annualDepreciation = $bookValue * $rate;
                return max($annualDepreciation / 12, 0);
                
            case 'sum_of_years_digits':
                // Sum of Years Digits
                $currentAccumulated = $aset->akumulasi_penyusutan ?? 0;
                $remainingLife = max($life - ($currentAccumulated / max($cost - $res, 1) * $life), 1);
                $sumOfYears = ($life * ($life + 1)) / 2;
                $annualDepreciation = ($cost - $res) * ($remainingLife / $sumOfYears);
                return max($annualDepreciation / 12, 0);
                
            default:
                // Default ke garis lurus
                $months = $life * 12;
                $base = max($cost - $res, 0);
                return $months > 0 ? ($base / $months) : 0;
        }
    }
}

