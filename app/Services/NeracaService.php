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
            // Hitung saldo berdasarkan debit dan kredit yang ditampilkan di neraca saldo
            // Untuk aset: gunakan nilai debit
            // Untuk kewajiban & ekuitas: gunakan nilai kredit
            $saldo = 0;
            
            if ($account['debit'] > 0) {
                $saldo = $account['debit'];
            } elseif ($account['kredit'] > 0) {
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
        
        // Aset Tetap (TIDAK TERMASUK PERSEDIAAN)
        $asetTetap = $neracaSaldo->filter(function($item) {
            return (stripos($item['nama_akun'], 'peralatan') !== false ||
                    stripos($item['nama_akun'], 'gedung') !== false ||
                    stripos($item['nama_akun'], 'kendaraan') !== false ||
                    stripos($item['nama_akun'], 'mesin') !== false ||
                    stripos($item['nama_akun'], 'tanah') !== false ||
                    stripos($item['nama_akun'], 'bangunan') !== false) &&
                    stripos($item['nama_akun'], 'akumulasi') === false &&
                    stripos($item['nama_akun'], 'persediaan') === false &&
                    stripos($item['nama_akun'], 'pers.') === false;
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
        
        // TIDAK menambahkan Laba/Rugi Berjalan
        // Laba/Rugi tidak ditampilkan di Laporan Posisi Keuangan
        
        return $ekuitas;
    }
    
    /**
     * Hitung Laba Rugi
     */
    private function calculateLabaRugi($neracaSaldo)
    {
        // Pendapatan (muncul di kredit di neraca saldo)
        $pendapatan = $neracaSaldo->filter(function($item) {
            return $item['tipe_akun'] === 'Pendapatan' ||
                   stripos($item['nama_akun'], 'penjualan') !== false ||
                   stripos($item['nama_akun'], 'pendapatan') !== false;
        })->sum('kredit');
        
        // Biaya (muncul di debit di neraca saldo)
        $biaya = $neracaSaldo->filter(function($item) {
            return $item['tipe_akun'] === 'Biaya' ||
                   stripos($item['nama_akun'], 'biaya') !== false ||
                   stripos($item['nama_akun'], 'beban') !== false;
        })->sum('debit');
        
        // Laba/Rugi = Pendapatan - Biaya
        // Return 0 karena tidak ditampilkan di Laporan Posisi Keuangan
        return 0;
    }
}