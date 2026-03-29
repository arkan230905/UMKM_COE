@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Detail Pembelian</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Tanggal:</strong> {{ $pembelian->tanggal?->format('d-m-Y') }}</div>
                <div class="col-md-4"><strong>Vendor:</strong> {{ $pembelian->vendor->nama_vendor ?? '-' }}</div>
                <div class="col-md-4"><strong>Total:</strong> Rp {{ number_format($pembelian->total_harga ?? 0,0,',','.') }}</div>
                <div class="col-md-4"><strong>Pembayaran:</strong> {{ ($pembelian->payment_method ?? 'cash')==='credit' ? 'Kredit' : 'Tunai' }}</div>
            </div>
        </div>
    </div>

    <!-- Detail Bahan Baku -->
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Detail Bahan Baku</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:5%">#</th>
                            <th>Nama Bahan Baku</th>
                            <th class="text-end">Kuantitas</th>
                            <th>Satuan Pembelian</th>
                            <th class="text-end">Harga per Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalBahanBaku = 0; @endphp
                        @foreach(($pembelian->details ?? [])->where('bahan_baku_id', '!=', null) as $i => $d)
                        @php 
                            $subtotal = $d->subtotal ?? 0; // Use stored subtotal from database
                            $totalBahanBaku += $subtotal;
                        @endphp
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $d->bahanBaku ? $d->bahanBaku->nama_bahan : 'Unknown' }}</td>
                            <td class="text-end">
                                {{ number_format($d->jumlah, 0, '.', '') }}
                                @if($d->faktor_konversi && abs($d->faktor_konversi - 1) > 0.001)
                                    <br><small class="text-muted">
                                        (= {{ number_format($d->jumlah_satuan_utama, 2, ',', '.') }} {{ $d->satuan_utama }})
                                    </small>
                                @endif
                                
                                @if($d->konversiManual && $d->konversiManual->count() > 0)
                                    <div class="mt-1">
                                        <small class="text-primary"><strong>Konversi Manual:</strong></small>
                                        @foreach($d->konversiManual as $konversi)
                                            @php
                                                $jumlahSatuanUtama = $d->jumlah_satuan_utama ?? ($d->jumlah * ($d->faktor_konversi ?? 1));
                                                $faktorKonversi = $jumlahSatuanUtama > 0 ? ($konversi->jumlah_konversi / $jumlahSatuanUtama) : 0;
                                            @endphp
                                            <br><small class="text-success">
                                                = {{ number_format($konversi->jumlah_konversi, 2, ',', '.') }} {{ $konversi->satuan_nama }}
                                            </small>
                                            <br><small class="text-muted" style="font-size: 10px;">
                                                <em>Rumus: {{ number_format($jumlahSatuanUtama, 2, ',', '.') }} {{ $d->satuan_utama }} × {{ number_format($faktorKonversi, 4, ',', '.') }} {{ $konversi->satuan_nama }}/{{ $d->satuan_utama }} = {{ number_format($konversi->jumlah_konversi, 2, ',', '.') }} {{ $konversi->satuan_nama }}</em>
                                            </small>
                                        @endforeach
                                    </div>
                                @elseif($d->bahanBaku)
                                    @php
                                        $jumlahSatuanUtama = $d->jumlah_satuan_utama ?? ($d->jumlah * ($d->faktor_konversi ?? 1));
                                        $bb = $d->bahanBaku;
                                    @endphp
                                    
                                    @if($bb->sub_satuan_1_konversi && $bb->subSatuan1)
                                        <br><small class="text-success">
                                            = {{ number_format($jumlahSatuanUtama * $bb->sub_satuan_1_konversi, 2, ',', '.') }} {{ $bb->subSatuan1->nama }} (otomatis)
                                        </small>
                                    @endif
                                    
                                    @if($bb->sub_satuan_2_konversi && $bb->subSatuan2)
                                        <br><small class="text-info">
                                            = {{ number_format($jumlahSatuanUtama * $bb->sub_satuan_2_konversi, 2, ',', '.') }} {{ $bb->subSatuan2->nama }} (otomatis)
                                        </small>
                                    @endif
                                    
                                    @if($bb->sub_satuan_3_konversi && $bb->subSatuan3)
                                        <br><small class="text-warning">
                                            = {{ number_format($jumlahSatuanUtama * $bb->sub_satuan_3_konversi, 2, ',', '.') }} {{ $bb->subSatuan3->nama }} (otomatis)
                                        </small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                {{ $d->satuan_nama }}
                                @if($d->faktor_konversi && abs($d->faktor_konversi - 1) > 0.001)
                                    <br><small class="text-muted">Konversi: 1:{{ number_format($d->faktor_konversi, 2, ',', '.') }}</small>
                                @endif
                            </td>
                            <td class="text-end">Rp {{ number_format($d->harga_satuan,0,',','.') }}</td>
                            <td class="text-end">Rp {{ number_format($subtotal,0,',','.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($totalBahanBaku > 0)
                    <tfoot>
                        <tr class="table-success">
                            <th colspan="5" class="text-end">Total Bahan Baku:</th>
                            <th class="text-end">Rp {{ number_format($totalBahanBaku,0,',','.') }}</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Detail Bahan Pendukung -->
    <div class="card mb-3">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Detail Bahan Pendukung</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:5%">#</th>
                            <th>Nama Bahan Pendukung</th>
                            <th class="text-end">Kuantitas</th>
                            <th>Satuan Pembelian</th>
                            <th class="text-end">Harga per Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalBahanPendukung = 0; @endphp
                        @foreach(($pembelian->details ?? [])->where('bahan_pendukung_id', '!=', null) as $i => $d)
                        @php 
                            $subtotal = $d->subtotal ?? 0; // Use stored subtotal from database
                            $totalBahanPendukung += $subtotal;
                        @endphp
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $d->bahanPendukung ? $d->bahanPendukung->nama_bahan : 'Unknown' }}</td>
                            <td class="text-end">
                                {{ number_format($d->jumlah, 0, '.', '') }}
                                @if($d->faktor_konversi && abs($d->faktor_konversi - 1) > 0.001)
                                    <br><small class="text-muted">
                                        (= {{ number_format($d->jumlah_satuan_utama, 2, ',', '.') }} {{ $d->satuan_utama }})
                                    </small>
                                @endif
                                
                                @if($d->konversiManual && $d->konversiManual->count() > 0)
                                    <div class="mt-1">
                                        <small class="text-primary"><strong>Konversi Manual:</strong></small>
                                        @foreach($d->konversiManual as $konversi)
                                            @php
                                                $jumlahSatuanUtama = $d->jumlah_satuan_utama ?? ($d->jumlah * ($d->faktor_konversi ?? 1));
                                                $faktorKonversi = $jumlahSatuanUtama > 0 ? ($konversi->jumlah_konversi / $jumlahSatuanUtama) : 0;
                                            @endphp
                                            <br><small class="text-success">
                                                = {{ number_format($konversi->jumlah_konversi, 2, ',', '.') }} {{ $konversi->satuan_nama }}
                                            </small>
                                            <br><small class="text-muted" style="font-size: 10px;">
                                                <em>Rumus: {{ number_format($jumlahSatuanUtama, 2, ',', '.') }} {{ $d->satuan_utama }} × {{ number_format($faktorKonversi, 4, ',', '.') }} {{ $konversi->satuan_nama }}/{{ $d->satuan_utama }} = {{ number_format($konversi->jumlah_konversi, 2, ',', '.') }} {{ $konversi->satuan_nama }}</em>
                                            </small>
                                        @endforeach
                                    </div>
                                @elseif($d->bahanPendukung)
                                    @php
                                        $jumlahSatuanUtama = $d->jumlah_satuan_utama ?? ($d->jumlah * ($d->faktor_konversi ?? 1));
                                        $bp = $d->bahanPendukung;
                                    @endphp
                                    
                                    @if($bp->sub_satuan_1_konversi && $bp->subSatuan1)
                                        <br><small class="text-success">
                                            = {{ number_format($jumlahSatuanUtama * $bp->sub_satuan_1_konversi, 2, ',', '.') }} {{ $bp->subSatuan1->nama }} (otomatis)
                                        </small>
                                    @endif
                                    
                                    @if($bp->sub_satuan_2_konversi && $bp->subSatuan2)
                                        <br><small class="text-info">
                                            = {{ number_format($jumlahSatuanUtama * $bp->sub_satuan_2_konversi, 2, ',', '.') }} {{ $bp->subSatuan2->nama }} (otomatis)
                                        </small>
                                    @endif
                                    
                                    @if($bp->sub_satuan_3_konversi && $bp->subSatuan3)
                                        <br><small class="text-warning">
                                            = {{ number_format($jumlahSatuanUtama * $bp->sub_satuan_3_konversi, 2, ',', '.') }} {{ $bp->subSatuan3->nama }} (otomatis)
                                        </small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                {{ $d->satuan_nama }}
                                @if($d->faktor_konversi && abs($d->faktor_konversi - 1) > 0.001)
                                    <br><small class="text-muted">Konversi: 1:{{ number_format($d->faktor_konversi, 2, ',', '.') }}</small>
                                @endif
                            </td>
                            <td class="text-end">Rp {{ number_format($d->harga_satuan,0,',','.') }}</td>
                            <td class="text-end">Rp {{ number_format($subtotal,0,',','.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($totalBahanPendukung > 0)
                    <tfoot>
                        <tr class="table-info">
                            <th colspan="5" class="text-end">Total Bahan Pendukung:</th>
                            <th class="text-end">Rp {{ number_format($totalBahanPendukung,0,',','.') }}</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Ringkasan Pembelian</h6>
                    <div class="d-flex justify-content-between">
                        <span>Total Bahan Baku:</span>
                        <strong class="text-success">Rp {{ number_format($totalBahanBaku,0,',','.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Total Bahan Pendukung:</span>
                        <strong class="text-info">Rp {{ number_format($totalBahanPendukung,0,',','.') }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Subtotal Item:</span>
                        <strong>Rp {{ number_format(($pembelian->subtotal ?? ($totalBahanBaku + $totalBahanPendukung)),0,',','.') }}</strong>
                    </div>
                    @if(($pembelian->biaya_kirim ?? 0) > 0)
                    <div class="d-flex justify-content-between">
                        <span>Biaya Kirim:</span>
                        <strong class="text-warning">Rp {{ number_format($pembelian->biaya_kirim,0,',','.') }}</strong>
                    </div>
                    @endif
                    @if(($pembelian->ppn_nominal ?? 0) > 0)
                    <div class="d-flex justify-content-between">
                        <span>PPN ({{ $pembelian->ppn_persen ?? 0 }}%):</span>
                        <strong class="text-danger">Rp {{ number_format($pembelian->ppn_nominal,0,',','.') }}</strong>
                    </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><strong>Total Pembelian:</strong></span>
                        <strong class="text-primary fs-5">Rp {{ number_format($pembelian->total_harga ?? ($totalBahanBaku + $totalBahanPendukung),0,',','.') }}</strong>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <h6>Status Pembayaran</h6>
                    <div class="d-flex justify-content-between">
                        <span>Metode Pembayaran:</span>
                        <strong>
                            @if($pembelian->payment_method === 'credit')
                                <span class="badge bg-warning">Kredit</span>
                            @elseif($pembelian->payment_method === 'transfer')
                                <span class="badge bg-info">Transfer</span>
                            @else
                                <span class="badge bg-success">Tunai</span>
                            @endif
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Terbayar:</span>
                        <strong class="text-success">Rp {{ number_format($pembelian->terbayar ?? 0,0,',','.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Sisa Pembayaran:</span>
                        <strong class="text-danger">Rp {{ number_format($pembelian->sisa_pembayaran ?? 0,0,',','.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Status:</span>
                        <strong>
                            @if($pembelian->status === 'lunas')
                                <span class="badge bg-success">Lunas</span>
                            @else
                                <span class="badge bg-warning">Belum Lunas</span>
                            @endif
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('transaksi.pembelian.show', $pembelian->id) }}" 
                   class="btn btn-primary" 
                   title="Lihat Detail Pembelian {{ $pembelian->nomor_pembelian }}">
                    <i class="fas fa-eye me-2"></i>Detail
                </a>
                <button type="button" 
                        class="btn btn-info" 
                        data-bs-toggle="modal" 
                        data-bs-target="#journalModal"
                        title="Lihat Jurnal Pembelian {{ $pembelian->nomor_pembelian }}">
                    <i class="fas fa-book me-2"></i>Lihat Jurnal
                </button>
                <a href="{{ route('transaksi.retur-pembelian.create', ['pembelian_id' => $pembelian->id]) }}" 
                   class="btn btn-secondary" 
                   title="Retur Pembelian {{ $pembelian->nomor_pembelian }}">
                    <i class="fas fa-undo me-2"></i>Retur
                </a>
                <form action="{{ route('transaksi.pembelian.destroy', $pembelian->id) }}" 
                      method="POST" 
                      class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" 
                            title="Hapus Pembelian {{ $pembelian->nomor_pembelian }}">
                        <i class="fas fa-trash me-2"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

<!-- Journal Modal -->
<div class="modal fade" id="journalModal" tabindex="-1" aria-labelledby="journalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="journalModalLabel">
                    <i class="fas fa-book me-2"></i>Jurnal Pembelian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Tanggal</th>
                                <th>Akun</th>
                                <th>Keterangan</th>
                                <th class="text-end">Debet</th>
                                <th class="text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Get actual journal entries for this purchase
                                $journalEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
                                    ->where('ref_id', $pembelian->id)
                                    ->with('lines.coa')
                                    ->orderBy('id', 'desc')
                                    ->first();
                                
                                $totalDebit = 0;
                                $totalCredit = 0;
                            @endphp
                            
                            @if($journalEntries && $journalEntries->lines)
                                @foreach($journalEntries->lines as $line)
                                    @php
                                        $totalDebit += $line->debit;
                                        $totalCredit += $line->credit;
                                    @endphp
                                    <tr>
                                        <td>{{ $journalEntries->tanggal ? \Carbon\Carbon::parse($journalEntries->tanggal)->format('d-m-Y') : '-' }}</td>
                                        <td>
                                            @if($line->coa)
                                                <span class="badge bg-primary">{{ $line->coa->nama_akun }}</span><br>
                                                <small class="text-muted">{{ $line->coa->kode_akun }}</small>
                                            @else
                                                <span class="badge bg-secondary">COA tidak ditemukan</span>
                                            @endif
                                        </td>
                                        <td>{{ $line->memo ?? $journalEntries->memo }}</td>
                                        <td class="text-end">
                                            @if($line->debit > 0)
                                                Rp {{ number_format($line->debit, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($line->credit > 0)
                                                Rp {{ number_format($line->credit, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                
                                <!-- Total Row -->
                                <tr class="table-secondary fw-bold">
                                    <td colspan="3" class="text-end">Total:</td>
                                    <td class="text-end">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Jurnal belum dibuat untuk pembelian ini
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add loading indicator for delete form
    const deleteForm = document.querySelector('form[action*="destroy"]');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            const confirmed = confirm('Apakah Anda yakin ingin menghapus pembelian {{ $pembelian->nomor_pembelian }}?\n\nPerhatian: Data yang dihapus tidak dapat dikembalikan!');
            
            if (confirmed) {
                // Show loading indicator
                const submitButton = this.querySelector('button[type="submit"], button:not([type])');
                if (submitButton) {
                    const originalText = submitButton.innerHTML;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menghapus...';
                    submitButton.disabled = true;
                    
                    // Add overlay to prevent multiple clicks
                    const overlay = document.createElement('div');
                    overlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.5);
                        z-index: 9999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        font-size: 18px;
                    `;
                    overlay.innerHTML = '<div><i class="fas fa-spinner fa-spin me-2"></i>Menghapus data pembelian...</div>';
                    document.body.appendChild(overlay);
                }
                
                return true; // Allow form submission
            } else {
                e.preventDefault(); // Cancel form submission
                return false;
            }
        });
    }
});
</script>
@endpush

@endsection
