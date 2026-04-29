<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\JurnalUmum;
use Illuminate\Support\Facades\DB;

class NeracaService
{
    /**
     * Generate Laporan Posisi Keuangan (Neraca)
     */
    public function generateLaporanPosisiKeuangan($tanggalAwal = null, $tanggalAkhir = null)
    {
        // Default ke periode bulan ini jika tidak ada tanggal
        if (!$tanggalAwal) {
            $tanggalAwal = now()->startOfMonth()->format('Y-m-d');
        }
        if (!$tanggalAkhir) {
            $tanggalAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        // Ambil saldo akun dari neraca saldo
        $neracaSaldo = $this->getNeracaSaldo($tanggalAwal, $tanggalAkhir);
        
        // Kategorikan akun berdasarkan jenis
        $asetLancar = $this->calculateAsetLancar($neracaSaldo);
        $asetTidakLancar = $this->calculateAsetTidakLancar($neracaSaldo);
        $kewajiban = $this->calculateKewajiban($neracaSaldo);
        $ekuitas = $this->calculateEkuitas($neracaSaldo);
        
        // Hitung total
        $totalAsetLancar = array_sum(array_column($asetLancar, 'saldo'));
        $totalAsetTidakLancar = array_sum(array_column($asetTidakLancar, 'saldo'));
        $totalAset = $totalAsetLancar + $totalAsetTidakLancar;
        
        $totalKewajiban = array_sum(array_column($kewajiban, 'saldo'));
        $totalEkuitas = array_sum(array_column($ekuitas, 'saldo'));
        $totalKewajibanEkuitas = $totalKewajiban + $totalEkuitas;
        
        // Cek keseimbangan neraca
        $isBalanced = abs($totalAset - $totalKewajibanEkuitas) < 0.01;
        $selisih = $totalAset - $totalKewajibanEkuitas;
        
        return [
            'periode' => [
                'tanggal_awal' => $tanggalAwal,
                'tanggal_akhir' => $tanggalAkhir
            ],
            'aset' => [
                'lancar' => $asetLancar,
                'tidak_lancar' => $asetTidakLancar,
                'total_lancar' => $totalAsetLancar,
                'total_tidak_lancar' => $totalAsetTidakLancar,
                'total_aset' => $totalAset
            ],
            'kewajiban' => [
                'detail' => $kewajiban,
                'total' => $totalKewajiban
            ],
            'ekuitas' => [
                'detail' => $ekuitas,
                'total' => $totalEkuitas
            ],
            'total_kewajiban_ekuitas' => $totalKewajibanEkuitas,
            'neraca_seimbang' => $isBalanced,
            'selisih' => $selisih
        ];
    }
    
    /**
     * Ambil data neraca saldo
     */
    private function getNeracaSaldo($tanggalAwal, $tanggalAkhir)
    {
        // Gunakan TrialBalanceService untuk mendapatkan data yang sama persis
        $trialBalanceService = app(\App\Services\TrialBalanceService::class);
        $trialBalanceData = $trialBalanceService->calculateTrialBalance($tanggalAwal, $tanggalAkhir);
        
        $neracaSaldo = [];
        
        foreach ($trialBalanceData['accounts'] as $account) {
            // Hitung saldo akhir berdasarkan saldo awal + mutasi
            $saldo = 0;
            
            if ($account['debit'] > 0) {
                // Akun normal debit (aset/beban): Saldo Akhir = Saldo Awal + Debit - Kredit
                $saldo = $account['debit']; // Ini adalah saldo akhir yang sudah dihitung oleh TrialBalanceService
            } elseif ($account['kredit'] > 0) {
                // Akun normal kredit (kewajiban/ekuitas/pendapatan): Saldo Akhir = Saldo Awal - Debit + Kredit
                $saldo = -$account['kredit']; // Negatif untuk kewajiban/ekuitas
            }
            
            $neracaSaldo[] = [
                'coa_id' => null,
                'kode_akun' => $account['kode_akun'],
                'nama_akun' => $account['nama_akun'],
                'tipe_akun' => $account['tipe_akun'],
                'kategori_akun' => null,
                'saldo' => $saldo,
                'debit' => $account['debit'],
                'kredit' => $account['kredit'],
                'total_debit' => $account['mutasi_debit'],
                'total_kredit' => $account['mutasi_kredit']
            ];
        }
        
        return collect($neracaSaldo);
    }

    
    /**
     * Hitung Aset Lancar
     */
    private function calculateAsetLancar($neracaSaldo)
    {
        $asetLancar = [];
        
        // Kas dan Bank - pastikan bukan Utang/Hutang
        $kasBank = $neracaSaldo->filter(function($item) {
            return ((stripos($item['nama_akun'], 'kas') !== false || 
                     stripos($item['nama_akun'], 'bank') !== false) &&
                    stripos($item['nama_akun'], 'piutang') === false &&
                    stripos($item['nama_akun'], 'utang') === false &&
                    stripos($item['nama_akun'], 'hutang') === false) ||
                   $item['kode_akun'] === '111';
        });
        
        foreach ($kasBank as $item) {
            $saldo = $item['debit'] > 0 ? $item['debit'] : -$item['kredit'];
            if (abs($saldo) > 0.01) {
                $asetLancar[] = [
                    'nama_akun' => $item['nama_akun'],
                    'kode_akun' => $item['kode_akun'],
                    'saldo' => $saldo
                ];
            }
        }
        
        // Piutang Usaha
        $piutang = $neracaSaldo->filter(function($item) {
            return stripos($item['nama_akun'], 'piutang') !== false;
        });
        
        foreach ($piutang as $item) {
            $saldo = $item['debit'] > 0 ? $item['debit'] : -$item['kredit'];
            if (abs($saldo) > 0.01) {
                $asetLancar[] = [
                    'nama_akun' => $item['nama_akun'],
                    'kode_akun' => $item['kode_akun'],
                    'saldo' => $saldo
                ];
            }
        }
        
        // PPN Masukan
        $ppnMasukan = $neracaSaldo->filter(function($item) {
            return stripos($item['nama_akun'], 'ppn masukan') !== false ||
                   $item['kode_akun'] === '127';
        });
        
        foreach ($ppnMasukan as $item) {
            $saldo = $item['debit'] > 0 ? $item['debit'] : -$item['kredit'];
            if (abs($saldo) > 0.01) {
                $asetLancar[] = [
                    'nama_akun' => $item['nama_akun'],
                    'kode_akun' => $item['kode_akun'],
                    'saldo' => $saldo
                ];
            }
        }
        
        // Persediaan Bahan Baku & Pendukung (PINDAH KE ASET LANCAR)
        $persediaan = $neracaSaldo->filter(function($item) {
            return (stripos($item['nama_akun'], 'persediaan') !== false ||
                    stripos($item['nama_akun'], 'pers.') !== false ||
                    stripos($item['nama_akun'], 'bahan baku') !== false ||
                    stripos($item['nama_akun'], 'bahan pendukung') !== false ||
                    stripos($item['nama_akun'], 'barang jadi') !== false ||
                    in_array($item['kode_akun'], ['1104', '1107', '1141', '1142', '1143', '1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '1161', '1162']));
        });
        
        foreach ($persediaan as $item) {
            $saldo = $item['debit'] > 0 ? $item['debit'] : -$item['kredit'];
            if (abs($saldo) > 0.01) {
                $asetLancar[] = [
                    'nama_akun' => $item['nama_akun'],
                    'kode_akun' => $item['kode_akun'],
                    'saldo' => $saldo
                ];
            }
        }
        
        return $asetLancar;
    }
    
    /**
     * Hitung Aset Tidak Lancar
     */
    private function calculateAsetTidakLancar($neracaSaldo)
    {
        $asetTidakLancar = [];
        
        // Aset Tetap (TIDAK TERMASUK PERSEDIAAN dan BIAYA PENYUSUTAN)
        $asetTetap = $neracaSaldo->filter(function($item) {
            // Exclude expense accounts (5xx) - these are never assets
            $firstDigit = substr($item['kode_akun'], 0, 1);
            if ($firstDigit == '5') {
                return false;
            }
            
            // Include only actual fixed asset names
            $isFixedAsset = (stripos($item['nama_akun'], 'peralatan') !== false ||
                           stripos($item['nama_akun'], 'gedung') !== false ||
                           stripos($item['nama_akun'], 'kendaraan') !== false ||
                           stripos($item['nama_akun'], 'mesin') !== false ||
                           stripos($item['nama_akun'], 'tanah') !== false ||
                           stripos($item['nama_akun'], 'bangunan') !== false);
            
            // Exclude accumulated depreciation (these go in separate section)
            $isAccumulatedDepreciation = stripos($item['nama_akun'], 'akumulasi') !== false;
            
            // Exclude inventory
            $isInventory = stripos($item['nama_akun'], 'persediaan') !== false ||
                          stripos($item['nama_akun'], 'pers.') !== false;
            
            // Exclude depreciation expenses
            $isDepreciationExpense = stripos($item['nama_akun'], 'biaya penyusutan') !== false ||
                                   stripos($item['nama_akun'], 'beban penyusutan') !== false ||
                                   (stripos($item['nama_akun'], 'penyusutan') !== false && 
                                    stripos($item['nama_akun'], 'akumulasi') === false);
            
            return $isFixedAsset && 
                   !$isAccumulatedDepreciation && 
                   !$isInventory && 
                   !$isDepreciationExpense;
        });
        
        foreach ($asetTetap as $item) {
            $saldo = $item['debit'] > 0 ? $item['debit'] : -$item['kredit'];
            if (abs($saldo) > 0.01) {
                $asetTidakLancar[] = [
                    'nama_akun' => $item['nama_akun'],
                    'kode_akun' => $item['kode_akun'],
                    'saldo' => $saldo
                ];
            }
        }
        
        // Akumulasi Penyusutan (mengurangi aset)
        $akumulasiPenyusutan = $neracaSaldo->filter(function($item) {
            return stripos($item['nama_akun'], 'akumulasi penyusutan') !== false ||
                   stripos($item['nama_akun'], 'akumulasi') !== false && stripos($item['nama_akun'], 'penyusutan') !== false;
        });
        
        foreach ($akumulasiPenyusutan as $item) {
            $saldo = $item['kredit'] > 0 ? $item['kredit'] : $item['debit'];
            if ($saldo > 0.01) {
                $asetTidakLancar[] = [
                    'nama_akun' => $item['nama_akun'],
                    'kode_akun' => $item['kode_akun'],
                    'saldo' => -$saldo // Negatif karena mengurangi aset
                ];
            }
        }
        
        return $asetTidakLancar;
    }
    
    /**
     * Hitung Kewajiban
     */
    private function calculateKewajiban($neracaSaldo)
    {
        $kewajiban = [];
        $processedCodes = []; // Track kode akun yang sudah diproses
        
        // Filter berdasarkan kode akun untuk menghindari duplikasi
        foreach ($neracaSaldo as $item) {
            // Skip jika sudah diproses
            if (in_array($item['kode_akun'], $processedCodes)) {
                continue;
            }
            
            // Cek apakah ini kewajiban berdasarkan kode akun (2xx) atau nama
            $isKewajiban = false;
            $firstDigit = substr($item['kode_akun'], 0, 1);
            
            if ($firstDigit == '2') {
                // Kode 2xx adalah kewajiban
                $isKewajiban = true;
            } elseif (stripos($item['nama_akun'], 'hutang') !== false || 
                      stripos($item['nama_akun'], 'utang') !== false) {
                // Atau nama mengandung hutang/utang (tapi bukan piutang)
                if (stripos($item['nama_akun'], 'piutang') === false) {
                    $isKewajiban = true;
                }
            } elseif (stripos($item['nama_akun'], 'ppn keluaran') !== false) {
                // Atau PPN Keluaran
                $isKewajiban = true;
            }
            
            if ($isKewajiban && $item['kredit'] > 0) {
                $kewajiban[] = [
                    'nama_akun' => $item['nama_akun'],
                    'kode_akun' => $item['kode_akun'],
                    'saldo' => $item['kredit']
                ];
                $processedCodes[] = $item['kode_akun'];
            }
        }
        
        return $kewajiban;
    }
    
    /**
     * Hitung Ekuitas
     */
    private function calculateEkuitas($neracaSaldo)
    {
        $ekuitas = [];
        
        // Modal
        $modal = $neracaSaldo->filter(function($item) {
            return $item['tipe_akun'] === 'Ekuitas' || 
                   stripos($item['nama_akun'], 'modal') !== false ||
                   in_array($item['kode_akun'], ['311', '3111']);
        });
        
        foreach ($modal as $item) {
            if ($item['kredit'] > 0) {
                $ekuitas[] = [
                    'nama_akun' => $item['nama_akun'],
                    'kode_akun' => $item['kode_akun'],
                    'saldo' => $item['kredit'] // Gunakan nilai kredit dari neraca saldo
                ];
            }
        }
        
        // Hitung dan tambahkan Laba/Rugi Berjalan ke Ekuitas
        $labaRugi = $this->calculateLabaRugi($neracaSaldo);
        
        if ($labaRugi != 0) {
            $ekuitas[] = [
                'nama_akun' => $labaRugi > 0 ? 'Laba Tahun Berjalan' : 'Rugi Tahun Berjalan',
                'kode_akun' => '399',
                'saldo' => abs($labaRugi)
            ];
        }
        
        return $ekuitas;
    }
    
    /**
     * Hitung Laba Rugi
     */
    private function calculateLabaRugi($neracaSaldo)
    {
        // Gunakan TrialBalanceService untuk mendapatkan data lengkap
        $trialBalanceService = app(\App\Services\TrialBalanceService::class);
        $trialBalance = $trialBalanceService->calculateTrialBalance(
            now()->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d')
        );
        
        // Pendapatan (4xx accounts)
        $pendapatan = 0;
        $biaya = 0;
        
        foreach ($trialBalance['accounts'] as $account) {
            $firstDigit = substr($account['kode_akun'], 0, 1);
            
            if ($firstDigit == '4') {
                // Revenue accounts (4xx) - credit normal
                $pendapatan += $account['kredit'];
            } elseif ($firstDigit == '5') {
                // Expense accounts (5xx) - debit normal
                $biaya += $account['debit'];
            }
        }
        
        // Laba/Rugi = Pendapatan - Biaya
        $labaRugi = $pendapatan - $biaya;
        
        return $labaRugi;
    }
}