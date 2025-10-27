@extends('layouts.app')

@section('title', 'Detail Laporan Pembelian')

@section('content')
<div class="container mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Detail Laporan Pembelian #{{ $pembelian->id }}</h1>

    <div class="bg-white p-4 rounded shadow">
        <p><strong>Tanggal:</strong> {{ $pembelian->tanggal }}</p>
        <p><strong>Vendor:</strong> {{ $pembelian->vendor->nama_vendor ?? '-' }}</p>
        <p><strong>Total:</strong> Rp {{ number_format($pembelian->total, 0, ',', '.') }}</p>

        <h2 class="text-lg font-semibold mt-4 mb-2">Detail Barang</h2>
        <table class="table-auto w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1">Nama Barang</th>
                    <th class="border px-2 py-1">Qty</th>
                    <th class="border px-2 py-1">Harga</th>
                    <th class="border px-2 py-1">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pembelian->detail as $item)
                <tr>
                    <td class="border px-2 py-1">{{ $item->nama_barang }}</td>
                    <td class="border px-2 py-1 text-center">{{ $item->qty }}</td>
                    <td class="border px-2 py-1 text-right">{{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="border px-2 py-1 text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
