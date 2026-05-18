@extends('layouts.pelanggan')

@section('content')
<div style="background: white; padding: 1.5rem 0.8rem;">
    <div style="max-width: 800px; margin: 0 auto;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.1rem; font-weight: 800; color: #2d3748; margin: 0 0 0.2rem 0;">📦 Ajukan Retur Pesanan</h2>
            <p style="color: #999; margin: 0; font-size: 0.65rem;">Kembalikan produk yang tidak sesuai dan dapatkan kompensasi</p>
        </div>

        @if($errors->any())
        <div style="background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; padding: 0.6rem; margin-bottom: 1rem;">
            <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.65rem; color: #7f1d1d;">
                @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Pilih Pesanan -->
        <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0; margin-bottom: 1rem;">
            <div style="padding: 0.8rem; border-bottom: 1px solid #f0f0f0;">
                <h6 style="font-size: 0.7rem; font-weight: 800; color: #2d3748; margin: 0;">Pilih Pesanan</h6>
            </div>
            <div style="padding: 0.8rem;">
                <form method="GET" action="{{ url("/" . $perusahaan_slug . "/pelanggan/returns/create") }}" style="display: flex; gap: 0.5rem; align-items: flex-end;">
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.3rem;">Pesanan</label>
                        <select name="order_id" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem; color: #2d3748;" required>
                            <option value="">-- Pilih Pesanan --</option>
                            @foreach($orders as $o)
                            <option value="{{ $o->id }}" {{ request('order_id') == $o->id ? 'selected' : '' }}>
                                #{{ $o->nomor_order }} - Rp {{ number_format($o->total_amount, 0, ',', '.') }} ({{ ucfirst($o->status) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" style="padding: 0.4rem 0.8rem; background: #3b82f6; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 0.6rem;">Muat Item</button>
                </form>
            </div>
        </div>

        @if($order)
        <form action="{{ url("/" . $perusahaan_slug . "/pelanggan/returns") }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">

            <!-- Item Pesanan -->
            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0; margin-bottom: 1rem;">
                <div style="padding: 0.8rem; border-bottom: 1px solid #f0f0f0;">
                    <h6 style="font-size: 0.7rem; font-weight: 800; color: #2d3748; margin: 0;">📦 Item Pesanan yang Bisa Diretur</h6>
                </div>
                <div style="padding: 0.8rem; overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.65rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid #f0f0f0;">
                                <th style="text-align: left; padding: 0.4rem; font-weight: 700; color: #2d3748;">Produk</th>
                                <th style="text-align: right; padding: 0.4rem; font-weight: 700; color: #2d3748;">Harga</th>
                                <th style="text-align: center; padding: 0.4rem; font-weight: 700; color: #2d3748;">Qty Dipesan</th>
                                <th style="text-align: center; padding: 0.4rem; font-weight: 700; color: #2d3748;">Qty Retur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $it)
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 0.4rem; color: #2d3748;">{{ $it->produk->nama_produk ?? 'Produk' }}</td>
                                <td style="padding: 0.4rem; text-align: right; color: #8b6f47; font-weight: 600;">Rp {{ number_format($it->harga, 0, ',', '.') }}</td>
                                <td style="padding: 0.4rem; text-align: center; color: #2d3748;">{{ $it->qty }}</td>
                                <td style="padding: 0.4rem; text-align: center;">
                                    <input type="hidden" name="items[{{ $loop->index }}][order_item_id]" value="{{ $it->id }}">
                                    <input type="number" name="items[{{ $loop->index }}][qty]" value="0" min="0" max="{{ $it->qty }}" style="width: 50px; padding: 0.3rem; border: 1px solid #ddd; border-radius: 4px; text-align: center; font-size: 0.65rem;">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detail Pengajuan -->
            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #f0f0f0; margin-bottom: 1rem;">
                <div style="padding: 0.8rem; border-bottom: 1px solid #f0f0f0;">
                    <h6 style="font-size: 0.7rem; font-weight: 800; color: #2d3748; margin: 0;">✓ Detail Pengajuan</h6>
                </div>
                <div style="padding: 0.8rem;">
                    <div style="margin-bottom: 0.8rem;">
                        <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.3rem;">Kompensasi</label>
                        <select name="tipe_kompensasi" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem; color: #2d3748;" required>
                            <option value="barang">Tukar Barang</option>
                            <option value="uang">Refund Uang</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.3rem;">Alasan</label>
                        <textarea name="alasan" rows="3" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem; color: #2d3748; font-family: inherit; resize: vertical;" placeholder="Tuliskan alasan retur (opsional)"></textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div style="display: flex; justify-content: center; gap: 0.5rem;">
                <a href="{{ url("/" . $perusahaan_slug . "/pelanggan/dashboard") }}" style="padding: 0.5rem 1.2rem; background: #e0e0e0; color: #2d3748; border: none; border-radius: 50px; font-weight: 700; text-decoration: none; font-size: 0.65rem; cursor: pointer;">Batal</a>
                <button type="submit" style="padding: 0.5rem 1.2rem; background: #10b981; color: white; border: none; border-radius: 50px; font-weight: 700; cursor: pointer; font-size: 0.65rem;">✓ Ajukan Retur</button>
            </div>
        </form>
        @endif
    </div>
</div>

@endsection
