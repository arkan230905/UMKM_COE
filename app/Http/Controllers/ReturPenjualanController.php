<?php

namespace App\Http\Controllers;

use App\Models\ReturPenjualan;
use App\Models\DetailReturPenjualan;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturPenjualanController extends Controller
{
    public function index()
    {
        return redirect()->route('transaksi.penjualan.index');
    }

    public function detailRetur($penjualanId)
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penjualan = Penjualan::where('user_id', auth()->id())
            ->with(['penjualanDetails.produk'])
            ->findOrFail($penjualanId);
            
        if (!$penjualan->eligible_retur) {
            return redirect()->route('transaksi.penjualan.index')->with('error', 'Batas waktu pengajuan retur telah berakhir. Retur hanya dapat diajukan maksimal 5 jam setelah pembayaran berhasil.');
        }
        // 🔒 SECURITY: Filter pelanggan by perusahaan_id
        $pelanggans = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
            ->get();
        $jenisReturOptions = [
            'tukar_barang' => 'Tukar Barang',
            'refund' => 'Refund (Pengembalian Uang)',
        ];
        if ($penjualan->payment_method === 'credit') {
            $jenisReturOptions['kredit'] = 'Kredit';
        }

        $kasBankCoas = \App\Helpers\AccountHelper::getBankAccountsWithBalance(auth()->id());

        return view('transaksi.retur-penjualan.detail-retur', compact('penjualan', 'pelanggans', 'jenisReturOptions', 'kasBankCoas'));
    }

    public function show($id)
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $returPenjualan = ReturPenjualan::where('user_id', auth()->id())
            ->with(['detailReturPenjualans.produk', 'penjualan'])
            ->findOrFail($id);

        // Validasi akun yang diperlukan (for display purposes only)
        $validator = new \App\Services\JournalValidationService();
        $validation = $validator->validateReturPenjualan($returPenjualan);

        // Re-generate jurnal secara otomatis untuk memastikan akun menggunakan logic terbaru (misal: PPN bukan Hutang Gaji)
        try {
            \App\Services\JournalService::createJournalFromReturPenjualan($returPenjualan);
        } catch (\Exception $e) {
            \Log::error('Failed to auto-create journal for retur penjualan ' . $returPenjualan->id . ': ' . $e->getMessage());
        }

        // Ambil jurnal yang sudah diperbarui dari jurnal_umum, urutkan Debit di atas
        $journalLines = \App\Models\JurnalUmum::where('tipe_referensi', 'sales_return')
            ->where('referensi', (string)$returPenjualan->id)
            ->with('coa')
            ->orderByRaw('debit > 0 DESC')
            ->orderBy('id')
            ->get();

        // Transform jurnal lines untuk kompatibilitas dengan view
        $journalEntry = null;
        if ($journalLines->isNotEmpty()) {
            $journalEntry = (object) [
                'linesWithAccount' => $journalLines->map(function($line) {
                    return (object) [
                        'debit' => $line->debit,
                        'credit' => $line->kredit,
                        'memo' => $line->keterangan,
                        'coa' => $line->coa,
                    ];
                }),
                'created_at' => $journalLines->first()->created_at,
            ];
        }

        return view('transaksi.retur-penjualan.show', compact('returPenjualan', 'validation', 'journalEntry'));
    }

    public function jurnal($id)
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $returPenjualan = ReturPenjualan::where('user_id', auth()->id())
            ->with(['detailReturPenjualans.produk', 'penjualan'])
            ->findOrFail($id);

        // Validasi akun yang diperlukan
        $validator = new \App\Services\JournalValidationService();
        $validation = $validator->validateReturPenjualan($returPenjualan);

        // Re-generate jurnal secara otomatis untuk memastikan akun menggunakan logic terbaru (misal: PPN bukan Hutang Gaji)
        try {
            \App\Services\JournalService::createJournalFromReturPenjualan($returPenjualan);
        } catch (\Exception $e) {
            \Log::warning('Failed to auto-create journal for retur: ' . $e->getMessage());
        }

        // Ambil jurnal yang sudah diperbarui dari jurnal_umum, urutkan Debit di atas
        $journalLines = \App\Models\JurnalUmum::where('tipe_referensi', 'sales_return')
            ->where('referensi', (string)$returPenjualan->id)
            ->with('coa')
            ->orderByRaw('debit > 0 DESC')
            ->orderBy('id')
            ->get();

        // Transform jurnal lines untuk kompatibilitas dengan view
        $journalEntry = null;
        if ($journalLines->isNotEmpty()) {
            $journalEntry = (object) [
                'linesWithAccount' => $journalLines->map(function($line) {
                    return (object) [
                        'debit' => $line->debit,
                        'credit' => $line->kredit,
                        'memo' => $line->keterangan,
                        'coa' => $line->coa,
                    ];
                }),
                'created_at' => $journalLines->first()->created_at,
            ];
        }

        return view('transaksi.retur-penjualan.jurnal', compact('returPenjualan', 'validation', 'journalEntry'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'penjualan_id' => 'required|exists:penjualans,id',
            'jenis_retur' => 'required|in:tukar_barang,refund,kredit',
            'tanggal' => 'required|date',
            'pelanggan_id' => 'nullable|exists:users,id',
            'keterangan' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.penjualan_detail_id' => 'required|exists:penjualan_details,id',
            'details.*.qty_retur' => 'required|numeric|min:0.0001',
            'details.*.harga_barang' => 'required|numeric|min:0',
            'metode_refund' => 'required_if:jenis_retur,refund|in:kas,transfer|nullable',
            'bank_refund_id' => 'required_if:metode_refund,transfer|exists:coas,id|nullable',
            'nama_penerima_refund' => 'required_if:metode_refund,transfer|string|nullable',
            'bank_tujuan_refund' => 'required_if:metode_refund,transfer|string|nullable',
            'no_rekening_refund' => 'required_if:metode_refund,transfer|string|nullable',
            'bukti_foto' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penjualan = Penjualan::where('user_id', auth()->id())->findOrFail($request->penjualan_id);

        // 5-Hour Return Logic Restriction
        if (!$penjualan->eligible_retur) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Batas waktu pengajuan retur telah berakhir. Retur hanya dapat diajukan maksimal 5 jam setelah pembayaran berhasil.');
        }

        if ($request->jenis_retur === 'kredit' && $penjualan->payment_method !== 'credit') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Jenis retur kredit hanya bisa digunakan untuk transaksi penjualan dengan metode pembayaran kredit.');
        }

        try {
            DB::beginTransaction();

            $returPenjualan = new ReturPenjualan();
            $returPenjualan->nomor_retur = $returPenjualan->generateNomorRetur();
            $returPenjualan->tanggal = $request->tanggal;
            $returPenjualan->penjualan_id = $request->penjualan_id;
            $returPenjualan->pelanggan_id = $request->pelanggan_id ?? null;
            $returPenjualan->jenis_retur = $request->jenis_retur;
            $returPenjualan->keterangan = $request->keterangan;
            $returPenjualan->user_id = auth()->id(); // CRITICAL: Set user_id
            
            if ($request->jenis_retur === 'refund') {
                $returPenjualan->metode_refund = $request->metode_refund;
                
                if ($request->metode_refund === 'kas') {
                    // Coba cari Kas Tunai
                    $kasCoa = \App\Models\Coa::where(function($q) {
                        $q->where('user_id', auth()->id())
                          ->orWhereNull('user_id');
                    })
                    ->where(function($q) {
                        $q->where('nama_akun', 'Kas Tunai')
                          ->orWhere('nama_akun', 'Kas');
                    })
                    ->orderByRaw("FIELD(nama_akun, 'Kas Tunai', 'Kas')")
                    ->first();
                    
                    if ($kasCoa) {
                        $returPenjualan->bank_refund_id = $kasCoa->id;
                    } else {
                        // Fallback ke default bank_refund_id
                        $returPenjualan->bank_refund_id = $request->bank_refund_id;
                    }
                    $returPenjualan->bank_tujuan_refund = null;
                    $returPenjualan->no_rekening_refund = null;
                    $returPenjualan->nama_penerima_refund = null;
                } else {
                    $returPenjualan->bank_refund_id = $request->bank_refund_id;
                    $returPenjualan->bank_tujuan_refund = $request->bank_tujuan_refund;
                    $returPenjualan->no_rekening_refund = $request->no_rekening_refund;
                    $returPenjualan->nama_penerima_refund = $request->nama_penerima_refund;
                }
            }
            
            if ($request->hasFile('bukti_foto')) {
                $file = $request->file('bukti_foto');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('retur-penjualan', $filename, 'public');
                $returPenjualan->bukti_foto = $path;
            }

            
            $returPenjualan->save();

            foreach ($request->details as $detail) {
                $penjualanDetail = PenjualanDetail::find($detail['penjualan_detail_id']);

                if ($detail['qty_retur'] > $penjualanDetail->jumlah) {
                    throw new \Exception('Qty retur tidak boleh melebihi qty penjualan');
                }

                if ($request->jenis_retur === 'tukar_barang') {
                    $produk = \App\Models\Produk::find($penjualanDetail->produk_id);
                    if ($produk && (float)($produk->stok ?? 0) < (float)$detail['qty_retur']) {
                        throw new \Exception("Stok barang pengganti tidak mencukupi untuk {$produk->nama_produk}");
                    }
                }

                DetailReturPenjualan::create([
                    'retur_penjualan_id' => $returPenjualan->id,
                    'penjualan_detail_id' => $detail['penjualan_detail_id'],
                    'produk_id' => $penjualanDetail->produk_id,
                    'qty_retur' => $detail['qty_retur'],
                    'harga_barang' => $detail['harga_barang'],
                    'keterangan' => $detail['keterangan'] ?? null
                ]);
            }

            $returPenjualan->calculateTotalRetur();
            $returPenjualan->processRetur();

            // Auto-create jurnal untuk retur penjualan
            try {
                \App\Services\JournalService::createJournalFromReturPenjualan($returPenjualan);
            } catch (\Exception $e) {
                \Log::warning('Failed to create journal for retur penjualan: ' . $e->getMessage());
                // Continue even if journal creation fails
            }

            DB::commit();

            return redirect()->route('transaksi.retur-penjualan.index')
                ->with('success', 'Retur penjualan berhasil dibuat dengan nomor: ' . $returPenjualan->nomor_retur);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(ReturPenjualan $returPenjualan)
    {
        // CRITICAL: Verify ownership untuk multi-tenant isolation
        if ($returPenjualan->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }
        
        if ($returPenjualan->status !== 'belum_dibayar') {
            return redirect()->route('transaksi.retur-penjualan.index')
                ->with('error', 'Retur tidak dapat diedit karena status sudah ' . $returPenjualan->status);
        }

        $returPenjualan->load(['detailReturPenjualans.penjualanDetail.produk']);
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penjualans = Penjualan::where('user_id', auth()->id())
            ->with(['penjualanDetails.produk'])
            ->get();
        // 🔒 SECURITY: Filter pelanggan by perusahaan_id
        $pelanggans = User::where('role', 'pelanggan')
            ->where('perusahaan_id', auth()->user()->perusahaan_id)
            ->get();
        $jenisReturOptions = [
            'tukar_barang' => 'Tukar Barang',
            'refund' => 'Refund (Pengembalian Uang)',
        ];
        if ($returPenjualan->penjualan && $returPenjualan->penjualan->payment_method === 'credit') {
            $jenisReturOptions['kredit'] = 'Kredit';
        }

        $kasBankCoas = \App\Helpers\AccountHelper::getKasBankAccounts(auth()->id());

        return view('transaksi.retur-penjualan.edit', compact('returPenjualan', 'penjualans', 'pelanggans', 'jenisReturOptions', 'kasBankCoas'));
    }

    public function update(Request $request, ReturPenjualan $returPenjualan)
    {
        // CRITICAL: Verify ownership untuk multi-tenant isolation
        if ($returPenjualan->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }
        
        if ($returPenjualan->status !== 'belum_dibayar') {
            return redirect()->route('transaksi.retur-penjualan.index')
                ->with('error', 'Retur tidak dapat diedit karena status sudah ' . $returPenjualan->status);
        }

        $request->validate([
            'penjualan_id' => 'required|exists:penjualans,id',
            'jenis_retur' => 'required|in:tukar_barang,refund,kredit',
            'tanggal' => 'required|date',
            'pelanggan_id' => 'nullable|exists:users,id',
            'keterangan' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.penjualan_detail_id' => 'required|exists:penjualan_details,id',
            'details.*.qty_retur' => 'required|numeric|min:0.0001',
            'details.*.harga_barang' => 'required|numeric|min:0',
            'metode_refund' => 'required_if:jenis_retur,refund|in:kas,transfer|nullable',
            'bank_refund_id' => 'required_if:metode_refund,transfer|exists:coas,id|nullable',
            'nama_penerima_refund' => 'required_if:metode_refund,transfer|string|nullable',
            'bank_tujuan_refund' => 'required_if:metode_refund,transfer|string|nullable',
            'no_rekening_refund' => 'required_if:metode_refund,transfer|string|nullable',
            'bukti_foto' => 'nullable|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penjualan = Penjualan::where('user_id', auth()->id())->findOrFail($request->penjualan_id);
        if ($request->jenis_retur === 'kredit' && $penjualan->payment_method !== 'credit') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Jenis retur kredit hanya bisa digunakan untuk transaksi penjualan dengan metode pembayaran kredit.');
        }

        try {
            DB::beginTransaction();

            $returPenjualan->penjualan_id = $request->penjualan_id;
            $returPenjualan->tanggal = $request->tanggal;
            $returPenjualan->pelanggan_id = $request->pelanggan_id ?? null;
            $returPenjualan->jenis_retur = $request->jenis_retur;
            $returPenjualan->keterangan = $request->keterangan;
            
            if ($request->jenis_retur === 'refund') {
                $returPenjualan->metode_refund = $request->metode_refund;
                
                if ($request->metode_refund === 'kas') {
                    // Coba cari Kas Tunai
                    $kasCoa = \App\Models\Coa::where(function($q) {
                        $q->where('user_id', auth()->id())
                          ->orWhereNull('user_id');
                    })
                    ->where(function($q) {
                        $q->where('nama_akun', 'Kas Tunai')
                          ->orWhere('nama_akun', 'Kas');
                    })
                    ->orderByRaw("FIELD(nama_akun, 'Kas Tunai', 'Kas')")
                    ->first();
                    
                    if ($kasCoa) {
                        $returPenjualan->bank_refund_id = $kasCoa->id;
                    } else {
                        $returPenjualan->bank_refund_id = $request->bank_refund_id;
                    }
                    $returPenjualan->bank_tujuan_refund = null;
                    $returPenjualan->no_rekening_refund = null;
                    $returPenjualan->nama_penerima_refund = null;
                } else {
                    $returPenjualan->bank_refund_id = $request->bank_refund_id;
                    $returPenjualan->bank_tujuan_refund = $request->bank_tujuan_refund;
                    $returPenjualan->no_rekening_refund = $request->no_rekening_refund;
                    $returPenjualan->nama_penerima_refund = $request->nama_penerima_refund;
                }
            } else {
                $returPenjualan->metode_refund = null;
                $returPenjualan->bank_refund_id = null;
                $returPenjualan->bank_tujuan_refund = null;
                $returPenjualan->no_rekening_refund = null;
                $returPenjualan->nama_penerima_refund = null;
            }
            
            if ($request->hasFile('bukti_foto')) {
                // Delete old file if exists
                if ($returPenjualan->bukti_foto && \Storage::disk('public')->exists($returPenjualan->bukti_foto)) {
                    \Storage::disk('public')->delete($returPenjualan->bukti_foto);
                }
                
                $file = $request->file('bukti_foto');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('retur-penjualan', $filename, 'public');
                $returPenjualan->bukti_foto = $path;
            }
            
            $returPenjualan->save();

            $returPenjualan->detailReturPenjualans()->delete();

            foreach ($request->details as $detail) {
                $penjualanDetail = PenjualanDetail::find($detail['penjualan_detail_id']);

                if ($detail['qty_retur'] > $penjualanDetail->jumlah) {
                    throw new \Exception('Qty retur tidak boleh melebihi qty penjualan');
                }

                DetailReturPenjualan::create([
                    'retur_penjualan_id' => $returPenjualan->id,
                    'penjualan_detail_id' => $detail['penjualan_detail_id'],
                    'produk_id' => $penjualanDetail->produk_id,
                    'qty_retur' => $detail['qty_retur'],
                    'harga_barang' => $detail['harga_barang'],
                    'keterangan' => $detail['keterangan'] ?? null
                ]);
            }

            $returPenjualan->calculateTotalRetur();
            $returPenjualan->processRetur();

            // Auto-create jurnal untuk retur penjualan
            try {
                \App\Services\JournalService::createJournalFromReturPenjualan($returPenjualan);
            } catch (\Exception $e) {
                \Log::warning('Failed to create journal for retur penjualan: ' . $e->getMessage());
                // Continue even if journal creation fails
            }

            DB::commit();

            return redirect()->route('transaksi.retur-penjualan.index')
                ->with('success', 'Retur penjualan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(ReturPenjualan $returPenjualan)
    {
        // CRITICAL: Verify ownership untuk multi-tenant isolation
        if ($returPenjualan->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }
        
        try {
            DB::beginTransaction();

            $returPenjualan->detailReturPenjualans()->delete();
            $returPenjualan->delete();

            DB::commit();

            return redirect()->route('transaksi.retur-penjualan.index')
                ->with('success', 'Retur penjualan berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('transaksi.retur-penjualan.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function getPenjualanDetails($penjualanId)
    {
        // CRITICAL: Filter by user_id untuk multi-tenant isolation
        $penjualan = Penjualan::where('user_id', auth()->id())
            ->with(['penjualanDetails.produk'])
            ->find($penjualanId);

        if (!$penjualan) {
            return response()->json(['error' => 'Penjualan tidak ditemukan'], 404);
        }

        $details = $penjualan->penjualanDetails->map(function ($detail) {
            return [
                'id' => $detail->id,
                'produk_nama' => $detail->produk->nama_produk,
                'jumlah' => $detail->jumlah,
                'harga_satuan' => $detail->harga_satuan,
                'subtotal' => $detail->subtotal
            ];
        });

        return response()->json($details);
    }

    public function approveCustomerReturn(Request $request, $id)
    {
        $retur = \App\Models\Retur::where('type', 'sale')
            ->where('id', $id)
            ->firstOrFail();

        // 1. Verify ownership of the corresponding Penjualan
        $penjualan = Penjualan::where('order_id', $retur->ref_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$penjualan) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk menyetujui retur ini.');
        }

        if ($retur->status === 'approved') {
            return redirect()->back()->with('error', 'Retur ini sudah disetujui sebelumnya.');
        }

        if ($retur->status === 'rejected') {
            return redirect()->back()->with('error', 'Retur ini sudah ditolak.');
        }

        try {
            DB::beginTransaction();

            // Update status retur pelanggan menjadi approved
            $retur->status = 'approved';
            $retur->save();

            // Create ReturPenjualan (owner-facing)
            $returPenjualan = new ReturPenjualan();
            $returPenjualan->nomor_retur = $returPenjualan->generateNomorRetur();
            $returPenjualan->tanggal = $retur->tanggal ?? now();
            $returPenjualan->penjualan_id = $penjualan->id;
            $returPenjualan->pelanggan_id = $penjualan->pelanggan_id ?? null;
            $returPenjualan->jenis_retur = $retur->kompensasi === 'barang' ? 'tukar_barang' : 'refund';
            $returPenjualan->keterangan = 'Retur Pelanggan (' . $retur->memo . '): ' . $retur->alasan;
            $returPenjualan->bukti_foto = $retur->bukti_foto;
            $returPenjualan->status = 'approved';
            $returPenjualan->user_id = auth()->id(); // owner ID
            $returPenjualan->save();

            // Create DetailReturPenjualan records
            foreach ($retur->details as $detail) {
                // Find matching PenjualanDetail by produk_id
                $penjualanDetail = PenjualanDetail::where('penjualan_id', $penjualan->id)
                    ->where('produk_id', $detail->produk_id)
                    ->first();

                if (!$penjualanDetail) {
                    throw new \Exception('Detail penjualan tidak ditemukan untuk produk ' . ($detail->produk?->nama_produk ?? $detail->produk_id));
                }

                if ($detail->qty > $penjualanDetail->jumlah) {
                    throw new \Exception('Qty retur tidak boleh melebihi qty penjualan');
                }

                DetailReturPenjualan::create([
                    'retur_penjualan_id' => $returPenjualan->id,
                    'penjualan_detail_id' => $penjualanDetail->id,
                    'produk_id' => $detail->produk_id,
                    'qty_retur' => $detail->qty,
                    'harga_barang' => $detail->harga_satuan_asal,
                    'keterangan' => 'Retur Pelanggan: ' . ($retur->alasan ?? '')
                ]);
            }

            // Calculate total values and process the return (adjust stock, journals, etc.)
            $returPenjualan->calculateTotalRetur();
            $returPenjualan->processRetur();

            // Jika refund, buat jurnal entries
            if ($returPenjualan->jenis_retur === 'refund') {
                try {
                    \App\Services\JournalService::createJournalFromReturRefund($returPenjualan);
                } catch (\Exception $e) {
                    \Log::error('Gagal membuat jurnal retur refund: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Pengajuan retur berhasil disetujui.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error approving customer return: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyetujui retur: ' . $e->getMessage());
        }
    }

    public function rejectCustomerReturn(Request $request, $id)
    {
        $retur = \App\Models\Retur::where('type', 'sale')
            ->where('id', $id)
            ->firstOrFail();

        // Verify ownership
        $penjualan = Penjualan::where('order_id', $retur->ref_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$penjualan) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk menolak retur ini.');
        }

        if ($retur->status === 'approved') {
            return redirect()->back()->with('error', 'Retur ini sudah disetujui sebelumnya.');
        }

        if ($retur->status === 'rejected') {
            return redirect()->back()->with('error', 'Retur ini sudah ditolak.');
        }

        try {
            DB::beginTransaction();

            $retur->status = 'rejected';
            $retur->save();

            // Create ReturPenjualan to show in history
            $returPenjualan = new ReturPenjualan();
            $returPenjualan->nomor_retur = $returPenjualan->generateNomorRetur();
            $returPenjualan->tanggal = $retur->tanggal ?? now();
            $returPenjualan->penjualan_id = $penjualan->id;
            $returPenjualan->pelanggan_id = $penjualan->pelanggan_id ?? null;
            $returPenjualan->jenis_retur = $retur->kompensasi === 'barang' ? 'tukar_barang' : 'refund';
            $returPenjualan->keterangan = 'Retur Pelanggan Ditolak (' . $retur->memo . '): ' . $retur->alasan;
            $returPenjualan->bukti_foto = $retur->bukti_foto;
            $returPenjualan->status = 'rejected';
            $returPenjualan->user_id = auth()->id();
            $returPenjualan->save();

            DB::commit();

            return redirect()->back()->with('success', 'Pengajuan retur berhasil ditolak.');
        } catch (\Exception $e) {
            \Log::error('Error rejecting customer return: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menolak retur: ' . $e->getMessage());
        }
    }

}
