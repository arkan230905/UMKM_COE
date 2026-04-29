@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header" style="background-color: #8B4513; color: white;">
                    <h3 class="card-title mb-0">Laporan Posisi Keuangan (Neraca)</h3>
                </div>
                
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('laporan.neraca.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Bulan</label>
                                    <select name="bulan" class="form-control">
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" 
                                                {{ (isset($bulan) && $bulan == str_pad($m, 2, '0', STR_PAD_LEFT)) ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tahun</label>
                                    <select name="tahun" class="form-control">
                                        @for($y = date('Y'); $y >= 2020; $y--)
                                            <option value="{{ $y }}" {{ (isset($tahun) && $tahun == $y) ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Tampilkan
                                        </button>
                                        <a href="{{ route('laporan.neraca.export-pdf', ['bulan' => $bulan ?? date('m'), 'tahun' => $tahun ?? date('Y')]) }}" 
                                           class="btn btn-danger" target="_blank">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <a href="{{ route('laporan.neraca.export-excel', ['bulan' => $bulan ?? date('m'), 'tahun' => $tahun ?? date('Y')]) }}" 
                                           class="btn btn-success">
                                            <i class="fas fa-file-excel"></i> Excel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

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

                    <!-- Neraca Table -->
                    <div class="row">
                        <!-- ASET Column -->
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead style="background-color: #8B4513; color: white;">
                                        <tr>
                                            <th colspan="2" class="text-center">ASET</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Aset Lancar -->
                                        <tr style="background-color: #f8f9fa;">
                                            <td colspan="2"><strong>ASET LANCAR</strong></td>
                                        </tr>
                                        @forelse($neraca['aset']['lancar'] as $item)
                                        <tr>
                                            <td>{{ $item['nama_akun'] }}</td>
                                            <td class="text-right">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                                        </tr>
                                        @endforelse
                                        <tr style="background-color: #e9ecef;">
                                            <td><strong>Total Aset Lancar</strong></td>
                                            <td class="text-right"><strong>Rp {{ number_format($neraca['aset']['total_lancar'], 0, ',', '.') }}</strong></td>
                                        </tr>
                                        
                                        <!-- Aset Tidak Lancar -->
                                        <tr style="background-color: #f8f9fa;">
                                            <td colspan="2"><strong>ASET TIDAK LANCAR</strong></td>
                                        </tr>
                                        @forelse($neraca['aset']['tidak_lancar'] as $item)
                                        <tr>
                                            <td>{{ $item['nama_akun'] }}</td>
                                            <td class="text-right">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                                        </tr>
                                        @endforelse
                                        <tr style="background-color: #e9ecef;">
                                            <td><strong>Total Aset Tidak Lancar</strong></td>
                                            <td class="text-right"><strong>Rp {{ number_format($neraca['aset']['total_tidak_lancar'], 0, ',', '.') }}</strong></td>
                                        </tr>
                                        
                                        <!-- Total Aset -->
                                        <tr style="background-color: #8B4513; color: white;">
                                            <td><strong>TOTAL ASET</strong></td>
                                            <td class="text-right"><strong>Rp {{ number_format($neraca['aset']['total_aset'], 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- KEWAJIBAN DAN EKUITAS Column -->
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead style="background-color: #8B4513; color: white;">
                                        <tr>
                                            <th colspan="2" class="text-center">KEWAJIBAN DAN EKUITAS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Kewajiban -->
                                        <tr style="background-color: #f8f9fa;">
                                            <td colspan="2"><strong>KEWAJIBAN</strong></td>
                                        </tr>
                                        @forelse($neraca['kewajiban']['detail'] as $item)
                                        <tr>
                                            <td>{{ $item['nama_akun'] }}</td>
                                            <td class="text-right">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                                        </tr>
                                        @endforelse
                                        <tr style="background-color: #e9ecef;">
                                            <td><strong>Total Kewajiban</strong></td>
                                            <td class="text-right"><strong>Rp {{ number_format($neraca['kewajiban']['total'], 0, ',', '.') }}</strong></td>
                                        </tr>
                                        
                                        <!-- Ekuitas -->
                                        <tr style="background-color: #f8f9fa;">
                                            <td colspan="2"><strong>EKUITAS</strong></td>
                                        </tr>
                                        @forelse($neraca['ekuitas']['detail'] as $item)
                                        <tr>
                                            <td>{{ $item['nama_akun'] }}</td>
                                            <td class="text-right">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                                        </tr>
                                        @endforelse
                                        <tr style="background-color: #e9ecef;">
                                            <td><strong>Total Ekuitas</strong></td>
                                            <td class="text-right"><strong>Rp {{ number_format($neraca['ekuitas']['total'], 0, ',', '.') }}</strong></td>
                                        </tr>
                                        
                                        <!-- Total Kewajiban dan Ekuitas -->
                                        <tr style="background-color: #8B4513; color: white;">
                                            <td><strong>TOTAL KEWAJIBAN DAN EKUITAS</strong></td>
                                            <td class="text-right"><strong>Rp {{ number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Period Info -->
                    <div class="text-center mt-3 text-muted">
                        <small>Periode: {{ date('d/m/Y', strtotime($neraca['periode']['tanggal_awal'])) }} - {{ date('d/m/Y', strtotime($neraca['periode']['tanggal_akhir'])) }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
