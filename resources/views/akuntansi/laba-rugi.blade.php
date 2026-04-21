@extends('layouts.app')

@section('title', 'Laba Rugi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-line me-2"></i>Laba Rugi
        </h2>
        <form method="get" class="d-flex gap-2 align-items-end">
            <div>
                <label class="form-label">Dari</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control">
            </div>
            <div>
                <label class="form-label">Sampai</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control">
            </div>
            <div>
                <button class="btn btn-primary">Terapkan</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>Laporan Laba Rugi
                @if($from && $to)
                    Periode {{ \Carbon\Carbon::parse($from)->isoFormat('D MMMM YYYY') }} - {{ \Carbon\Carbon::parse($to)->isoFormat('D MMMM YYYY') }}
                @endif
            </h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <table class="table table-borderless">
                        <tbody>
                            <!-- PENDAPATAN USAHA -->
                            <tr class="table-success">
                                <th colspan="2" class="fw-bold text-uppercase">PENDAPATAN USAHA</th>
                                <th class="text-end fw-bold"></th>
                            </tr>
                            
                            @php $sumRev = 0; @endphp
                            @foreach($revenue as $acc)
                                @php
                                    $q = \App\Models\JournalLine::where('coa_id',$acc->id)->with('entry');
                                    if ($from) { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','>=',$from)); }
                                    if ($to)   { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','<=',$to)); }
                                    $row = $q->selectRaw('COALESCE(SUM(credit - debit),0) as bal')->first();
                                    $bal = (float)($row->bal ?? 0);
                                    $sumRev += $bal;
                                @endphp
                                @if($bal != 0)
                                <tr>
                                    <td class="ps-4">{{ $acc->nama_akun }}</td>
                                    <td class="text-muted small">{{ $acc->kode_akun }}</td>
                                    <td class="text-end">Rp {{ number_format($bal,0,',','.') }}</td>
                                </tr>
                                @endif
                            @endforeach
                            
                            @if($sumRev == 0)
                                <tr>
                                    <td colspan="3" class="text-center text-muted ps-4">
                                        <em>Belum ada data penjualan</em>
                                    </td>
                                </tr>
                            @endif
                            
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Total Penjualan</td>
                                <td class="text-end fw-bold">Rp {{ number_format($sumRev,0,',','.') }}</td>
                            </tr>
                            
                            <tr>
                                <td colspan="3" class="border-0">&nbsp;</td>
                            </tr>
                            
                            <!-- HPP (HARGA POKOK PENJUALAN) -->
                            <tr class="table-warning">
                                <th colspan="2" class="fw-bold text-uppercase">HPP (HARGA POKOK PENJUALAN)</th>
                                <th class="text-end fw-bold"></th>
                            </tr>
                            
                            @php $sumHpp = 0; @endphp
                            @foreach($hppAccounts as $acc)
                                @php
                                    $q = \App\Models\JournalLine::where('coa_id',$acc->id)->with('entry');
                                    if ($from) { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','>=',$from)); }
                                    if ($to)   { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','<=',$to)); }
                                    $row = $q->selectRaw('COALESCE(SUM(debit - credit),0) as bal')->first();
                                    $bal = (float)($row->bal ?? 0);
                                    $sumHpp += $bal;
                                @endphp
                                @if($bal != 0)
                                <tr>
                                    <td class="ps-4">{{ $acc->nama_akun }}</td>
                                    <td class="text-muted small">{{ $acc->kode_akun }}</td>
                                    <td class="text-end">Rp {{ number_format($bal,0,',','.') }}</td>
                                </tr>
                                @endif
                            @endforeach
                            
                            @if($sumHpp == 0)
                                <tr>
                                    <td colspan="3" class="text-center text-muted ps-4">
                                        <em>Belum ada transaksi HPP pada periode ini</em>
                                    </td>
                                </tr>
                            @endif
                            
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Total HPP</td>
                                <td class="text-end fw-bold">Rp {{ number_format($sumHpp,0,',','.') }}</td>
                            </tr>
                            
                            @if($sumHpp > 0)
                            <tr class="text-muted small">
                                <td colspan="3" class="ps-4">
                                    <em>👉 HPP berdasarkan jurnal penjualan yang telah dicatat</em>
                                </td>
                            </tr>
                            @endif
                            
                            <!-- LABA KOTOR -->
                            <tr class="table-info fw-bold">
                                <td colspan="2">LABA KOTOR</td>
                                <td class="text-end">
                                    @php $labaKotor = $sumRev - $sumHpp; @endphp
                                    @if($labaKotor < 0)
                                        <span class="text-danger">(Rp {{ number_format(abs($labaKotor),0,',','.') }})</span>
                                    @else
                                        Rp {{ number_format($labaKotor,0,',','.') }}
                                    @endif
                                </td>
                            </tr>
                            
                            <tr>
                                <td colspan="3" class="border-0">&nbsp;</td>
                            </tr>
                            
                            <!-- BEBAN USAHA -->
                            <tr class="table-danger">
                                <th colspan="2" class="fw-bold text-uppercase">BEBAN USAHA</th>
                                <th class="text-end fw-bold"></th>
                            </tr>
                            
                            @php $sumExp = 0; @endphp
                            @foreach($expense as $acc)
                                @php
                                    $q = \App\Models\JournalLine::where('coa_id',$acc->id)->with('entry');
                                    if ($from) { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','>=',$from)); }
                                    if ($to)   { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','<=',$to)); }
                                    $row = $q->selectRaw('COALESCE(SUM(debit - credit),0) as bal')->first();
                                    $bal = (float)($row->bal ?? 0);
                                    $sumExp += $bal;
                                @endphp
                                @if($bal != 0)
                                <tr>
                                    <td class="ps-4">
                                        {{ $acc->nama_akun }}
                                        @if(str_contains(strtolower($acc->nama_akun), 'gaji'))
                                            <small class="text-muted">(BTKTL - Biaya Tenaga Kerja Tidak Langsung)</small>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $acc->kode_akun }}</td>
                                    <td class="text-end">Rp {{ number_format($bal,0,',','.') }}</td>
                                </tr>
                                @endif
                            @endforeach
                            
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Total Beban Usaha</td>
                                <td class="text-end fw-bold">Rp {{ number_format($sumExp,0,',','.') }}</td>
                            </tr>
                            
                            <tr class="text-muted small">
                                <td colspan="3" class="ps-4">
                                    <em>👉 Catatan: Gaji di beban usaha adalah BTKTL (gaji admin, supervisor, dll), 
                                    berbeda dengan BTKL di HPP (gaji pekerja produksi langsung)</em>
                                </td>
                            </tr>
                            
                            <!-- LABA BERSIH -->
                            <tr class="table-primary fw-bold">
                                <td colspan="2">LABA BERSIH</td>
                                <td class="text-end">
                                    @php $labaBersih = $labaKotor - $sumExp; @endphp
                                    @if($labaBersih < 0)
                                        <span class="text-danger">(Rp {{ number_format(abs($labaBersih),0,',','.') }})</span>
                                    @else
                                        <span class="text-success">Rp {{ number_format($labaBersih,0,',','.') }}</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
