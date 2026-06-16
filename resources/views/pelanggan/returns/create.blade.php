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

        @if(session('error'))
        <div style="background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; padding: 0.6rem; margin-bottom: 1rem; font-size: 0.65rem; color: #7f1d1d;">
            {{ session('error') }}
        </div>
        @endif

        @if(session('success'))
        <div style="background: #dcfce7; border: 1px solid #86efac; border-radius: 8px; padding: 0.6rem; margin-bottom: 1rem; font-size: 0.65rem; color: #166534;">
            {{ session('success') }}
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
        <form action="{{ route('pelanggan.returns.store', ['perusahaan_slug' => request()->route('perusahaan_slug')]) }}" method="POST" enctype="multipart/form-data">
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
                                    <input type="number" name="items[{{ $loop->index }}][qty]" value="{{ old('items.'.$loop->index.'.qty', 0) }}" min="0" max="{{ $it->qty }}" style="width: 50px; padding: 0.3rem; border: 1px solid #ddd; border-radius: 4px; text-align: center; font-size: 0.65rem;">
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
                        <select name="tipe_kompensasi" id="tipe_kompensasi" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem; color: #2d3748;" required>
                            <option value="barang" {{ old('tipe_kompensasi') == 'barang' ? 'selected' : '' }}>Tukar Barang</option>
                            <option value="uang" {{ old('tipe_kompensasi') == 'uang' ? 'selected' : '' }}>Refund Uang</option>
                        </select>
                    </div>

                    <!-- Refund Options Box (image 2 design) -->
                    <div id="refund_options_container" style="display: none; margin-bottom: 0.8rem; padding: 0.8rem; border: 1px solid #fbd38d; background-color: #fffaf0; border-radius: 8px;">
                        <label style="display: block; font-size: 0.65rem; font-weight: 700; color: #b7791f; margin-bottom: 0.5rem;">💵 Opsi Pengembalian Dana (Refund)</label>
                        <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.3rem;">Metode Pengembalian Dana *</label>
                        <div style="display: flex; gap: 1.2rem; margin-bottom: 0.8rem;">
                            <label style="display: inline-flex; align-items: center; font-size: 0.65rem; color: #2d3748; cursor: pointer; font-weight: normal;">
                                <input type="radio" name="metode_refund" value="tunai" style="margin-right: 0.3rem;" {{ old('metode_refund', 'tunai') == 'tunai' ? 'checked' : '' }}> Tunai / Kas
                            </label>
                            <label style="display: inline-flex; align-items: center; font-size: 0.65rem; color: #2d3748; cursor: pointer; font-weight: normal;">
                                <input type="radio" name="metode_refund" value="transfer" style="margin-right: 0.3rem;" {{ old('metode_refund') == 'transfer' ? 'checked' : '' }}> Transfer Bank
                            </label>
                        </div>

                        <!-- Bank Details Input -->
                        <div id="bank_details_container" style="display: none; border-top: 1px dashed #fbd38d; padding-top: 0.6rem;">
                            <div style="margin-bottom: 0.5rem;">
                                <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.2rem;">Nama Bank *</label>
                                <input type="text" name="nama_bank" value="{{ old('nama_bank') }}" placeholder="Contoh: BCA, Mandiri, BRI, BNI" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem; color: #2d3748;">
                            </div>
                            <div style="margin-bottom: 0.5rem;">
                                <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.2rem;">Nomor Rekening *</label>
                                <input type="text" name="rekening_nomor" value="{{ old('rekening_nomor') }}" placeholder="Contoh: 1234567890" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem; color: #2d3748;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.2rem;">Nama Pemilik Rekening *</label>
                                <input type="text" name="rekening_nama" value="{{ old('rekening_nama') }}" placeholder="Nama lengkap pemilik rekening" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem; color: #2d3748;">
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 0.8rem;">
                        <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.3rem;">Alasan</label>
                        <textarea name="alasan" rows="3" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem; color: #2d3748; font-family: inherit; resize: vertical;" placeholder="Tuliskan alasan retur (opsional)">{{ old('alasan') }}</textarea>
                    </div>

                    <!-- Bukti Foto -->
                    <div style="border-top: 1px solid #f0f0f0; padding-top: 0.8rem; margin-top: 0.8rem;">
                        <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.2rem;">📷 Foto Bukti Barang (Opsional)</label>
                        <p style="color: #999; margin: 0 0 0.5rem 0; font-size: 0.55rem;">Pilih foto produk yang bermasalah untuk mempercepat proses persetujuan (Maksimal 2MB)</p>
                        <input type="file" name="bukti_foto" accept="image/*" style="font-size: 0.65rem; color: #2d3748; width: 100%; cursor: pointer;">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipeKompensasi = document.getElementById('tipe_kompensasi');
    const refundContainer = document.getElementById('refund_options_container');
    const bankContainer = document.getElementById('bank_details_container');
    const metodeRefundRadios = document.querySelectorAll('input[name="metode_refund"]');
    
    const namaBankInput = document.querySelector('input[name="nama_bank"]');
    const rekeningNomorInput = document.querySelector('input[name="rekening_nomor"]');
    const rekeningNamaInput = document.querySelector('input[name="rekening_nama"]');

    function toggleRefundOptions() {
        if (tipeKompensasi.value === 'uang') {
            refundContainer.style.display = 'block';
        } else {
            refundContainer.style.display = 'none';
            // Reset values
            if(namaBankInput) namaBankInput.value = '';
            if(rekeningNomorInput) rekeningNomorInput.value = '';
            if(rekeningNamaInput) rekeningNamaInput.value = '';
        }
    }

    function toggleBankDetails() {
        let selectedMetode = '';
        metodeRefundRadios.forEach(radio => {
            if (radio.checked) {
                selectedMetode = radio.value;
            }
        });

        if (selectedMetode === 'transfer' && tipeKompensasi.value === 'uang') {
            bankContainer.style.display = 'block';
            if(namaBankInput) namaBankInput.required = true;
            if(rekeningNomorInput) rekeningNomorInput.required = true;
            if(rekeningNamaInput) rekeningNamaInput.required = true;
        } else {
            bankContainer.style.display = 'none';
            if(namaBankInput) namaBankInput.required = false;
            if(rekeningNomorInput) rekeningNomorInput.required = false;
            if(rekeningNamaInput) rekeningNamaInput.required = false;
        }
    }

    if (tipeKompensasi) {
        tipeKompensasi.addEventListener('change', function() {
            toggleRefundOptions();
            toggleBankDetails();
        });
        // Init state
        toggleRefundOptions();
    }

    metodeRefundRadios.forEach(radio => {
        radio.addEventListener('change', toggleBankDetails);
    });
    
    // Init state for bank details
    toggleBankDetails();
});
</script>

@endsection
