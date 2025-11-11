@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Data COA</h1>

    <a href="{{ route('master-data.coa.create') }}" class="btn btn-primary mb-3">Tambah COA</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

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
                    <td>Rp {{ number_format((float)($coa->saldo_awal ?? 0), 0, ',', '.') }}</td>
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