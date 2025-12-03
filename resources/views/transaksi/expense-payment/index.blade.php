@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Pembayaran Beban</h3>
    <a href="{{ route('transaksi.pembayaran-beban.create') }}" class="btn btn-primary">Tambah</a>
  </div>

  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>#</th><th>Tanggal</th><th>COA Beban</th><th>Nominal</th><th>Keterangan</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $r->tanggal->format('d/m/Y') }}</td>
        <td>
            @if($r->coaBeban)
                {{ $r->coaBeban->kode_akun }} - {{ $r->coaBeban->nama_akun }}
            @else
                <span class="text-danger">Akun beban tidak ditemukan ({{ $r->coa_beban_id }})</span>
            @endif
        </td>
        <td>Rp {{ number_format($r->nominal, 0, ',', '.') }}</td>
        <td>{{ $r->deskripsi }}</td>
        <td>
          <div class="btn-group" role="group">
            <a href="{{ route('transaksi.pembayaran-beban.show', $r->id) }}" class="btn btn-info btn-sm" title="Invoice">
              <i class="fas fa-file-invoice"></i>
            </a>
            <a href="{{ route('transaksi.pembayaran-beban.edit', $r->id) }}" class="btn btn-warning btn-sm" title="Edit">
              <i class="fas fa-edit"></i>
            </a>
            <form action="{{ route('transaksi.pembayaran-beban.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" class="text-center">Tidak ada data pembayaran beban</td>
      </tr>
      @endforelse
    </tbody>
  </table>

  {{ $rows->links() }}
</div>
@endsection
