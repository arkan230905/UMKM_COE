@extends('layouts.app')

@section('title', 'Laporan Posisi Keuangan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-balance-scale me-2"></i>Laporan Posisi Keuangan
        </h2>
        <div class="d-flex gap-2">
            <form method="get" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" 
                                {{ (isset($bulan) && $bulan == str_pad($m, 2, '0', STR_PAD_LEFT)) ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ (isset($tahun) && $tahun == $y) ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tampilkan
                </button>
            </form>
            <div>
                <label class="form-label">&nbsp;</label>
                <a href="{{ route('akuntansi.laporan-posisi-keuangan.pdf', ['bulan' => $bulan ?? date('m'), 'tahun' => $tahun ?? date('Y')]) }}" class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Cetak PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Balance Status Alert -->
    @if(!$neraca['neraca_seimbang'])
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Peringatan:</strong> Neraca tidak seimbang! 
        Selisih: Rp {{ number_format(abs($neraca['selisih']), 0, ',', '.') }}
    </div>
    @else
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <strong>Neraca Seimbang</strong>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-balance-scale me-2"></i>Laporan Posisi Keuangan per {{ \Carbon\Carbon::create($tahun ?? date('Y'), $bulan ?? date('m'), 1)->isoFormat('MMMM YYYY') }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <table class="table table-borderless">
                        <tbody>
                            <!-- ASET SECTION -->
                            <tr class="table-secondary">
                                <th colspan="2" class="fw-bold text-uppercase">ASET</th>
                                <th class="text-end fw-bold"></th>
                            </tr>
                            
                            <!-- ASET LANCAR -->
                            <tr>
                                <td colspan="2" class="fw-bold ps-4">ASET LANCAR</td>
                                <td class="text-end"></td>
                            </tr>
                            @forelse($neraca['aset']['lancar'] as $item)
                                <tr>
                                    <td class="ps-5">{{ $item['nama_akun'] }}</td>
                                    <td class="text-muted small">{{ $item['kode_akun'] }}</td>
                                    <td class="text-end">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="ps-5 text-muted">Tidak ada data</td>
                                </tr>
                            @endforelse
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Aset Lancar</td>
                                <td class="text-end fw-bold">Rp {{ number_format($neraca['aset']['total_lancar'], 0, ',', '.') }}</td>
                            </tr>
                            
                            <!-- ASET TIDAK LANCAR -->
                            <tr>
                                <td colspan="2" class="fw-bold ps-4">ASET TIDAK LANCAR</td>
                                <td class="text-end"></td>
                            </tr>
                            @forelse($neraca['aset']['tidak_lancar'] as $item)
                                <tr>
                                    <td class="ps-5">{{ $item['nama_akun'] }}</td>
                                    <td class="text-muted small">{{ $item['kode_akun'] }}</td>
                                    <td class="text-end">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="ps-5 text-muted">Tidak ada data</td>
                                </tr>
                            @endforelse
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Aset Tidak Lancar</td>
                                <td class="text-end fw-bold">Rp {{ number_format($neraca['aset']['total_tidak_lancar'], 0, ',', '.') }}</td>
                            </tr>
                            
                            <!-- TOTAL ASET -->
                            <tr class="table-primary fw-bold">
                                <td colspan="2">JUMLAH ASET</td>
                                <td class="text-end">Rp {{ number_format($neraca['aset']['total_aset'], 0, ',', '.') }}</td>
                            </tr>
                            
                            <tr>
                                <td colspan="3" class="border-0">&nbsp;</td>
                            </tr>
                            
                            <!-- KEWAJIBAN DAN EKUITAS SECTION -->
                            <tr class="table-secondary">
                                <th colspan="2" class="fw-bold text-uppercase">KEWAJIBAN DAN EKUITAS</th>
                                <th class="text-end fw-bold"></th>
                            </tr>
                            
                            <!-- KEWAJIBAN -->
                            <tr>
                                <td colspan="2" class="fw-bold ps-4">KEWAJIBAN</td>
                                <td class="text-end"></td>
                            </tr>
                            @forelse($neraca['kewajiban']['detail'] as $item)
                                <tr>
                                    <td class="ps-5">{{ $item['nama_akun'] }}</td>
                                    <td class="text-muted small">{{ $item['kode_akun'] }}</td>
                                    <td class="text-end">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="ps-5 text-muted">Tidak ada data</td>
                                </tr>
                            @endforelse
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Kewajiban</td>
                                <td class="text-end fw-bold">Rp {{ number_format($neraca['kewajiban']['total'], 0, ',', '.') }}</td>
                            </tr>
                            
                            <!-- EKUITAS -->
                            <tr>
                                <td colspan="2" class="fw-bold ps-4">EKUITAS / MODAL</td>
                                <td class="text-end"></td>
                            </tr>
                            @forelse($neraca['ekuitas']['detail'] as $item)
                                <tr>
                                    <td class="ps-5">{{ $item['nama_akun'] }}</td>
                                    <td class="text-muted small">{{ $item['kode_akun'] }}</td>
                                    <td class="text-end">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="ps-5 text-muted">Tidak ada data</td>
                                </tr>
                            @endforelse
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Ekuitas</td>
                                <td class="text-end fw-bold">Rp {{ number_format($neraca['ekuitas']['total'], 0, ',', '.') }}</td>
                            </tr>
                            
                            <!-- TOTAL KEWAJIBAN DAN EKUITAS -->
                            <tr class="table-success fw-bold">
                                <td colspan="2">JUMLAH KEWAJIBAN DAN EKUITAS</td>
                                <td class="text-end">Rp {{ number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection