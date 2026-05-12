<?php

namespace App\Exports;

use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class BukuBesarExport
{
    protected $from;
    protected $to;

    public function __construct($from = null, $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function download($filename = 'buku-besar.csv')
    {
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 (helps with Excel compatibility)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Get all COA accounts that have transactions or non-zero balance
        $coas = Coa::orderBy('kode_akun')->get();
        $hasData = false;
        
        foreach ($coas as $coa) {
            // Get transactions using the same logic as bukuBesar controller
            $entries = $this->getAccountTransactions($coa->kode_akun);
            $saldoAwal = $coa->saldo_awal ?? 0;
            
            // Skip akun yang tidak ada transaksi dan saldo awal 0
            if ($entries->isEmpty() && $saldoAwal == 0) {
                continue;
            }
            
            $hasData = true;
            
            // Write account header
            fputcsv($output, ['=== ' . $coa->kode_akun . ' - ' . $coa->nama_akun . ' ===']);
            
            // Write periode if exists
            if ($this->from || $this->to) {
                $periode = 'Periode: ';
                if ($this->from) $periode .= date('d/m/Y', strtotime($this->from));
                if ($this->from && $this->to) $periode .= ' s/d ';
                if ($this->to) $periode .= date('d/m/Y', strtotime($this->to));
                fputcsv($output, [$periode]);
            }
            
            // Write headers
            fputcsv($output, ['Tanggal', 'Ref Type', 'Ref ID', 'Keterangan', 'Debit', 'Kredit', 'Saldo']);
            
            // Write saldo awal
            fputcsv($output, ['', '', '', 'Saldo Awal', '', '', number_format($saldoAwal, 2, '.', '')]);
            
            // Write transactions
            $saldo = (float)$saldoAwal;
            $selectedAccountCode = $coa->kode_akun;
            
            foreach ($entries as $entry) {
                foreach ($entry->lines as $line) {
                    // Only process lines for the selected account
                    if ($line->coa->kode_akun == $selectedAccountCode) {
                        $saldo += ((float)$line->debit - (float)$line->credit);
                        
                        fputcsv($output, [
                            date('d/m/Y', strtotime($entry->tanggal)),
                            $entry->ref_type ?? '',
                            $entry->ref_id ?? '',
                            $entry->memo ?? '',
                            $line->debit > 0 ? number_format($line->debit, 2, '.', '') : '',
                            $line->credit > 0 ? number_format($line->credit, 2, '.', '') : '',
                            number_format($saldo, 2, '.', '')
                        ]);
                    }
                }
            }
            
            // Write saldo akhir
            fputcsv($output, ['', '', '', 'Saldo Akhir', '', '', number_format($saldo, 2, '.', '')]);
            
            // Add empty line between accounts
            fputcsv($output, ['']);
        }
        
        // If no data found
        if (!$hasData) {
            fputcsv($output, ['Tidak ada data untuk periode yang dipilih']);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Get account transactions using the same logic as bukuBesar controller
     */
    private function getAccountTransactions($accountCode)
    {
        $entries = collect();
        
        // Build query untuk journal entries (from JournalEntry system)
        $query = DB::table('journal_entries as je')
            ->leftJoin('journal_lines as jl', 'jl.journal_entry_id', '=', 'je.id')
            ->leftJoin('coas', 'coas.id', '=', 'jl.coa_id')
            ->select([
                'je.*',
                'jl.id as line_id',
                'jl.debit',
                'jl.credit',
                'jl.memo as line_memo',
                'coas.kode_akun',
                'coas.nama_akun',
                'coas.tipe_akun'
            ])
            ->where(function($q) {
                $q->where('jl.debit', '>', 0)
                  ->orWhere('jl.credit', '>', 0);
            })
            ->where('coas.kode_akun', $accountCode)
            ->orderBy('je.tanggal','asc')
            ->orderBy('je.id','asc')
            ->orderBy('jl.id','asc');
        
        // Apply date filters
        if ($this->from) {
            $query->whereDate('je.tanggal', '>=', $this->from);
        }
        if ($this->to) {
            $query->whereDate('je.tanggal', '<=', $this->to);
        }
        
        $results = $query->get();
        
        // Group results by journal entry
        $groupedResults = $results->groupBy('id');
        
        foreach ($groupedResults as $entryId => $lines) {
            $firstLine = $lines->first();
            
            if ($lines->isEmpty()) continue;
            
            $entry = (object) [
                'id' => $firstLine->id,
                'tanggal' => $firstLine->tanggal,
                'ref_type' => $firstLine->ref_type,
                'ref_id' => $firstLine->ref_id,
                'memo' => $firstLine->memo,
                'lines' => $lines->map(function($line) {
                    return (object) [
                        'id' => $line->line_id,
                        'debit' => $line->debit,
                        'credit' => $line->credit,
                        'memo' => $line->line_memo,
                        'account_code' => $line->kode_akun,
                        'account_name' => $line->nama_akun,
                        'account_type' => $line->tipe_akun,
                        'coa' => (object) [
                            'kode_akun' => $line->kode_akun,
                            'nama_akun' => $line->nama_akun,
                            'tipe_akun' => $line->tipe_akun
                        ]
                    ];
                })
            ];
            $entries->push($entry);
        }
        
        // TAMBAHAN: Ambil data dari tabel jurnal_umum (untuk transaksi yang hanya ada di sana)
        // Exclude transactions that already exist in journal_entries to avoid duplicates
        $jurnalUmumQuery = DB::table('jurnal_umum as ju')
            ->leftJoin('coas', 'coas.id', '=', 'ju.coa_id')
            ->select([
                'ju.id',
                'ju.tanggal',
                'ju.keterangan as memo',
                'ju.referensi',
                'ju.tipe_referensi as ref_type',
                'ju.debit',
                'ju.kredit as credit',
                'coas.kode_akun',
                'coas.nama_akun',
                'coas.tipe_akun'
            ])
            ->where(function($q) {
                $q->where('ju.debit', '>', 0)
                  ->orWhere('ju.kredit', '>', 0);
            })
            ->where('coas.kode_akun', $accountCode)
            ->whereNotIn('ju.tipe_referensi', ['purchase', 'sale', 'sales_return', 'debt_payment']) // Exclude types that exist in journal_entries (penggajian should be included)
            ->orderBy('ju.tanggal','asc')
            ->orderBy('ju.id','asc');
        
        // Apply date filters for jurnal_umum as well
        if ($this->from) {
            $jurnalUmumQuery->whereDate('ju.tanggal', '>=', $this->from);
        }
        if ($this->to) {
            $jurnalUmumQuery->whereDate('ju.tanggal', '<=', $this->to);
        }
        
        $jurnalUmumResults = $jurnalUmumQuery->get();
        
        // Group jurnal_umum results by date and memo
        $jurnalUmumGrouped = $jurnalUmumResults->groupBy(function($item) {
            return $item->tanggal . '|' . $item->memo;
        });
        
        foreach ($jurnalUmumGrouped as $key => $group) {
            $firstItem = $group->first();
            
            $entry = (object) [
                'id' => 'ju_' . $firstItem->id,
                'tanggal' => $firstItem->tanggal,
                'ref_type' => $firstItem->ref_type,
                'ref_id' => null,
                'memo' => $firstItem->memo,
                'lines' => $group->map(function($item) {
                    return (object) [
                        'id' => $item->id,
                        'debit' => $item->debit,
                        'credit' => $item->credit,
                        'memo' => null,
                        'account_code' => $item->kode_akun,
                        'account_name' => $item->nama_akun,
                        'account_type' => $item->tipe_akun,
                        'coa' => (object) [
                            'kode_akun' => $item->kode_akun,
                            'nama_akun' => $item->nama_akun,
                            'tipe_akun' => $item->tipe_akun
                        ]
                    ];
                })
            ];
            $entries->push($entry);
        }
        
        // Sort all entries by date
        return $entries->sortBy('tanggal');
    }
}
