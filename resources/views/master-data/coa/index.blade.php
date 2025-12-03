@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Data COA</h1>
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
            <div>
                <label class="form-label">&nbsp;</label>
                <a href="{{ route('master-data.coa.create') }}" class="btn btn-primary d-block">Tambah COA</a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="alert alert-info">
        <strong><i class="bi bi-info-circle"></i> Periode: {{ \Carbon\Carbon::parse($periode->periode.'-01')->isoFormat('MMMM YYYY') }}</strong>
        @if($periode->is_closed)
            <span class="badge bg-success ms-2">Periode Ditutup</span>
        @else
            <span class="badge bg-warning ms-2">Periode Aktif</span>
        @endif
        <p class="mb-0 mt-2 small">Saldo awal yang ditampilkan adalah saldo untuk periode yang dipilih.</p>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Kode Akun</th>
                    <th>Nama Akun</th>
                    <th>Kategori Akun</th>
                    <th>Kode Induk</th>
                    <th>Saldo Normal</th>
                    <th>Saldo Awal</th>
                    <th class="col-keterangan">Keterangan</th>
                    <th style="width:140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($coas as $coa)
                <tr>
                    <td>{{ $coa->id }}</td>
                    <td>{{ $coa->kode_akun }}</td>
                    <td>{{ $coa->nama_akun }}</td>
                    <td>{{ $coa->kategori_akun }}</td>
                    <td>
                        @if($coa->kode_induk)
                            {{ $coa->kode_induk }} - {{ \App\Models\Coa::where('kode_akun', $coa->kode_induk)->value('nama_akun') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-capitalize">{{ $coa->saldo_normal }}</td>
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
                    <td class="col-keterangan"><small class="text-muted">{{ $coa->keterangan }}</small></td>
                    <td>
                        <a href="{{ route('master-data.coa.edit', $coa->kode_akun) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('master-data.coa.destroy', $coa->kode_akun) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus COA ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

<style>
    .table-responsive { overflow-x: auto; }
    .col-keterangan { white-space: normal; min-width: 200px; max-width: 300px; }
    .table th, .table td {
        vertical-align: middle;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
</style>

<!-- No custom JS: rely on native horizontal scrollbar/trackpad like tabel Pegawai -->