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
                            <th class="text-center" style="width: 50px">NO</th>
                            <th>Nama Akun</th>
                            <th>Kode Akun</th>
                            <th>Tipe</th>
                            <th class="text-end">Saldo Awal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coas as $key => $coa)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-calculator text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $coa->nama_akun }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><code>{{ $coa->kode_akun }}</code></td>
                                <td>
                                    <span class="badge {{ $coa->tipe_akun == 'Asset' || $coa->tipe_akun == 'Aset' ? 'bg-success' : ($coa->tipe_akun == 'Liability' || $coa->tipe_akun == 'Kewajiban' ? 'bg-warning' : ($coa->tipe_akun == 'Equity' || $coa->tipe_akun == 'Modal' ? 'bg-info' : ($coa->tipe_akun == 'Revenue' || $coa->tipe_akun == 'Pendapatan' ? 'bg-primary' : 'bg-danger'))) }}">
                                        {{ $coa->tipe_akun }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    {{ number_format($saldoPeriode[$coa->id] ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('master-data.coa.edit', $coa->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('master-data.coa.destroy', $coa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus COA ini?')">
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
