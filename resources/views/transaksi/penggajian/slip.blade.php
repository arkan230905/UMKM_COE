@extends('layouts.app')

@section('title', 'Slip Gaji - ' . $penggajian->pegawai->nama)

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h3 class="text-dark"><i class="bi bi-file-earmark-pdf"></i> Slip Gaji</h3>
        <div>
            <a href="{{ route('transaksi.penggajian.slip-pdf', $penggajian->id) }}" class="btn btn-sm btn-danger">
                <i class="bi bi-download"></i> Download PDF
            </a>
            <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Slip Gaji Card -->
    <div class="card bg-white border-0 shadow-lg">
        <div class="card-body p-5" style="background-color: #f8f9fa;">
            <!-- Header Slip -->
            <div class="text-center border-bottom pb-4 mb-4">
                <h3 class="fw-bold text-dark" style="font-size: 28px; letter-spacing: 1px;">SLIP GAJI</h3>
                <p class="text-dark mb-0" style="font-size: 14px;">Periode: <strong>{{ $penggajian->tanggal_penggajian->format('d F Y') }}</strong></p>
            </div>

            <!-- Data Pegawai -->
            <div class="row mb-4 bg-white p-3 rounded">
                <div class="col-md-6">
                    <p class="text-dark small mb-1" style="font-weight: 600;">Nama Pegawai</p>
                    <p class="text-dark fw-bold" style="font-size: 16px;">{{ $penggajian->pegawai->nama }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-dark small mb-1" style="font-weight: 600;">Kode Pegawai</p>
                    <p class="text-dark fw-bold" style="font-size: 16px;">{{ $penggajian->pegawai->kode_pegawai ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-dark small mb-1" style="font-weight: 600;">Jabatan</p>
                    <p class="text-dark fw-bold" style="font-size: 16px;">{{ $penggajian->pegawai->jabatan ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-dark small mb-1" style="font-weight: 600;">Jenis Pegawai</p>
                    <p class="text-dark fw-bold" style="font-size: 16px;">
                        @if($slipData['jenis_pegawai'] === 'btkl')
                            BTKL (Borongan/Tarif)
                        @else
                            BTKTL (Tetap)
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="text-dark small mb-1" style="font-weight: 600;">Bank</p>
                    <p class="text-dark fw-bold" style="font-size: 16px;">{{ $penggajian->pegawai->bank ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-dark small mb-1" style="font-weight: 600;">Nomor Rekening</p>
                    <p class="text-dark fw-bold" style="font-size: 16px;">{{ $penggajian->pegawai->nomor_rekening ?? '-' }}</p>
                </div>
            </div>

            <!-- Rincian Pendapatan -->
            <div class="mb-4 bg-white p-3 rounded">
                <h6 class="fw-bold border-bottom pb-3 mb-3 text-dark" style="font-size: 15px; color: #0066cc !important;">📊 RINCIAN PENDAPATAN</h6>
                <table class="table table-sm table-borderless">
                    <tbody>
                        @foreach($slipData['pendapatan'] as $item)
                            <tr>
                                <td>
                                    <p class="mb-0 text-dark fw-bold">{{ $item['label'] }}</p>
                                    @if(isset($item['tarif']))
                                        <small class="text-dark" style="font-size: 12px;">
                                            Rp {{ number_format($item['tarif'], 0, ',', '.') }} × {{ (int)$item['unit'] }} jam
                                        </small>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-dark" style="font-size: 15px;">Rp {{ number_format($item['nilai'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="border-top pt-3 d-flex justify-content-between fw-bold text-dark" style="font-size: 16px;">
                    <span>Total Pendapatan</span>
                    <span style="color: #0066cc; font-size: 18px;">Rp {{ number_format($slipData['total_pendapatan'], 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Rincian Potongan -->
            @if(count($slipData['potongan']) > 0)
                <div class="mb-4 bg-white p-3 rounded">
                    <h6 class="fw-bold border-bottom pb-3 mb-3 text-dark" style="font-size: 15px; color: #cc0000 !important;">📉 RINCIAN POTONGAN</h6>
                    <table class="table table-sm table-borderless">
                        <tbody>
                            @foreach($slipData['potongan'] as $item)
                                <tr>
                                    <td class="text-dark fw-bold">{{ $item['label'] }}</td>
                                    <td class="text-end fw-bold text-dark" style="font-size: 15px;">Rp {{ number_format($item['nilai'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="border-top pt-3 d-flex justify-content-between fw-bold text-dark" style="font-size: 16px;">
                        <span>Total Potongan</span>
                        <span style="color: #cc0000; font-size: 18px;">Rp {{ number_format($slipData['total_potongan'], 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif

            <!-- Total Akhir -->
            <div class="alert alert-success mb-4 p-4" style="background-color: #d4edda !important; border: 2px solid #28a745;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark" style="font-size: 18px;">💰 GAJI BERSIH</h5>
                    <h4 class="mb-0 fw-bold" style="color: #28a745; font-size: 24px;">Rp {{ number_format($slipData['total_akhir'], 0, ',', '.') }}</h4>
                </div>
            </div>

            <!-- Informasi Tambahan -->
            <div class="alert alert-light p-3 mb-0 text-dark" style="font-size: 12px; border-left: 4px solid #0066cc;">
                <p class="mb-1"><strong>Catatan:</strong> Slip gaji ini dicetak pada {{ now()->format('d F Y H:i:s') }}</p>
                <p class="mb-0">Dokumen ini merupakan bukti resmi pembayaran gaji karyawan.</p>
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <div class="text-center mt-4 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg">
            <i class="bi bi-printer"></i> Cetak Slip Gaji
        </button>
    </div>
</div>

<style>
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: white;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>
@endsection
