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
            // Cek sudah dipost bulan ini?
            $exists = JournalEntry::where('ref_type','depr')
                ->where('ref_id', $a->id)
                ->whereBetween('tanggal', [$from, $to])
                ->exists();
            if ($exists) { $skipped++; continue; }

            $months = $life * 12;
            $base = max($cost - $res, 0);
            $perMonth = $months > 0 ? ($base / $months) : 0;
            if ($perMonth <= 0) { $skipped++; continue; }

            // Post jurnal: Dr Beban Penyusutan (5103); Cr Akumulasi Penyusutan (120401)
            $methodLabel = match($a->metode_penyusutan) {
                'garis_lurus' => 'GL',
                'saldo_menurun' => 'SM',
                'sum_of_years_digits' => 'SYD',
                default => $a->metode_penyusutan
            };
            $journal->post($dt->toDateString(), 'depr', (int)$a->id, 'Penyusutan Aset '.$a->nama_aset.' ('.$methodLabel.') '.$dt->format('Y-m'), [
                ['code' => '5103', 'debit' => (float)$perMonth, 'credit' => 0],  // Beban Penyusutan
                ['code' => '120401', 'debit' => 0, 'credit' => (float)$perMonth],  // Akumulasi Penyusutan Peralatan
            ]);
            $countPosted++;
        }

        return back()->with('success', "Penyusutan bulan {$dt->format('Y-m')} diposting: {$countPosted} aset, dilewati: {$skipped}.");
    }
}

