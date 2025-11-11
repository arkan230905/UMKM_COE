<?php

namespace App\Services;

use App\Models\Retur;
use App\Models\ReturDetail;
use App\Models\ReturKompensasi;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\StockMovement;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;
use Exception;

class ReturService
{
    /**
     * Proses retur penjualan dengan kompensasi barang
     */
    public function prosesReturPenjualanBarang($data)
    {
        DB::beginTransaction();
        try {
            // 1. Buat retur
            $retur = $this->createRetur($data, 'penjualan', 'barang');

            // 2. Tambah stok produk yang diretur (penerimaan)
            $this->tambahStokProduk($retur);

            // 3. Buat jurnal penerimaan barang
            $this->buatJurnalPenerimaanBarangPenjualan($retur);

            // 4. Proses kompensasi produk baru
            if (isset($data['kompensasi_items'])) {
                $this->prosesKompensasiBarang($retur, $data['kompensasi_items'], 'produk');
            }

            // 5. Update status
            $retur->update(['status' => 'selesai']);

            DB::commit();
            return ['success' => true, 'retur' => $retur];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Proses retur penjualan dengan kompensasi uang
     */
    public function prosesReturPenjualanUang($data)
    {
        DB::beginTransaction();
        try {
            // 1. Buat retur
            $retur = $this->createRetur($data, 'penjualan', 'uang');

            // 2. Tambah stok produk yang diretur
            $this->tambahStokProduk($retur);

            // 3. Buat jurnal penerimaan barang
            $this->buatJurnalPenerimaanBarangPenjualan($retur);

            // 4. Proses kompensasi uang
            $this->prosesKompensasiUang($retur, $data['kompensasi_uang']);

            // 5. Update status
            $retur->update(['status' => 'selesai']);

            DB::commit();
            return ['success' => true, 'retur' => $retur];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Proses retur pembelian dengan kompensasi barang
     */
    public function prosesReturPembelianBarang($data)
    {
        DB::beginTransaction();
        try {
            // 1. Buat retur
            $retur = $this->createRetur($data, 'pembelian', 'barang');

            // 2. Kurangi stok bahan baku yang diretur (pengiriman)
            $this->kurangiStokBahanBaku($retur);

            // 3. Buat jurnal pengiriman barang
            $this->buatJurnalPengirimanBarangPembelian($retur);

            // 4. Proses kompensasi bahan baku baru
            if (isset($data['kompensasi_items'])) {
                $this->prosesKompensasiBarang($retur, $data['kompensasi_items'], 'bahan_baku');
            }

            // 5. Update status
            $retur->update(['status' => 'selesai']);

            DB::commit();
            return ['success' => true, 'retur' => $retur];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Proses retur pembelian dengan kompensasi uang
     */
    public function prosesReturPembelianUang($data)
    {
        DB::beginTransaction();
        try {
            // 1. Buat retur
            $retur = $this->createRetur($data, 'pembelian', 'uang');

            // 2. Kurangi stok bahan baku yang diretur
            $this->kurangiStokBahanBaku($retur);

            // 3. Buat jurnal pengiriman barang
            $this->buatJurnalPengirimanBarangPembelian($retur);

            // 4. Proses kompensasi uang
            $this->prosesKompensasiUang($retur, $data['kompensasi_uang']);

            // 5. Update status
            $retur->update(['status' => 'selesai']);

            DB::commit();
            return ['success' => true, 'retur' => $retur];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Buat retur baru
     */
    private function createRetur($data, $tipe_retur, $tipe_kompensasi)
    {
        $retur = Retur::create([
            'kode_retur' => Retur::generateKodeRetur(),
            'tanggal' => $data['tanggal'],
            'tipe_retur' => $tipe_retur,
            'referensi_id' => $data['referensi_id'] ?? null,
            'referensi_kode' => $data['referensi_kode'] ?? null,
            'tipe_kompensasi' => $tipe_kompensasi,
            'total_nilai_retur' => 0,
            'nilai_kompensasi' => 0,
            'status' => 'draft',
            'keterangan' => $data['keterangan'] ?? null,
            'created_by' => auth()->id()
        ]);

        // Buat detail retur
        $totalNilaiRetur = 0;
        foreach ($data['items'] as $item) {
            $subtotal = $item['qty'] * $item['harga_satuan'];
            $totalNilaiRetur += $subtotal;

            ReturDetail::create([
                'retur_id' => $retur->id,
                'item_type' => $item['item_type'],
                'item_id' => $item['item_id'],
                'item_nama' => $item['item_nama'],
                'qty_retur' => $item['qty'],
                'satuan' => $item['satuan'],
                'harga_satuan' => $item['harga_satuan'],
                'subtotal' => $subtotal,
                'keterangan' => $item['keterangan'] ?? null
            ]);
        }

        $retur->update(['total_nilai_retur' => $totalNilaiRetur]);

        return $retur;
    }

    /**
     * Tambah stok produk (untuk retur penjualan)
     */
    private function tambahStokProduk($retur)
    {
        foreach ($retur->details as $detail) {
            if ($detail->item_type === 'produk') {
                $produk = Produk::find($detail->item_id);
                $produk->increment('stok', $detail->qty_retur);

                // Catat stock movement
                StockMovement::create([
                    'item_type' => 'produk',
                    'item_id' => $produk->id,
                    'movement_type' => 'in',
                    'quantity' => $detail->qty_retur,
                    'reference_type' => 'retur',
                    'reference_id' => $retur->id,
                    'tanggal' => $retur->tanggal,
                    'keterangan' => 'Retur Penjualan - ' . $retur->kode_retur
                ]);
            }
        }
    }

    /**
     * Kurangi stok bahan baku (untuk retur pembelian)
     */
    private function kurangiStokBahanBaku($retur)
    {
        foreach ($retur->details as $detail) {
            if ($detail->item_type === 'bahan_baku') {
                $bahanBaku = BahanBaku::find($detail->item_id);
                
                // Validasi stok
                if ($bahanBaku->stok < $detail->qty_retur) {
                    throw new Exception("Stok {$bahanBaku->nama} tidak mencukupi untuk retur");
                }

                $bahanBaku->decrement('stok', $detail->qty_retur);

                // Catat stock movement
                StockMovement::create([
                    'item_type' => 'bahan_baku',
                    'item_id' => $bahanBaku->id,
                    'movement_type' => 'out',
                    'quantity' => $detail->qty_retur,
                    'reference_type' => 'retur',
                    'reference_id' => $retur->id,
                    'tanggal' => $retur->tanggal,
                    'keterangan' => 'Retur Pembelian - ' . $retur->kode_retur
                ]);
            }
        }
    }

    /**
     * Proses kompensasi barang
     */
    private function prosesKompensasiBarang($retur, $items, $item_type)
    {
        $totalNilaiKompensasi = 0;

        foreach ($items as $item) {
            $nilaiKompensasi = $item['qty'] * $item['harga_satuan'];
            $totalNilaiKompensasi += $nilaiKompensasi;

            // Buat record kompensasi
            ReturKompensasi::create([
                'retur_id' => $retur->id,
                'tipe_kompensasi' => 'barang',
                'item_type' => $item_type,
                'item_id' => $item['item_id'],
                'item_nama' => $item['item_nama'],
                'qty' => $item['qty'],
                'satuan' => $item['satuan'],
                'nilai_kompensasi' => $nilaiKompensasi,
                'tanggal_kompensasi' => now(),
                'status' => 'selesai',
                'keterangan' => $item['keterangan'] ?? null
            ]);

            // Update stok
            if ($retur->tipe_retur === 'penjualan') {
                // Kurangi stok produk kompensasi
                $produk = Produk::find($item['item_id']);
                $produk->decrement('stok', $item['qty']);

                StockMovement::create([
                    'item_type' => 'produk',
                    'item_id' => $produk->id,
                    'movement_type' => 'out',
                    'quantity' => $item['qty'],
                    'reference_type' => 'retur_kompensasi',
                    'reference_id' => $retur->id,
                    'tanggal' => now(),
                    'keterangan' => 'Kompensasi Retur Penjualan - ' . $retur->kode_retur
                ]);
            } else {
                // Tambah stok bahan baku kompensasi
                $bahanBaku = BahanBaku::find($item['item_id']);
                $bahanBaku->increment('stok', $item['qty']);

                StockMovement::create([
                    'item_type' => 'bahan_baku',
                    'item_id' => $bahanBaku->id,
                    'movement_type' => 'in',
                    'quantity' => $item['qty'],
                    'reference_type' => 'retur_kompensasi',
                    'reference_id' => $retur->id,
                    'tanggal' => now(),
                    'keterangan' => 'Kompensasi Retur Pembelian - ' . $retur->kode_retur
                ]);
            }
        }

        $retur->update(['nilai_kompensasi' => $totalNilaiKompensasi]);

        // Buat jurnal kompensasi barang
        $this->buatJurnalKompensasiBarang($retur);
    }

    /**
     * Proses kompensasi uang
     */
    private function prosesKompensasiUang($retur, $dataKompensasi)
    {
        ReturKompensasi::create([
            'retur_id' => $retur->id,
            'tipe_kompensasi' => 'uang',
            'nilai_kompensasi' => $dataKompensasi['nilai'],
            'metode_pembayaran' => $dataKompensasi['metode'],
            'akun_id' => $dataKompensasi['akun_id'],
            'tanggal_kompensasi' => now(),
            'status' => 'selesai',
            'keterangan' => $dataKompensasi['keterangan'] ?? null
        ]);

        $retur->update(['nilai_kompensasi' => $dataKompensasi['nilai']]);

        // Buat jurnal kompensasi uang
        $this->buatJurnalKompensasiUang($retur, $dataKompensasi);
    }

    /**
     * Buat jurnal penerimaan barang retur penjualan
     * Dr. Persediaan Produk Jadi
     * Cr. Retur Penjualan
     */
    private function buatJurnalPenerimaanBarangPenjualan($retur)
    {
        $akunPersediaanProduk = Coa::where('kode_akun', '1-1400')->first(); // Persediaan Produk Jadi
        $akunReturPenjualan = Coa::where('kode_akun', '4-1200')->first(); // Retur Penjualan

        $journalEntry = JournalEntry::create([
            'tanggal' => $retur->tanggal,
            'keterangan' => 'Penerimaan Retur Penjualan - ' . $retur->kode_retur,
            'reference_type' => 'retur',
            'reference_id' => $retur->id
        ]);

        // Debit: Persediaan Produk Jadi
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $akunPersediaanProduk->id,
            'debit' => $retur->total_nilai_retur,
            'credit' => 0
        ]);

        // Credit: Retur Penjualan
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $akunReturPenjualan->id,
            'debit' => 0,
            'credit' => $retur->total_nilai_retur
        ]);

        // Link jurnal ke retur
        DB::table('retur_jurnal_entries')->insert([
            'retur_id' => $retur->id,
            'jurnal_entry_id' => $journalEntry->id,
            'tipe_jurnal' => 'penerimaan_barang',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Buat jurnal pengiriman barang retur pembelian
     * Dr. Retur Pembelian
     * Cr. Persediaan Bahan Baku
     */
    private function buatJurnalPengirimanBarangPembelian($retur)
    {
        $akunPersediaanBahanBaku = Coa::where('kode_akun', '1-1300')->first(); // Persediaan Bahan Baku
        $akunReturPembelian = Coa::where('kode_akun', '5-1200')->first(); // Retur Pembelian

        $journalEntry = JournalEntry::create([
            'tanggal' => $retur->tanggal,
            'keterangan' => 'Pengiriman Retur Pembelian - ' . $retur->kode_retur,
            'reference_type' => 'retur',
            'reference_id' => $retur->id
        ]);

        // Debit: Retur Pembelian
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $akunReturPembelian->id,
            'debit' => $retur->total_nilai_retur,
            'credit' => 0
        ]);

        // Credit: Persediaan Bahan Baku
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $akunPersediaanBahanBaku->id,
            'debit' => 0,
            'credit' => $retur->total_nilai_retur
        ]);

        // Link jurnal ke retur
        DB::table('retur_jurnal_entries')->insert([
            'retur_id' => $retur->id,
            'jurnal_entry_id' => $journalEntry->id,
            'tipe_jurnal' => 'pengiriman_barang',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Buat jurnal kompensasi barang
     */
    private function buatJurnalKompensasiBarang($retur)
    {
        if ($retur->tipe_retur === 'penjualan') {
            // Dr. Retur Penjualan
            // Cr. Persediaan Produk Jadi
            $akunDebit = Coa::where('kode_akun', '4-1200')->first();
            $akunCredit = Coa::where('kode_akun', '1-1400')->first();
        } else {
            // Dr. Persediaan Bahan Baku
            // Cr. Retur Pembelian
            $akunDebit = Coa::where('kode_akun', '1-1300')->first();
            $akunCredit = Coa::where('kode_akun', '5-1200')->first();
        }

        $journalEntry = JournalEntry::create([
            'tanggal' => now(),
            'keterangan' => 'Kompensasi Barang Retur - ' . $retur->kode_retur,
            'reference_type' => 'retur',
            'reference_id' => $retur->id
        ]);

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $akunDebit->id,
            'debit' => $retur->nilai_kompensasi,
            'credit' => 0
        ]);

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $akunCredit->id,
            'debit' => 0,
            'credit' => $retur->nilai_kompensasi
        ]);

        DB::table('retur_jurnal_entries')->insert([
            'retur_id' => $retur->id,
            'jurnal_entry_id' => $journalEntry->id,
            'tipe_jurnal' => 'kompensasi_barang',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Buat jurnal kompensasi uang
     */
    private function buatJurnalKompensasiUang($retur, $dataKompensasi)
    {
        if ($retur->tipe_retur === 'penjualan') {
            // Dr. Retur Penjualan
            // Cr. Kas/Bank
            $akunDebit = Coa::where('kode_akun', '4-1200')->first();
            $akunCredit = Coa::find($dataKompensasi['akun_id']);
        } else {
            // Dr. Kas/Bank
            // Cr. Retur Pembelian
            $akunDebit = Coa::find($dataKompensasi['akun_id']);
            $akunCredit = Coa::where('kode_akun', '5-1200')->first();
        }

        $journalEntry = JournalEntry::create([
            'tanggal' => now(),
            'keterangan' => 'Kompensasi Uang Retur - ' . $retur->kode_retur,
            'reference_type' => 'retur',
            'reference_id' => $retur->id
        ]);

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $akunDebit->id,
            'debit' => $retur->nilai_kompensasi,
            'credit' => 0
        ]);

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $akunCredit->id,
            'debit' => 0,
            'credit' => $retur->nilai_kompensasi
        ]);

        DB::table('retur_jurnal_entries')->insert([
            'retur_id' => $retur->id,
            'jurnal_entry_id' => $journalEntry->id,
            'tipe_jurnal' => 'kompensasi_uang',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
