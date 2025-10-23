@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Data Bahan Baku</h3>

    <div class="mb-3 text-end">
        <a href="{{ route('master-data.bahan-baku.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Bahan Baku
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-primary">
                <tr>
                    <th rowspan="2">Nama Bahan</th>
                    <th rowspan="2">Stok</th>
                    <th rowspan="2">Satuan</th>
                    <th rowspan="2">Harga Utama</th>
                    <th colspan="3">Detail Harga (Satuan Lebih Kecil)</th>
                    <th rowspan="2">Dibuat Pada</th>
                    <th rowspan="2">Diperbarui Pada</th>
                    <th rowspan="2">Aksi</th>
                </tr>
                <tr>
                    <th>Sub 1</th>
                    <th>Sub 2</th>
                    <th>Sub 3</th>
                </tr>
            </thead>

            <tbody>
                @foreach($bahanBaku as $bahan)
                    @php
                        // Format harga turunan otomatis
                        $hargaUtama = $bahan->harga_satuan;
                        $satuan = strtolower($bahan->satuan);
                        $sub1 = $sub2 = $sub3 = '-';

                        if ($satuan == 'kg') {
                            $sub1 = 'Rp ' . number_format($hargaUtama / 10, 2, ',', '.') . ' / hg';
                            $sub2 = 'Rp ' . number_format($hargaUtama / 1000, 2, ',', '.') . ' / g';
                            $sub3 = 'Rp ' . number_format($hargaUtama / 1000000, 2, ',', '.') . ' / mg';
                        } elseif ($satuan == 'liter') {
                            $sub1 = 'Rp ' . number_format($hargaUtama / 10, 2, ',', '.') . ' / dL';
                            $sub2 = 'Rp ' . number_format($hargaUtama / 100, 2, ',', '.') . ' / cL';
                            $sub3 = 'Rp ' . number_format($hargaUtama / 1000, 2, ',', '.') . ' / mL';
                        } elseif ($satuan == 'm') {
                            $sub1 = 'Rp ' . number_format($hargaUtama / 100, 2, ',', '.') . ' / cm';
                            $sub2 = 'Rp ' . number_format($hargaUtama / 1000, 2, ',', '.') . ' / mm';
                            $sub3 = '-';
                        }
                    @endphp

                    <tr>
                        <td>{{ $bahan->nama_bahan }}</td>
                        <td>{{ number_format($bahan->stok, 2, ',', '.') }}</td>
                        <td>{{ $bahan->satuan }}</td>
                        <td>Rp {{ number_format($hargaUtama, 0, ',', '.') }} / {{ $bahan->satuan }}</td>
                        <td>{{ $sub1 }}</td>
                        <td>{{ $sub2 }}</td>
                        <td>{{ $sub3 }}</td>
                        <td>{{ $bahan->created_at->format('d-m-Y H:i') }}</td>
                        <td>{{ $bahan->updated_at->format('d-m-Y H:i') }}</td>
                        <td>
                            <a href="{{ route('master-data.bahan-baku.edit', $bahan->id) }}" class="btn btn-warning btn-sm">
                                Edit
                            </a>
                            <form action="{{ route('master-data.bahan-baku.destroy', $bahan->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin ingin menghapus bahan ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
