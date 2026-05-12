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

        $countPosted = 0; $skipped = 0; $corrected = 0;
        $asets = Aset::with(['expenseCoa', 'accumDepreciationCoa'])
                    ->whereNotNull('metode_penyusutan')
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
            $existingJournal = JournalEntry::where('ref_type','depr')
                ->where('ref_id', $a->id)
                ->whereBetween('tanggal', [$from, $to])
                ->first();
            
            // GUNAKAN LOGIKA YANG SAMA PERSIS DENGAN AsetController.show()
            // PRIORITAS: Hitung ulang berdasarkan data aset untuk memastikan akurasi
            $totalPerolehan = $cost + (float)($a->biaya_perolehan ?? 0);
            
            // Hitung penyusutan yang benar berdasarkan metode
            $penyusutan = 0;
            if ($life > 0 && $totalPerolehan > 0) {
                switch ($a->metode_penyusutan) {
                    case 'garis_lurus':
                        $nilaiDisusutkan = $totalPerolehan - $res;
                        $penyusutanPerTahun = $nilaiDisusutkan / $life;
                        $penyusutan = $penyusutanPerTahun / 12;
                        break;
                        
                    case 'saldo_menurun':
                        // Double Declining Balance - GUNAKAN RATA-RATA untuk konsistensi posting bulanan
                        // Bukan nilai yang berubah setiap bulan, tapi nilai tetap
                        $nilaiDisusutkan = $totalPerolehan - $res;
                        $averagePerTahun = $nilaiDisusutkan / $life;
                        $penyusutan = $averagePerTahun / 12;
                        break;
                        
                    case 'sum_of_years_digits':
                        // Sum of Years Digits - GUNAKAN RATA-RATA untuk konsistensi posting bulanan
                        $nilaiDisusutkan = $totalPerolehan - $res;
                        $averagePerTahun = $nilaiDisusutkan / $life;
                        $penyusutan = $averagePerTahun / 12;
                        break;
                        
                    default:
                        // Default ke garis lurus
                        $nilaiDisusutkan = $totalPerolehan - $res;
                        $penyusutanPerTahun = $nilaiDisusutkan / $life;
                        $penyusutan = $penyusutanPerTahun / 12;
                        break;
                }
            }
            
            // DEBUG: Print perhitungan yang benar
            \Log::info("DEBUG PENYUSUTAN - Aset: {$a->nama_aset} (ID: {$a->id})");
            \Log::info("  Total Perolehan: Rp " . number_format($totalPerolehan, 0, ',', '.'));
            \Log::info("  Nilai Residu: Rp " . number_format($res, 0, ',', '.'));
            \Log::info("  Nilai Disusutkan: Rp " . number_format($totalPerolehan - $res, 0, ',', '.'));
            \Log::info("  Umur Manfaat: {$life} tahun");
            \Log::info("  Metode: {$a->metode_penyusutan}");
            \Log::info("  Penyusutan/bulan (BENAR): Rp " . number_format($penyusutan, 2, ',', '.'));
            
            // Fallback ke nilai DB jika perhitungan menghasilkan 0
            if ($penyusutan <= 0) {
                $penyusutan = (float)($a->getAttributes()['penyusutan_per_bulan'] ?? 0);
                \Log::info("  Fallback ke DB: Rp " . number_format($penyusutan, 2, ',', '.'));
            }
            
            if ($penyusutan <= 0) { $skipped++; continue; }

            // Jika sudah ada jurnal, cek apakah nilainya benar
            if ($existingJournal) {
                $currentAmount = $existingJournal->lines()->where('debit', '>', 0)->first()->debit ?? 0;
                $selisih = abs($currentAmount - $penyusutan);
                
                if ($selisih > 0.01) {
                    \Log::info("KOREKSI DIPERLUKAN - Nilai saat ini: Rp " . number_format($currentAmount, 2, ',', '.') . ", Nilai benar: Rp " . number_format($penyusutan, 2, ',', '.'));
                    
                    // Hapus jurnal lama
                    $existingJournal->lines()->delete();
                    $existingJournal->delete();
                    
                    // Reset akumulasi penyusutan
                    $currentAkumulasi = (float)($a->akumulasi_penyusutan ?? 0);
                    $newAkumulasi = max($currentAkumulasi - $currentAmount, 0);
                    $a->update(['akumulasi_penyusutan' => $newAkumulasi]);
                    
                    $corrected++;
                } else {
                    $skipped++; 
                    continue;
                }
            }

            // VALIDASI: Pastikan nilai penyusutan masuk akal
            $nilaiDisusutkan = $totalPerolehan - $res;
            $maxPenyusutanPerBulan = $nilaiDisusutkan / ($life * 12);
            
            // Jika penyusutan melebihi maksimal yang wajar, gunakan perhitungan ulang
            if ($penyusutan > $maxPenyusutanPerBulan * 1.5) {
                \Log::warning("PENYUSUTAN TERLALU BESAR untuk {$a->nama_aset}: Rp " . number_format($penyusutan, 0, ',', '.') . " > Max: Rp " . number_format($maxPenyusutanPerBulan, 0, ',', '.'));
                $penyusutan = $maxPenyusutanPerBulan;
            }

            // Post jurnal: Dr Beban Penyusutan; Cr Akumulasi Penyusutan
            $methodLabel = match($a->metode_penyusutan) {
                'garis_lurus' => 'GL',
                'saldo_menurun' => 'SM',
                'sum_of_years_digits' => 'SYD',
                default => $a->metode_penyusutan
            };
            
            \Log::info("DEBUG PENYUSUTAN - POSTING JURNAL untuk {$a->nama_aset}: Rp " . number_format($penyusutan, 0, ',', '.'));
            
            // Gunakan COA yang sudah diatur di aset, bukan hardcode
            $bebanPenyusutanCoa = $a->expenseCoa ? $a->expenseCoa->kode_akun : '5103';
            $akumulasiPenyusutanCoa = $a->accumDepreciationCoa ? $a->accumDepreciationCoa->kode_akun : '120401';
            
            // Pastikan COA ada di database, jika tidak buat otomatis
            $this->ensureCoaExists($bebanPenyusutanCoa, 'Beban Penyusutan', 'Expense', 'debit');
            $this->ensureCoaExists($akumulasiPenyusutanCoa, 'Akumulasi Penyusutan Peralatan', 'Asset', 'credit');
            
            $journal->post($dt->toDateString(), 'depr', (int)$a->id, 'Penyusutan Aset '.$a->nama_aset.' ('.$methodLabel.') '.$dt->format('Y-m'), [
                ['code' => $bebanPenyusutanCoa, 'debit' => (float)$penyusutan, 'credit' => 0],  // Beban Penyusutan
                ['code' => $akumulasiPenyusutanCoa, 'debit' => 0, 'credit' => (float)$penyusutan],  // Akumulasi Penyusutan
            ]);
            
            // Update akumulasi penyusutan di tabel aset setelah posting
            $currentAkumulasi = (float)($a->akumulasi_penyusutan ?? 0);
            $newAkumulasi = $currentAkumulasi + $penyusutan;
            $newNilaiBuku = $totalPerolehan - $newAkumulasi;
            
            $a->update([
                'akumulasi_penyusutan' => $newAkumulasi,
                'nilai_buku' => max($newNilaiBuku, (float)($a->nilai_residu ?? 0)) // Tidak boleh kurang dari nilai residu
            ]);
            
            \Log::info("DEBUG PENYUSUTAN - UPDATED AKUMULASI untuk {$a->nama_aset}: Akumulasi baru = Rp " . number_format($newAkumulasi, 0, ',', '.') . ", Nilai buku = Rp " . number_format($newNilaiBuku, 0, ',', '.'));
            
            $countPosted++;
        }

        $message = "Penyusutan bulan {$dt->format('Y-m')} diposting: {$countPosted} aset, dilewati: {$skipped}";
        if ($corrected > 0) {
            $message .= ", dikoreksi: {$corrected}";
        }
        
        return back()->with('success', $message . ".");
    }

    /**
     * Hitung penyusutan per bulan sesuai metode aset
     * HARUS SAMA PERSIS dengan AsetController.show() untuk konsistensi
     */
    private function calculateMonthlyDepreciation($aset, $period)
    {
        // PRIORITAS UTAMA: Gunakan penyusutan_per_bulan dari masing-masing aset
        $penyusutanPerBulan = (float)($aset->getAttributes()['penyusutan_per_bulan'] ?? 0);
        
        if ($penyusutanPerBulan > 0) {
            return $penyusutanPerBulan;
        }
        
        // Jika tidak ada penyusutan_per_bulan, hitung manual berdasarkan metode
        // LOGIKA INI HARUS SAMA PERSIS dengan AsetController.show()
        $cost = (float)($aset->harga_perolehan ?? 0);
        $res  = (float)($aset->nilai_residu ?? 0);
        $life = (int)($aset->umur_manfaat ?? 0);
        
        if ($life <= 0 || $cost <= 0) return 0;
        
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
    
    /**
     * Pastikan COA ada di database, jika tidak buat otomatis
     */
    private function ensureCoaExists($kodeAkun, $namaAkun, $tipeAkun, $saldoNormal)
    {
        $coa = \App\Models\Coa::where('kode_akun', $kodeAkun)->first();
        
        if (!$coa) {
            \Log::info("Creating missing COA: {$kodeAkun} - {$namaAkun}");
            
            $kategoriAkun = match($tipeAkun) {
                'Asset' => 'Aset Tetap',
                'Expense' => 'Beban Operasional',
                default => 'Lainnya'
            };
            
            \App\Models\Coa::create([
                'kode_akun' => $kodeAkun,
                'tipe_akun' => $tipeAkun,
                'nama_akun' => $namaAkun,
                'kategori_akun' => $kategoriAkun,
                'is_akun_header' => false,
                'kode_induk' => null,
                'saldo_normal' => $saldoNormal,
                'keterangan' => "Auto-created for asset depreciation"
            ]);
        }
        
        return $coa;
    }
}

