@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-balance-scale me-2"></i>Neraca
        </h2>
        <div class="d-flex gap-2">
            <form method="get" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label">Periode</label>
                    <input type="month" name="periode" class="form-select" value="{{ $periode }}" onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-balance-scale me-2"></i>Neraca per {{ \Carbon\Carbon::parse($periode.'-01')->isoFormat('MMMM YYYY') }}
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
                            @foreach($asetLancar as $item)
                                @php $saldo = $calculateBalance($item); @endphp
                                @if($saldo != 0)
                                <tr>
                                    <td class="ps-5">{{ $item->nama_akun }}</td>
                                    <td class="text-muted small">{{ $item->kode_akun }}</td>
                                    <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                            @endforeach
                            @if($negativeLiabilities > 0)
                            <tr>
                                <td class="ps-5">Piutang dari Overpayment</td>
                                <td class="text-muted small">-</td>
                                <td class="text-end">Rp {{ number_format($negativeLiabilities, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Aset Lancar</td>
                                <td class="text-end fw-bold">Rp {{ number_format($totalAsetLancar, 0, ',', '.') }}</td>
                            </tr>
                            
                            <!-- ASET TIDAK LANCAR -->
                            <tr>
                                <td colspan="2" class="fw-bold ps-4">ASET TIDAK LANCAR</td>
                                <td class="text-end"></td>
                            </tr>
                            @foreach($asetTidakLancar as $item)
                                @php $saldo = $calculateBalance($item); @endphp
                                @if($saldo != 0)
                                <tr>
                                    <td class="ps-5">{{ $item->nama_akun }}</td>
                                    <td class="text-muted small">{{ $item->kode_akun }}</td>
                                    <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                            @endforeach
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Aset Tidak Lancar</td>
                                <td class="text-end fw-bold">Rp {{ number_format($totalAsetTidakLancar, 0, ',', '.') }}</td>
                            </tr>
                            
                            <!-- TOTAL ASET -->
                            <tr class="table-primary fw-bold">
                                <td colspan="2">JUMLAH ASET</td>
                                <td class="text-end">Rp {{ number_format($totalAset, 0, ',', '.') }}</td>
                            </tr>
                            
                            <tr>
                                <td colspan="3" class="border-0">&nbsp;</td>
                            </tr>
                            
                            <!-- KEWAJIBAN DAN EKUITAS SECTION -->
                            <tr class="table-secondary">
                                <th colspan="2" class="fw-bold text-uppercase">KEWAJIBAN DAN EKUITAS</th>
                                <th class="text-end fw-bold"></th>
                            </tr>
                            
                            <!-- KEWAJIBAN JANGKA PENDEK -->
                            <tr>
                                <td colspan="2" class="fw-bold ps-4">KEWAJIBAN JANGKA PENDEK</td>
                                <td class="text-end"></td>
                            </tr>
                            @foreach($kewajibanPendek as $item)
                                @php $saldo = $calculateBalance($item); @endphp
                                @if($saldo > 0)
                                <tr>
                                    <td class="ps-5">{{ $item->nama_akun }}</td>
                                    <td class="text-muted small">{{ $item->kode_akun }}</td>
                                    <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                            @endforeach
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Kewajiban Jangka Pendek</td>
                                <td class="text-end fw-bold">Rp {{ number_format($totalKewajibanPendek, 0, ',', '.') }}</td>
                            </tr>
                            
                            <!-- KEWAJIBAN JANGKA PANJANG -->
                            <tr>
                                <td colspan="2" class="fw-bold ps-4">KEWAJIBAN JANGKA PANJANG</td>
                                <td class="text-end"></td>
                            </tr>
                            @foreach($kewajibanPanjang as $item)
                                @php $saldo = $calculateBalance($item); @endphp
                                @if($saldo > 0)
                                <tr>
                                    <td class="ps-5">{{ $item->nama_akun }}</td>
                                    <td class="text-muted small">{{ $item->kode_akun }}</td>
                                    <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                            @endforeach
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Kewajiban Jangka Panjang</td>
                                <td class="text-end fw-bold">Rp {{ number_format($totalKewajibanPanjang, 0, ',', '.') }}</td>
                            </tr>
                            
                            <!-- EKUITAS -->
                            <tr>
                                <td colspan="2" class="fw-bold ps-4">EKUITAS / MODAL</td>
                                <td class="text-end"></td>
                            </tr>
                            @foreach($ekuitas as $item)
                                @php $saldo = $calculateBalance($item); @endphp
                                @if($saldo != 0)
                                <tr>
                                    <td class="ps-5">{{ $item->nama_akun }}</td>
                                    <td class="text-muted small">{{ $item->kode_akun }}</td>
                                    <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                            @endforeach
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Ekuitas</td>
                                <td class="text-end fw-bold">Rp {{ number_format($totalEkuitas, 0, ',', '.') }}</td>
                            </tr>
                            
                            <!-- TOTAL KEWAJIBAN DAN EKUITAS -->
                            <tr class="table-success fw-bold">
                                <td colspan="2">JUMLAH KEWAJIBAN DAN EKUITAS</td>
                                <td class="text-end">Rp {{ number_format($totalKewajibanEkuitas, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Balance Check -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert {{ ($totalAset == $totalKewajibanEkuitas) ? 'alert-success' : 'alert-warning' }}">
                                <strong>Cek Keseimbangan:</strong>
                                Total Aset: Rp {{ number_format($totalAset, 0, ',', '.') }} |
                                Total Kewajiban + Ekuitas: Rp {{ number_format($totalKewajibanEkuitas, 0, ',', '.') }}
                                @if($totalAset == $totalKewajibanEkuitas)
                                    <i class="fas fa-check-circle ms-2"></i> <strong>SEIMBANG</strong>
                                @else
                                    <i class="fas fa-exclamation-triangle ms-2"></i> <strong>TIDAK SEIMBANG</strong>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection