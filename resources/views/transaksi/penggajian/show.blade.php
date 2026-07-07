@extends('layouts.app')

@section('title', 'Detail Penggajian')

@section('content')
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-file-text"></i> Detail Penggajian</h3>

    @php
        $jenis = strtolower($penggajian->pegawai->jenis_pegawai ?? 'btktl');
        
        // Ambil metode pembayaran dari field metode_pembayaran
        $metodePembayaran = $penggajian->metode_pembayaran ?? 'transfer_bank';
        $metodePembayaranLabel = match($metodePembayaran) {
            'tunai', 'kas' => 'Tunai',
            'transfer_bank', 'transfer', 'bank' => 'Transfer Bank',
            default => ucwords(str_replace('_', ' ', $metodePembayaran))
        };

        $tarifProduk = (float)($produkPayroll['tarif_produk'] ?? 0);
        $produkDihasilkan = (float)($produkPayroll['produk_dihasilkan'] ?? 0);
        
        // Gunakan gaji_pokok dari database (yang sudah dibulatkan)
        $gajiPokok = round((float)($penggajian->gaji_pokok ?? 0));
        
        // Gunakan total_gaji dari database (yang sudah dibulatkan)
        $totalGaji = round((float)($penggajian->total_gaji ?? 0));
    @endphp

    <div class="row">
        <!-- Informasi Pegawai -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="border-bottom pb-2 mb-3">Informasi Pegawai</h5>
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td width="45%">Nama Pegawai</td>
                            <td>: <strong>{{ $penggajian->pegawai->nama ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td>Kualifikasi</td>
                            <td>: {{ $penggajian->pegawai->jabatanRelasi->nama ?? $penggajian->pegawai->jabatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Jenis Pegawai</td>
                            <td>: 
                                <span class="badge {{ strtoupper($jenis) === 'btkl' ? 'bg-info' : 'bg-secondary' }}">
                                    {{ strtoupper($jenis) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Tanggal Penggajian</td>
                            <td>: {{ \Carbon\Carbon::parse($penggajian->tanggal_penggajian)->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td>Metode Pembayaran</td>
                            <td>: <strong>{{ $metodePembayaranLabel }}</strong></td>
                        </tr>
                        @if(in_array($metodePembayaran, ['transfer_bank', 'transfer', 'bank']))
                        <tr>
                            <td colspan="2" class="p-0 pt-2 pb-2">
                                <div class="alert alert-info mb-3">
                                    <h6 class="alert-heading fw-bold mb-2" style="font-size: 0.9rem;"><i class="bi bi-bank me-1"></i> Informasi Rekening Tujuan</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted d-block" style="font-size: 0.8rem;">Bank</small>
                                            <strong style="font-size: 0.85rem;">{{ $penggajian->pegawai->bank ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block" style="font-size: 0.8rem;">No. Rekening</small>
                                            <strong style="font-size: 0.85rem;">{{ $penggajian->pegawai->nomor_rekening ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block" style="font-size: 0.8rem;">Atas Nama</small>
                                            <strong style="font-size: 0.85rem;">{{ $penggajian->pegawai->nama_rekening ?? '-' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td>Status Pembayaran</td>
                            <td>:
                                @if($penggajian->status_pembayaran === 'lunas')
                                    <span class="badge bg-success">Sudah Dibayar</span>
                                    @if($penggajian->tanggal_dibayar)
                                        <small class="text-muted">({{ \Carbon\Carbon::parse($penggajian->tanggal_dibayar)->format('d F Y') }})</small>
                                    @endif
                                @else
                                    <span class="badge bg-warning text-dark">Belum Dibayar</span>
                                    <button type="button" class="btn btn-sm btn-success ms-2" data-bs-toggle="modal" data-bs-target="#markAsPaidModal">
                                        <i class="fas fa-check-circle me-1"></i>Tandai Sudah Dibayar
                                    </button>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Rincian Gaji -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="border-bottom pb-2 mb-3">Rincian Gaji</h5>
                    <table class="table table-borderless mb-0">
                        @if($jenis === 'btkl')
                            <tr>
                                <td width="45%">Tarif per Produk</td>
                                <td>: Rp {{ number_format($tarifProduk, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Produk yang Dihasilkan</td>
                                <td>: {{ number_format($produkDihasilkan, 0, ',', '.') }} Produk</td>
                            </tr>
                            <tr>
                                <td>Gaji Pokok</td>
                                <td>: Rp {{ number_format($gajiPokok, 0, ',', '.') }}</td>
                            </tr>
                        @else
                            <tr>
                                <td width="45%">Gaji Pokok</td>
                                <td>: Rp {{ number_format($gajiPokok, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        
                        <!-- Tunjangan Detail -->
                        <tr class="table-light">
                            <td colspan="2"><strong>Tunjangan:</strong></td>
                        </tr>
                        <tr>
                            <td class="ps-4">&nbsp;&nbsp;• Tunjangan Kualifikasi</td>
                            <td>: Rp {{ number_format($penggajian->tunjangan_jabatan ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4">&nbsp;&nbsp;• Tunjangan Transport</td>
                            <td>: Rp {{ number_format($penggajian->tunjangan_transport ?? 0, 0, ',', '.') }}
                                @php
                                    $transpFull = $penggajian->tunjangan_transport_full ?? 0;
                                    $transpActual = $penggajian->tunjangan_transport ?? 0;
                                    $totalAlpha = $penggajian->total_alpha ?? 0;
                                    $totalHadir = $penggajian->total_hari_hadir ?? 0;
                                    $hariKerjaTp = $totalHadir + $totalAlpha;
                                @endphp
                                @if($transpFull > 0 && $totalAlpha > 0 && $hariKerjaTp > 0)
                                    <br><small class="text-danger">{{ number_format($transpFull, 0, ',', '.') }} x ({{ $totalHadir }}/{{ $hariKerjaTp }} hari kerja) -- dipotong karena {{ $totalAlpha }} hari alpa</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4">&nbsp;&nbsp;• Tunjangan Konsumsi</td>
                            <td>: Rp {{ number_format($penggajian->tunjangan_konsumsi ?? 0, 0, ',', '.') }}
                                @php
                                    $konsFull = $penggajian->tunjangan_konsumsi_full ?? 0;
                                @endphp
                                @if($konsFull > 0 && $totalAlpha > 0 && $hariKerjaTp > 0)
                                    <br><small class="text-danger">{{ number_format($konsFull, 0, ',', '.') }} x ({{ $totalHadir }}/{{ $hariKerjaTp }} hari kerja) -- dipotong karena {{ $totalAlpha }} hari alpa</small>
                                @endif
                            </td>
                        </tr>
                        <tr class="fw-bold">
                            <td class="ps-4">&nbsp;&nbsp;Total Tunjangan</td>
                            <td>: Rp {{ number_format($penggajian->total_tunjangan ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        
                        <tr>
                            <td>Asuransi / BPJS</td>
                            <td>: Rp {{ number_format($penggajian->asuransi ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Bonus</td>
                            <td>: Rp {{ number_format($penggajian->bonus ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Potongan</td>
                            <td>: Rp {{ number_format($penggajian->potongan ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Gaji -->
    <div class="card border-0 mb-4" style="background-color: #f8f9fa;">
        <div class="card-body text-center py-4">
            <h5 class="mb-2 text-dark fw-bold">Total Gaji</h5>
            <h2 class="mb-0 fw-bold" style="color: #333; font-size: 2.5rem;">
                Rp {{ number_format($totalGaji, 0, ',', '.') }}
            </h2>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end gap-2 mt-4 mb-4">
        <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        
        @if(in_array(auth()->user()->role, ['owner', 'admin']) && $penggajian->status_posting !== 'posted')
            <a href="{{ route('transaksi.penggajian.edit', $penggajian->id) }}" class="btn btn-info">
                <i class="bi bi-pencil"></i> Edit
            </a>
        @endif
        
        <a href="{{ route('transaksi.penggajian.slip', $penggajian->id) }}" 
           class="btn btn-success" target="_blank">
            <i class="bi bi-file-earmark-text"></i> Slip Gaji
        </a>
    </div>
    </div>
</div>

<!-- Modal Pembayaran Penggajian -->
<div class="modal fade" id="markAsPaidModal" tabindex="-1" aria-labelledby="markAsPaidModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('transaksi.penggajian.markAsPaid', $penggajian->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="markAsPaidModalLabel"><i class="bi bi-wallet2 me-2"></i>Konfirmasi Pembayaran Gaji</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        Total yang harus dibayar:<br>
                        <strong style="font-size: 1.25rem;">Rp {{ number_format($totalGaji, 0, ',', '.') }}</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="akun_sumber_dana" class="form-label">Pilih Sumber Dana ({{ $metodePembayaranLabel }}) <span class="text-danger">*</span></label>
                        <select name="akun_sumber_dana" id="akun_sumber_dana" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Akun Sumber Dana --</option>
                            @php
                                $isTransfer = in_array($metodePembayaran, ['transfer_bank', 'transfer', 'bank']);
                                $akunOptions = $isTransfer 
                                    ? \App\Helpers\AccountHelper::getBankAccountsWithBalance(auth()->id())
                                    : \App\Helpers\AccountHelper::getKasAccounts(auth()->id());
                                
                                if (!$isTransfer) {
                                    foreach($akunOptions as $akun) {
                                        $akun->saldo = \App\Helpers\AccountHelper::getCurrentBalance($akun->kode_akun, auth()->id());
                                    }
                                }
                            @endphp
                            
                            @foreach($akunOptions as $akun)
                                <option value="{{ $akun->kode_akun }}" {{ $akun->saldo < $totalGaji ? 'disabled' : '' }}>
                                    {{ $akun->nama_akun }} (Saldo: Rp {{ number_format($akun->saldo, 0, ',', '.') }})
                                    @if($akun->saldo < $totalGaji)
                                        - Saldo Tidak Cukup
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted mt-2 d-block">Pilihan di atas disesuaikan dengan metode pembayaran: <strong>{{ $metodePembayaranLabel }}</strong>.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Proses Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .navbar, .sidebar {
        display: none !important;
    }
}
</style>
@endsection
