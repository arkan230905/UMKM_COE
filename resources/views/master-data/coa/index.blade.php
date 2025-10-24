@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4 text-light fw-bold text-center">Data COA (Chart of Account)</h1>

    <div class="text-end mb-3">
        <a href="{{ route('master-data.coa.create') }}" class="btn btn-success px-4 py-2 rounded-pill shadow-sm">
            + Tambah COA
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="card p-4 shadow-lg" style="background-color: #1f1f2f; border-radius: 20px;">
        <table class="table custom-table mb-0 text-center align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode Akun</th>
                    <th>Nama Akun</th>
                    <th>Jenis</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($coas as $coa)
                <tr class="data-row">
                    <td>{{ $coa->id }}</td>
                    <td>{{ $coa->kode_akun }}</td>
                    <td>{{ $coa->nama_akun }}</td>
                    <td>{{ $coa->jenis }}</td>
                    <td>
                        <a href="{{ route('master-data.coa.edit', $coa->id) }}" class="btn btn-warning btn-sm fw-semibold px-3 rounded-pill">Edit</a>
                        <form action="{{ route('master-data.coa.destroy', $coa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus COA ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm fw-semibold px-3 rounded-pill">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
     /* Header tabel */
.custom-table thead th {
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    font-size: 0.85rem;
    color: #e0e0ee;
    background: linear-gradient(180deg, #2a2a3a 0%, #232333 100%);
    border: none;
    padding: 14px 10px;
    border-radius: 12px;
}
    body {
        background-color: #181828;
    }

    .custom-table thead {
        background: linear-gradient(90deg, #2d2d44, #23233a);
        color: #fff;
        border-radius: 12px;
    }

    .custom-table th {
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 16px;
        border: none;
    }

    .custom-table tbody td {
        font-weight: 600;
        font-size: 0.95rem;
        color:rgb(0, 0, 0);
        padding: 18px 14px;
        border: none;
    }

    .data-row {
        background: radial-gradient(circle at top left, #29293d, #1f1f2f);
        border-radius: 16px;
        transition: all 0.25s ease;
    }

    .data-row:hover {
        background: linear-gradient(180deg, #35354a, #29293f);
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
    }

    .custom-table tbody tr + tr {
        border-top: 12px solid transparent;
    }
</style>
@endsection
