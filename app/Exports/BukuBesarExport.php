<?php

namespace App\Exports;

use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class BukuBesarExport
{
    protected $from;
    protected $to;
    protected $accountCode;

    public function __construct($from = null, $to = null, $accountCode = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->accountCode = $accountCode;
    }

    public function download($filename = 'buku-besar.csv')
    {
        // Bersihkan output buffer jika ada (mencegah file corrupt)
        if (ob_get_contents()) {
            ob_clean();
        }

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 (helps with Excel compatibility)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Fetch COAs
        $coasQuery = Coa::where('user_id', auth()->id())->orderBy('kode_akun');
        if ($this->accountCode) {
            $coasQuery->where('kode_akun', $this->accountCode);
        }
        $coas = $coasQuery->get();
        
        $hasData = false;

        // Tulis header tabel flat yang rapi untuk Excel
        fputcsv($output, [
            'Kode Akun',
            'Nama Akun',
            'Tanggal',
            'Keterangan',
            'Debit',
            'Kredit',
            'Saldo'
        ]);

        foreach ($coas as $coa) {
            $entries = $this->getAccountTransactions($coa->kode_akun);
            
            $bahanBakuCoas = ['1101', '114', '1141', '1142', '1143'];
            $bahanPendukungCoas = ['1150', '1151', '1152', '1153', '1154', '1155', '1156', '1157', '115'];
            if (in_array($coa->kode_akun, $bahanBakuCoas) || in_array($coa->kode_akun, $bahanPendukungCoas)) {
                $saldoAwal = 0; 
            } else {
                $saldoAwal = $coa->saldo_awal ?? 0;
            }

            if ($entries->isEmpty() && $saldoAwal == 0) {
                continue;
            }
            
            $hasData = true;

            // Baris Saldo Awal
            fputcsv($output, [
                $coa->kode_akun,
                $coa->nama_akun,
                '',
                'Saldo Awal',
                '',
                '',
                number_format($saldoAwal, 2, '.', '')
            ]);

            // Transaksi
            $saldo = (float)$saldoAwal;
            
            foreach ($entries as $entry) {
                $debit = (float)($entry->debit ?? 0);
                $kredit = (float)($entry->kredit ?? 0);
                $saldo += ($debit - $kredit);

                fputcsv($output, [
                    $coa->kode_akun,
                    $coa->nama_akun,
                    date('d/m/Y', strtotime($entry->tanggal)),
                    $entry->memo,
                    $debit > 0 ? number_format($debit, 2, '.', '') : '',
                    $kredit > 0 ? number_format($kredit, 2, '.', '') : '',
                    number_format($saldo, 2, '.', '')
                ]);
            }
            
            // Baris kosong antar akun untuk pemisah visual yang rapi di Excel
            fputcsv($output, ['', '', '', '', '', '', '']);
        }

        if (!$hasData) {
            fputcsv($output, ['Tidak ada data untuk periode yang dipilih', '', '', '', '', '', '']);
        }

        fclose($output);
        exit;
    }

    private function getAccountTransactions($accountCode)
    {
        $query = DB::table('jurnal_umum as ju')
            ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
            ->where('ju.user_id', auth()->id())
            ->where('coas.kode_akun', $accountCode)
            ->select([
                'ju.id',
                'ju.tanggal',
                'ju.keterangan as memo',
                'ju.debit',
                'ju.kredit'
            ])
            ->where(function($q) {
                $q->where('ju.debit', '>', 0)
                  ->orWhere('ju.kredit', '>', 0);
            })
            ->orderBy('ju.tanggal','asc')
            ->orderBy('ju.id','asc');
        
        if ($this->from) {
            $query->whereDate('ju.tanggal', '>=', $this->from);
        }
        if ($this->to) {
            $query->whereDate('ju.tanggal', '<=', $this->to);
        }
        
        return $query->get();
    }
}
