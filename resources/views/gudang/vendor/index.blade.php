@extends('layouts.gudang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Daftar Vendor</h2>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Nama Vendor</th>
                    <th>Alamat</th>
                    <th>Telepon</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $v)
                <tr>
                    <td><strong>{{ $v->nama_vendor ?? $v->nama }}</strong></td>
                    <td>{{ $v->alamat ?? '-' }}</td>
                    <td>{{ $v->telepon ?? '-' }}</td>
                    <td>{{ $v->email ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $vendors->links() }}
    </div>
</div>
@endsection
