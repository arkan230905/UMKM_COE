@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-calculator me-2"></i>COA
        </h2>
        <div class="d-flex gap-2">
            <form method="get" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label">Pilih Periode</label>
                    <select name="period_id" class="form-select" onchange="this.form.submit()" style="min-width: 200px;">
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" {{ $periode && $periode->id == $p->id ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($p->periode.'-01')->isoFormat('MMMM YYYY') }}
                                {{ $p->is_closed ? '✓' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
            <a href="{{ route('master-data.coa.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah COA
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar COA (Chart of Accounts)
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Kategori Akun</th>
                            <th>Kode Induk</th>
                            <th>Saldo Normal</th>
                            <th>Saldo Awal</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coas as $key => $coa)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td><code>{{ $coa->kode_akun }}</code></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-calculator text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">
                                                {{ $coa->nama_akun }}
                                                @if($coa->is_akun_header)
                                                    <span class="badge bg-secondary ms-1">Header</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">ID: {{ $coa->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $coa->kategori_akun }}</td>
                                <td>
                                    @if($coa->kode_induk)
                                        <span class="badge bg-secondary">{{ $coa->kode_induk }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-capitalize">
                                    @php
                                        $saldoNormal = strtolower($coa->saldo_normal);
                                    @endphp
                                    <span class="badge {{ $saldoNormal == 'debit' ? 'bg-success' : 'bg-warning' }}">
                                        {{ $saldoNormal == 'debit' ? 'debit' : 'credit' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $saldo = $saldoPeriode[$coa->kode_akun] ?? 0;
                                    @endphp
                                    <span class="{{ $saldo != ($coa->saldo_awal ?? 0) ? 'text-primary fw-bold' : '' }}">
                                        Rp {{ number_format((float)$saldo, 0, ',', '.') }}
                                    </span>
                                    @if($saldo != ($coa->saldo_awal ?? 0))
                                        <small class="text-muted d-block">(Default: Rp {{ number_format((float)($coa->saldo_awal ?? 0), 0, ',', '.') }})</small>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $coa->keterangan }}</small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('master-data.coa.edit', $coa->kode_akun) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('master-data.coa.destroy', $coa->kode_akun) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus COA ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
