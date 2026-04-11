@extends('layouts.app')

@section('title', 'Detail Slip Gaji')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Detail Slip Gaji
                </h3>
                <div>
                    <a href="{{ route('pegawai.slip-gaji.pdf', $penggajian->id) }}" class="btn btn-success me-2">
                        <i class="fas fa-file-pdf me-1"></i> Download PDF
                    </a>
                    <a href="{{ route('pegawai.slip-gaji.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>

            <!-- Slip Gaji Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <!-- Header Perusahaan -->
                    <div class="text-center mb-4 pb-3" style="border-bottom: 2px solid #007bff;">
                        <h4 class="text-primary fw-bold mb-1">UMKM COE</h4>
                        <p class="text-muted mb-0">SLIP GAJI KARYAWAN</p>
                        <small class="text-muted">
                            {{ $penggajian->tanggal_penggajian->format('d F Y') }}
                        </small>
                    </div>

                    <div class="row">
                        <!-- Informasi Pegawai -->
                        <div class="col-md-6 mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Informasi Pegawai</h5>
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td width="40%">Nama Pegawai</td>
                                    <td>: <strong>{{ $pegawai->nama }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Kode Pegawai</td>
                                    <td>: {{ $pegawai->kode_pegawai }}</td>
                                </tr>
                                <tr>
                                    <td>Jabatan</td>
                                    <td>: {{ $pegawai->jabatan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Jenis Pegawai</td>
                                    <td>: 
                                        <span class="badge {{ strtoupper($jenis) === 'btkl' ? 'bg-primary' : 'bg-secondary' }}">
                                            {{ strtoupper($jenis) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Bank</td>
                                    <td>: {{ strtoupper($pegawai->bank ?? '-') }}</td>
                                </tr>
                                <tr>
                                    <td>No. Rekening</td>
                                    <td>: {{ $pegawai->nomor_rekening ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Status Pembayaran -->
                        <div class="col-md-6 mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Status Pembayaran</h5>
                            <div class="p-3 bg-light rounded">
                                @if($penggajian->status_pembayaran === 'lunas')
                                    <div class="alert alert-success mb-0">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>Sudah Dibayar</strong>
                                        @if($penggajian->tanggal_dibayar)
                                            <br><small class="text-muted">
                                                Tanggal: {{ $penggajian->tanggal_dibayar->format('d F Y') }}
                                            </small>
                                        @endif
                                        @if($penggajian->metode_pembayaran)
                                            <br><small class="text-muted">
                                                Metode: {{ ucfirst($penggajian->metode_pembayaran) }}
                                            </small>
                                        @endif
                                    </div>
                                @elseif($penggajian->status_pembayaran === 'disetujui')
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        <strong>Disetujui</strong>
                                        <br><small class="text-muted">
                                            Menunggu pembayaran
                                        </small>
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <strong>{{ ucfirst($penggajian->status_pembayaran) }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Rincian Gaji -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Rincian Gaji</h5>
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Komponen</th>
                                        <th class="text-end">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($jenis === 'btkl')
                                        <tr>
                                            <td>Tarif per Jam</td>
                                            <td class="text-end">Rp {{ number_format($penggajian->tarif_per_jam ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Jam Kerja</td>
                                            <td class="text-end">{{ number_format($penggajian->total_jam_kerja ?? 0, 0) }} Jam</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td>Gaji Pokok</td>
                                            <td class="text-end">Rp {{ number_format($penggajian->gaji_pokok ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    
                                    <tr class="table-light">
                                        <td colspan="2"><strong>Tunjangan:</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">&nbsp;&nbsp;• Tunjangan Jabatan</td>
                                        <td class="text-end">Rp {{ number_format($penggajian->tunjangan_jabatan ?? $penggajian->tunjangan ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">&nbsp;&nbsp;• Tunjangan Transport</td>
                                        <td class="text-end">Rp {{ number_format($penggajian->tunjangan_transport ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">&nbsp;&nbsp;• Tunjangan Konsumsi</td>
                                        <td class="text-end">Rp {{ number_format($penggajian->tunjangan_konsumsi ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <td class="ps-4">&nbsp;&nbsp;Total Tunjangan</td>
                                        <td class="text-end">Rp {{ number_format($penggajian->total_tunjangan ?? $penggajian->tunjangan ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    
                                    <tr>
                                        <td>Asuransi / BPJS</td>
                                        <td class="text-end">Rp {{ number_format($penggajian->asuransi ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Bonus</td>
                                        <td class="text-end">Rp {{ number_format($penggajian->bonus ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Potongan</td>
                                        <td class="text-end text-danger">Rp {{ number_format($penggajian->potongan ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Total Gaji -->
                    <div class="card mb-4" style="background-color: #f8f9fa;">
                        <div class="card-body text-center py-4">
                            <h5 class="mb-2 text-dark fw-bold">Total Gaji Diterima</h5>
                            <h2 class="mb-0 fw-bold" style="color: #007bff; font-size: 2.5rem;">
                                Rp {{ number_format($totalGajiHitung, 0, ',', '.') }}
                            </h2>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row mt-5">
                        <div class="col-md-6">
                            <div class="text-center">
                                <small class="text-muted">Dibuat pada:</small>
                                <p class="mb-0">{{ $penggajian->created_at->format('d F Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <small class="text-muted">Dokumen ini sah dan valid</small>
                                <p class="mb-0 mt-2">
                                    <em>UMKM COE</em>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
