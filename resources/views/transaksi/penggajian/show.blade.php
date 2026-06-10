@extends('layouts.app')

@section('title', 'Detail Penggajian')

@section('content')
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-file-text"></i> Detail Penggajian</h3>

    @php
        $jenis = strtolower($penggajian->pegawai->jenis_pegawai ?? 'btktl');
        $coa = \App\Models\Coa::where('kode_akun', $penggajian->coa_kasbank)->first();

        $tarifProduk = (float)($produkPayroll['tarif_produk'] ?? 0);
        $produkDihasilkan = (float)($produkPayroll['produk_dihasilkan'] ?? 0);
        $gajiDasar = (float)($produkPayroll['gaji_dasar'] ?? $penggajian->gaji_pokok ?? 0);
        $totalGajiHitung = (float)($produkPayroll['total_gaji'] ?? $penggajian->total_gaji ?? 0);
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
                            <td>: <strong>{{ $coa->nama_akun ?? $penggajian->coa_kasbank }}</strong></td>
                        </tr>
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
                                    <form action="{{ route('transaksi.penggajian.markAsPaid', $penggajian->id) }}" method="POST" class="d-inline ms-2">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Tandai penggajian ini sebagai sudah dibayar?')">
                                            <i class="fas fa-check-circle me-1"></i>Tandai Sudah Dibayar
                                        </button>
                                    </form>
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
                                <td>Gaji Dasar</td>
                                <td>: Rp {{ number_format($gajiDasar, 0, ',', '.') }}</td>
                            </tr>
                        @else
                            <tr>
                                <td width="45%">Gaji Pokok</td>
                                <td>: Rp {{ number_format($penggajian->gaji_pokok ?? 0, 0, ',', '.') }}</td>
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
                            <td>: Rp {{ number_format($penggajian->tunjangan_transport ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4">&nbsp;&nbsp;• Tunjangan Konsumsi</td>
                            <td>: Rp {{ number_format($penggajian->tunjangan_konsumsi ?? 0, 0, ',', '.') }}</td>
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
                Rp {{ number_format($totalGajiHitung, 0, ',', '.') }}
            </h2>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card border-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-end gap-2 flex-wrap">
                @if(in_array(auth()->user()->role, ['owner', 'admin']) && $penggajian->status_posting !== 'posted')
                    <a href="{{ route('transaksi.penggajian.edit', $penggajian->id) }}" class="btn btn-info">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                @endif

                <a href="{{ route('transaksi.penggajian.slip', $penggajian->id) }}" class="btn btn-success" target="_blank">
                    <i class="bi bi-file-earmark-text"></i> Lihat Slip Gaji
                </a>

                <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
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
