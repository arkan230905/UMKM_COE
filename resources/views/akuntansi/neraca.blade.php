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
            <div class="row">
                <!-- ASET -->
                <div class="col-md-6">
                    <h6 class="fw-bold text-primary mb-3">ASET</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Akun</th>
                                    <th class="text-end">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aset as $item)
                                    @php $saldo = $calculateBalance($item); @endphp
                                    @if($saldo != 0)
                                    <tr>
                                        <td><code>{{ $item->kode_akun }}</code></td>
                                        <td>{{ $item->nama_akun }}</td>
                                        <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                                <tr class="table-primary fw-bold">
                                    <td colspan="2">TOTAL ASET</td>
                                    <td class="text-end">Rp {{ number_format($totalAset, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- KEWAJIBAN & MODAL -->
                <div class="col-md-6">
                    <!-- KEWAJIBAN -->
                    <h6 class="fw-bold text-warning mb-3">KEWAJIBAN</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Akun</th>
                                    <th class="text-end">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kewajiban as $item)
                                    @php $saldo = $calculateBalance($item); @endphp
                                    @if($saldo != 0)
                                    <tr>
                                        <td><code>{{ $item->kode_akun }}</code></td>
                                        <td>{{ $item->nama_akun }}</td>
                                        <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                                <tr class="table-warning fw-bold">
                                    <td colspan="2">TOTAL KEWAJIBAN</td>
                                    <td class="text-end">Rp {{ number_format($totalKewajiban, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- MODAL -->
                    <h6 class="fw-bold text-success mb-3">MODAL</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Akun</th>
                                    <th class="text-end">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modal as $item)
                                    @php $saldo = $calculateBalance($item); @endphp
                                    @if($saldo != 0)
                                    <tr>
                                        <td><code>{{ $item->kode_akun }}</code></td>
                                        <td>{{ $item->nama_akun }}</td>
                                        <td class="text-end">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                                <tr class="table-success fw-bold">
                                    <td colspan="2">TOTAL MODAL</td>
                                    <td class="text-end">Rp {{ number_format($totalModal, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- TOTAL KEWAJIBAN + MODAL -->
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <tbody>
                                <tr class="table-dark fw-bold">
                                    <td colspan="2">TOTAL KEWAJIBAN + MODAL</td>
                                    <td class="text-end">Rp {{ number_format($totalKewajiban + $totalModal, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Balance Check -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert {{ ($totalAset == ($totalKewajiban + $totalModal)) ? 'alert-success' : 'alert-warning' }}">
                        <strong>Cek Keseimbangan:</strong>
                        Total Aset: Rp {{ number_format($totalAset, 0, ',', '.') }} |
                        Total Kewajiban + Modal: Rp {{ number_format($totalKewajiban + $totalModal, 0, ',', '.') }}
                        @if($totalAset == ($totalKewajiban + $totalModal))
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
@endsection