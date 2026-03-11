@extends('layouts.app')

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
                                    $q = \App\Models\JournalLine::where('account_id',$acc->id)->with('entry');
                                    if ($from) { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','>=',$from)); }
                                    if ($to)   { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','<=',$to)); }
                                    $row = $q->selectRaw('COALESCE(SUM(credit - debit),0) as bal')->first();
                                    $bal = (float)($row->bal ?? 0);
                                    $sumRev += $bal;
                                @endphp
                                @if($bal != 0)
                                <tr>
                                    <td class="ps-4">{{ $acc->name }}</td>
                                    <td class="text-muted small">{{ $acc->code }}</td>
                                    <td class="text-end">Rp {{ number_format($bal,0,',','.') }}</td>
                                </tr>
                                @endif
                            @endforeach
                            
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Pendapatan Usaha</td>
                                <td class="text-end fw-bold">Rp {{ number_format($sumRev,0,',','.') }}</td>
                            </tr>
                            
                            <tr>
                                <td colspan="3" class="border-0">&nbsp;</td>
                            </tr>
                            
                            <!-- BEBAN-BEBAN USAHA -->
                            <tr class="table-danger">
                                <th colspan="2" class="fw-bold text-uppercase">BEBAN-BEBAN USAHA</th>
                                <th class="text-end fw-bold"></th>
                            </tr>
                            
                            @php $sumExp = 0; @endphp
                            @foreach($expense as $acc)
                                @php
                                    $q = \App\Models\JournalLine::where('account_id',$acc->id)->with('entry');
                                    if ($from) { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','>=',$from)); }
                                    if ($to)   { $q->whereHas('entry', fn($qq)=>$qq->whereDate('tanggal','<=',$to)); }
                                    $row = $q->selectRaw('COALESCE(SUM(debit - credit),0) as bal')->first();
                                    $bal = (float)($row->bal ?? 0);
                                    $sumExp += $bal;
                                @endphp
                                @if($bal != 0)
                                <tr>
                                    <td class="ps-4">{{ $acc->name }}</td>
                                    <td class="text-muted small">{{ $acc->code }}</td>
                                    <td class="text-end">Rp {{ number_format($bal,0,',','.') }}</td>
                                </tr>
                                @endif
                            @endforeach
                            
                            <tr class="border-bottom">
                                <td colspan="2" class="fw-bold ps-4">Jumlah Beban Usaha</td>
                                <td class="text-end fw-bold">Rp {{ number_format($sumExp,0,',','.') }}</td>
                            </tr>
                            
                            <!-- LABA USAHA -->
                            <tr class="table-info fw-bold">
                                <td colspan="2">LABA USAHA</td>
                                <td class="text-end">Rp {{ number_format($sumRev - $sumExp,0,',','.') }}</td>
                            </tr>
                            
                            <tr>
                                <td colspan="3" class="border-0">&nbsp;</td>
                            </tr>
                            
                            <!-- PENDAPATAN/BEBAN DI LUAR USAHA -->
                            <tr class="table-secondary">
                                <th colspan="2" class="fw-bold text-uppercase">PENDAPATAN/BEBAN DI LUAR USAHA</th>
                                <th class="text-end fw-bold"></th>
                            </tr>
                            
                            <tr class="border-bottom">
                                <td colspan="2" class="ps-4">Pendapatan di Luar Usaha</td>
                                <td class="text-end">Rp 0</td>
                            </tr>
                            
                            <tr class="border-bottom">
                                <td colspan="2" class="ps-4">Beban di Luar Usaha</td>
                                <td class="text-end">Rp 0</td>
                            </tr>
                            
                            <!-- LABA SEBELUM PAJAK -->
                            <tr class="table-warning fw-bold">
                                <td colspan="2">LABA SEBELUM PAJAK</td>
                                <td class="text-end">Rp {{ number_format($sumRev - $sumExp,0,',','.') }}</td>
                            </tr>
                            
                            <!-- PAJAK PENGHASILAN -->
                            <tr class="table-secondary">
                                <td colspan="2" class="fw-bold ps-4">PAJAK PENGHASILAN</td>
                                <td class="text-end">Rp 0</td>
                            </tr>
                            
                            <!-- LABA BERSIH -->
                            <tr class="table-primary fw-bold">
                                <td colspan="2">LABA BERSIH</td>
                                <td class="text-end">Rp {{ number_format($laba,0,',','.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
