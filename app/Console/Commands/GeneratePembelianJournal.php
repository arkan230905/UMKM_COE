<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Services\PembelianJournalService;

class GeneratePembelianJournal extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pembelian:generate-journal 
                            {--id= : ID pembelian tertentu}
                            {--from= : Tanggal mulai (Y-m-d)}
                            {--to= : Tanggal akhir (Y-m-d)}
                            {--force : Regenerate jurnal yang sudah ada}
                            {--dry-run : Hanya tampilkan preview tanpa membuat jurnal}';

    /**
     * The console command description.
     */
    protected $description = 'Generate jurnal umum untuk transaksi pembelian';

    protected $journalService;

    public function __construct(PembelianJournalService $journalService)
    {
        parent::__construct();
        $this->journalService = $journalService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== GENERATE JURNAL PEMBELIAN ===');
        $this->newLine();

        $pembelianId = $this->option('id');
        $from = $this->option('from');
        $to = $this->option('to');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        if ($pembelianId) {
            $this->generateSinglePembelian($pembelianId, $force, $dryRun);
        } else {
            $this->generateMultiplePembelian($from, $to, $force, $dryRun);
        }

        return 0;
    }

    private function generateSinglePembelian($id, $force, $dryRun)
    {
        $pembelian = Pembelian::with([
            'details.bahanBaku',
            'details.bahanPendukung',
            'vendor'
        ])->find($id);

        if (!$pembelian) {
            $this->error("Pembelian dengan ID {$id} tidak ditemukan!");
            return;
        }

        $this->info("📦 Pembelian: {$pembelian->nomor_pembelian}");
        $this->info("📅 Tanggal: {$pembelian->tanggal}");
        $this->info("🏪 Vendor: " . ($pembelian->vendor->nama_vendor ?? 'N/A'));
        $this->info("💰 Total: Rp " . number_format($pembelian->total_harga, 0, ',', '.'));
        $this->info("💳 Pembayaran: {$pembelian->payment_method}");
        $this->newLine();

        if ($dryRun) {
            $this->previewJournal($pembelian);
        } else {
            $this->createJournal($pembelian, $force);
        }
    }

    private function generateMultiplePembelian($from, $to, $force, $dryRun)
    {
        $query = Pembelian::with([
            'details.bahanBaku',
            'details.bahanPendukung', 
            'vendor'
        ]);

        if ($from) {
            $query->whereDate('tanggal', '>=', $from);
        }
        if ($to) {
            $query->whereDate('tanggal', '<=', $to);
        }

        $pembelians = $query->orderBy('tanggal', 'asc')->get();

        if ($pembelians->isEmpty()) {
            $this->warn('Tidak ada pembelian yang ditemukan!');
            return;
        }

        $this->info("Ditemukan {$pembelians->count()} pembelian");
        $this->newLine();

        if (!$force && !$this->confirm('Lanjutkan generate jurnal?')) {
            $this->info('Dibatalkan.');
            return;
        }

        $processed = 0;
        $errors = 0;

        foreach ($pembelians as $pembelian) {
            try {
                $this->line("Processing: {$pembelian->nomor_pembelian}");
                
                if ($dryRun) {
                    $this->previewJournal($pembelian);
                } else {
                    $this->createJournal($pembelian, $force);
                }
                
                $processed++;
            } catch (\Exception $e) {
                $this->error("Error: {$pembelian->nomor_pembelian} - " . $e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->info("✅ Selesai! Processed: {$processed}, Errors: {$errors}");
    }

    private function previewJournal(Pembelian $pembelian)
    {
        $this->info("📋 PREVIEW JURNAL: {$pembelian->nomor_pembelian}");
        
        $tableData = [];
        
        // Persediaan (Debit)
        foreach ($pembelian->details as $detail) {
            $amount = $detail->jumlah * $detail->harga_satuan;
            $coaCode = $this->getCoaCodeForItem($detail);
            
            $tableData[] = [
                $coaCode,
                $this->getCoaNameForItem($detail),
                'Rp ' . number_format($amount, 0, ',', '.'),
                '-'
            ];
        }
        
        // PPN Masukan (Debit)
        if ($pembelian->ppn_nominal > 0) {
            $tableData[] = [
                '1130',
                'PPN Masukan',
                'Rp ' . number_format($pembelian->ppn_nominal, 0, ',', '.'),
                '-'
            ];
        }
        
        // Biaya Kirim (Debit)
        if ($pembelian->biaya_kirim > 0) {
            $tableData[] = [
                '5111',
                'Biaya Angkut Pembelian',
                'Rp ' . number_format($pembelian->biaya_kirim, 0, ',', '.'),
                '-'
            ];
        }
        
        // Kas/Bank/Utang (Credit)
        $creditInfo = $this->getCreditInfo($pembelian);
        $tableData[] = [
            $creditInfo['code'],
            $creditInfo['name'],
            '-',
            'Rp ' . number_format($pembelian->total_harga, 0, ',', '.')
        ];
        
        $this->table(['Kode COA', 'Nama Akun', 'Debit', 'Credit'], $tableData);
        $this->newLine();
    }

    private function createJournal(Pembelian $pembelian, $force)
    {
        // Cek apakah sudah ada jurnal
        $existingJournal = \App\Models\JournalEntry::where('ref_type', 'purchase')
            ->where('ref_id', $pembelian->id)
            ->exists();

        if ($existingJournal && !$force) {
            $this->warn("⚠️  Jurnal sudah ada untuk {$pembelian->nomor_pembelian}. Gunakan --force untuk regenerate.");
            return;
        }

        try {
            $journal = $this->journalService->createJournalFromPembelian($pembelian);
            
            if ($journal) {
                $this->info("✅ Jurnal berhasil dibuat: {$pembelian->nomor_pembelian} (Journal ID: {$journal->id})");
            } else {
                $this->warn("⚠️  Jurnal tidak dibuat untuk {$pembelian->nomor_pembelian} (mungkin tidak ada detail)");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error: {$pembelian->nomor_pembelian} - " . $e->getMessage());
            throw $e;
        }
    }

    private function getCoaCodeForItem($detail): string
    {
        if ($detail->bahan_baku_id && $detail->bahanBaku && $detail->bahanBaku->coa_persediaan_id) {
            $coa = \App\Models\Coa::find($detail->bahanBaku->coa_persediaan_id);
            return $coa ? $coa->kode_akun : '1104';
        }
        
        if ($detail->bahan_pendukung_id && $detail->bahanPendukung && $detail->bahanPendukung->coa_persediaan_id) {
            $coa = \App\Models\Coa::find($detail->bahanPendukung->coa_persediaan_id);
            return $coa ? $coa->kode_akun : '1107';
        }
        
        return $detail->bahan_baku_id ? '1104' : '1107';
    }

    private function getCoaNameForItem($detail): string
    {
        if ($detail->bahan_baku_id && $detail->bahanBaku && $detail->bahanBaku->coa_persediaan_id) {
            $coa = \App\Models\Coa::find($detail->bahanBaku->coa_persediaan_id);
            return $coa ? $coa->nama_akun : 'Persediaan Bahan Baku';
        }
        
        if ($detail->bahan_pendukung_id && $detail->bahanPendukung && $detail->bahanPendukung->coa_persediaan_id) {
            $coa = \App\Models\Coa::find($detail->bahanPendukung->coa_persediaan_id);
            return $coa ? $coa->nama_akun : 'Persediaan Bahan Pendukung';
        }
        
        return $detail->bahan_baku_id ? 'Persediaan Bahan Baku' : 'Persediaan Bahan Pendukung';
    }

    private function getCreditInfo(Pembelian $pembelian): array
    {
        switch ($pembelian->payment_method) {
            case 'cash':
                return ['code' => '1120', 'name' => 'Kas'];
            case 'transfer':
                return ['code' => '1121', 'name' => 'Kas di Bank'];
            case 'credit':
            default:
                return ['code' => '2110', 'name' => 'Utang Usaha'];
        }
    }
}