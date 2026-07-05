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
                @if($orders->isEmpty())
                <div style="background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 8px; padding: 0.8rem; color: #0369a1; font-size: 0.7rem; text-align: center;">
                    <strong>Tidak ada pesanan yang tersedia untuk diajukan retur.</strong><br>
                    <span style="font-size: 0.65rem; color: #0284c7;">Retur hanya dapat diajukan maksimal 5 jam setelah pesanan selesai dan jika masih ada item yang belum diretur.</span>
                </div>
                @else
                <form method="GET" action="{{ url("/" . $perusahaan_slug . "/pelanggan/returns/create") }}" style="display: flex; gap: 0.5rem; align-items: flex-end;">
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.3rem;">Pesanan</label>
                        <select name="order_id" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem; color: #2d3748;" required>
                            <option value="">-- Pilih Pesanan --</option>
                            @foreach($orders as $o)
                            @php
                                $sisaWaktu = '';
                                if (isset($o->calculated_base_time)) {
                                    $diff = 5 * 60 - now()->diffInMinutes(\Carbon\Carbon::parse($o->calculated_base_time));
                                    if ($diff > 0) {
                                        $hours = floor($diff / 60);
                                        $minutes = $diff % 60;
                                        $sisaWaktu = " - Sisa retur {$hours}j {$minutes}m";
                                    }
                                } elseif ($o->updated_at) {
                                    $diff = 5 * 60 - now()->diffInMinutes($o->updated_at);
                                    if ($diff > 0) {
                                        $hours = floor($diff / 60);
                                        $minutes = $diff % 60;
                                        $sisaWaktu = " - Sisa retur {$hours}j {$minutes}m";
                                    }
                                }
                                
                                $productNames = [];
                                if ($o->items && $o->items->count() > 0) {
                                    foreach ($o->items->take(2) as $item) {
                                        $nama = $item->produk->nama_produk ?? $item->produk->nama ?? $item->nama_produk ?? 'Produk';
                                        $productNames[] = $nama . " (" . (int)$item->qty . "x)";
                                    }
                                    $productsString = implode(', ', $productNames);
                                    if ($o->items->count() > 2) {
                                        $productsString .= ", +" . ($o->items->count() - 2) . " produk lainnya";
                                    }
                                } else {
                                    $productsString = "Produk tidak ditemukan";
                                }
                            @endphp
                            <option value="{{ $o->id }}" {{ request('order_id') == $o->id ? 'selected' : '' }}>
                                #{{ $o->nomor_order }} - {{ $productsString }} - Rp {{ number_format($o->total_amount, 0, ',', '.') }}{{ $sisaWaktu }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" style="padding: 0.4rem 0.8rem; background: #3b82f6; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 0.6rem;">Muat Item</button>
                </form>
                @endif
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
                                <th style="text-align: center; padding: 0.4rem; font-weight: 700; color: #2d3748;">Sisa / Qty</th>
                                <th style="text-align: center; padding: 0.4rem; font-weight: 700; color: #2d3748;">Qty Retur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 0.4rem; color: #2d3748;">
                                    <select id="select-produk-retur" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem; color: #2d3748;">
                                        @foreach($order->items as $it)
                                        <option value="{{ $it->id }}" data-harga="{{ number_format($it->harga, 0, ',', '.') }}" data-remaining="{{ $it->remaining_qty }}" data-qty="{{ $it->qty }}">
                                            {{ $it->produk->nama_produk ?? $it->produk->nama ?? $it->nama_produk ?? 'Produk tidak ditemukan' }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="padding: 0.4rem; text-align: right; color: #8b6f47; font-weight: 600;">Rp <span id="text-harga">{{ number_format($order->items->first()->harga ?? 0, 0, ',', '.') }}</span></td>
                                <td style="padding: 0.4rem; text-align: center; color: #2d3748;"><span id="text-sisa">{{ $order->items->first()->remaining_qty ?? 0 }}</span> / <span id="text-qty">{{ $order->items->first()->qty ?? 0 }}</span></td>
                                <td style="padding: 0.4rem; text-align: center;">
                                    <input type="hidden" name="items[0][order_item_id]" id="input-item-id" value="{{ $order->items->first()->id ?? '' }}">
                                    <input type="number" name="items[0][qty]" id="input-qty-retur" value="{{ old('items.0.qty', 1) }}" min="1" max="{{ $order->items->first()->remaining_qty ?? 0 }}" style="width: 50px; padding: 0.3rem; border: 1px solid #ddd; border-radius: 4px; text-align: center; font-size: 0.65rem;">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const selectProduk = document.getElementById('select-produk-retur');
                            if (selectProduk) {
                                selectProduk.addEventListener('change', function() {
                                    const option = this.options[this.selectedIndex];
                                    if (option) {
                                        document.getElementById('text-harga').innerText = option.getAttribute('data-harga');
                                        document.getElementById('text-sisa').innerText = option.getAttribute('data-remaining');
                                        document.getElementById('text-qty').innerText = option.getAttribute('data-qty');
                                        document.getElementById('input-item-id').value = option.value;
                                        
                                        const qtyInput = document.getElementById('input-qty-retur');
                                        const remaining = parseInt(option.getAttribute('data-remaining'));
                                        qtyInput.max = remaining;
                                        if (parseInt(qtyInput.value) > remaining) {
                                            qtyInput.value = remaining;
                                        }
                                        if (parseInt(qtyInput.value) < 1 && remaining > 0) {
                                            qtyInput.value = 1;
                                        }
                                    }
                                });
                            }
                        });
                    </script>
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
                    <div id="barang_options_container" style="display: none; margin-bottom: 0.8rem; padding: 0.8rem; border: 1px solid #90cdf4; background-color: #ebf8ff; border-radius: 8px;">
                        <label style="display: block; font-size: 0.65rem; font-weight: 700; color: #2b6cb0; margin-bottom: 0.5rem;">📦 Metode Pengambilan Barang Pengganti</label>
                        <div style="display: flex; gap: 1.2rem; margin-bottom: 0.8rem;">
                            <label style="display: inline-flex; align-items: center; font-size: 0.65rem; color: #2d3748; cursor: pointer; font-weight: normal;">
                                <input type="radio" name="metode_pengambilan_retur" value="ambil_di_toko" style="margin-right: 0.3rem;" {{ old('metode_pengambilan_retur', 'ambil_di_toko') == 'ambil_di_toko' ? 'checked' : '' }}> Ambil di Toko
                            </label>
                            <label style="display: inline-flex; align-items: center; font-size: 0.65rem; color: #2d3748; cursor: pointer; font-weight: normal;">
                                <input type="radio" name="metode_pengambilan_retur" value="delivery" style="margin-right: 0.3rem;" {{ old('metode_pengambilan_retur') == 'delivery' ? 'checked' : '' }}> Delivery
                            </label>
                        </div>

                        <!-- Info Ambil di Toko -->
                        <div id="info_ambil_di_toko" style="display: none; margin-bottom: 0.5rem;">
                            <div style="background: #e6fffa; border: 1px solid #b2f5ea; padding: 0.6rem; border-radius: 6px; font-size: 0.6rem; color: #234e52;">
                                <i class="fas fa-info-circle me-1"></i> Barang pengganti dapat diambil langsung di toko setelah pengajuan retur disetujui. Ongkir: Rp 0.
                            </div>
                        </div>

                        <!-- Form Delivery -->
                        <div id="form_delivery" style="display: none; border-top: 1px dashed #90cdf4; padding-top: 0.6rem;">
                            <div style="margin-bottom: 0.5rem;">
                                <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.2rem;">Pilih Lokasi di Peta *</label>
                                <div id="map" style="height: 250px; border-radius: 8px; margin-bottom: 0.5rem; border: 1px solid #ddd; z-index: 1;"></div>
                            </div>
                            
                            <input type="hidden" name="biaya_ongkir" id="biaya_ongkir" value="0">
                            <input type="hidden" name="latitude_pengiriman" id="latitude_pengiriman" value="">
                            <input type="hidden" name="longitude_pengiriman" id="longitude_pengiriman" value="">
                            
                            <div style="margin-bottom: 0.5rem;">
                                <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.2rem;">Alamat Pengiriman *</label>
                                <input type="text" id="alamat_pengiriman" name="alamat_retur" class="form-control" readonly required placeholder="Alamat terisi otomatis dari peta" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem; background: #f8f9fa;">
                            </div>

                            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <div style="flex: 1;">
                                    <input type="text" id="kecamatan" name="kecamatan" class="form-control" placeholder="Kecamatan" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem;">
                                </div>
                                <div style="flex: 1;">
                                    <input type="text" id="kota" name="kota" class="form-control" placeholder="Kota" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem;">
                                </div>
                            </div>

                            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <div style="flex: 1;">
                                    <input type="text" id="provinsi" name="provinsi" class="form-control" placeholder="Provinsi" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem;">
                                </div>
                                <div style="flex: 1;">
                                    <input type="text" id="kode_pos" name="kode_pos" class="form-control" placeholder="Kode Pos" style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem;">
                                </div>
                            </div>

                            <div style="margin-bottom: 0.5rem;">
                                <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.2rem;">Detail Alamat / Patokan (Opsional)</label>
                                <textarea name="detail_alamat_retur" id="detail_alamat" class="form-control" rows="2" placeholder="Cth: Rumah pagar hitam, depan masjid..." style="width: 100%; padding: 0.4rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.65rem;"></textarea>
                            </div>
                            
                            <div id="ongkir_display_container" style="background: #fff5f5; border: 1px solid #feb2b2; padding: 0.6rem; border-radius: 6px; font-size: 0.6rem; color: #9b2c2c; display: none;">
                                <strong>Biaya Ongkir: </strong> <span id="ongkir_display">Rp 0</span>
                            </div>
                            
                            <div id="ongkir_error_container" style="background: #fee2e2; border: 1px solid #fecaca; padding: 0.6rem; border-radius: 6px; font-size: 0.6rem; color: #7f1d1d; display: none; margin-top: 0.5rem;">
                            </div>
                        </div>
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
                        <label style="display: block; font-size: 0.6rem; font-weight: 600; color: #2d3748; margin-bottom: 0.2rem;">📷 Foto Bukti Barang</label>
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

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipeKompensasi = document.getElementById('tipe_kompensasi');
    const refundContainer = document.getElementById('refund_options_container');
    const barangContainer = document.getElementById('barang_options_container');
    const bankContainer = document.getElementById('bank_details_container');
    const metodeRefundRadios = document.querySelectorAll('input[name="metode_refund"]');
    const metodePengambilanRadios = document.querySelectorAll('input[name="metode_pengambilan_retur"]');
    
    const infoAmbilDiToko = document.getElementById('info_ambil_di_toko');
    const formDelivery = document.getElementById('form_delivery');
    
    const namaBankInput = document.querySelector('input[name="nama_bank"]');
    const rekeningNomorInput = document.querySelector('input[name="rekening_nomor"]');
    const rekeningNamaInput = document.querySelector('input[name="rekening_nama"]');
    
    let map = null;
    let marker = null;
    let companyLat = null;
    let companyLng = null;
    let tarifPerKm = null;
    
    // Fetch company delivery config once
    fetch(`/api/config/delivery?perusahaan_slug={{ $perusahaan_slug ?? 'default' }}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                companyLat = data.latitude;
                companyLng = data.longitude;
                tarifPerKm = data.tarif_per_km;
            }
        });

    function toggleKompensasiOptions() {
        if (tipeKompensasi.value === 'uang') {
            refundContainer.style.display = 'block';
            barangContainer.style.display = 'none';
        } else {
            refundContainer.style.display = 'none';
            barangContainer.style.display = 'block';
            // Reset refund values
            if(namaBankInput) namaBankInput.value = '';
            if(rekeningNomorInput) rekeningNomorInput.value = '';
            if(rekeningNamaInput) rekeningNamaInput.value = '';
            
            // Re-trigger layout for map if needed
            if (map) { setTimeout(() => map.invalidateSize(), 100); }
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
    
    function initMap() {
        if (map) return;
        
        // Default center
        let initialLat = -6.2088;
        let initialLng = 106.8456;
        
        if (companyLat && companyLng) {
            initialLat = parseFloat(companyLat);
            initialLng = parseFloat(companyLng);
        }

        map = L.map('map').setView([initialLat, initialLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        marker = L.marker([initialLat, initialLng], {draggable: true}).addTo(map);
        
        // Update form and calculate ongkir when marker is dragged
        marker.on('dragend', function(event) {
            const position = marker.getLatLng();
            updateLocationDetails(position.lat, position.lng);
        });
        
        // Try getting user location
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                map.setView([lat, lng], 15);
                marker.setLatLng([lat, lng]);
                updateLocationDetails(lat, lng);
            });
        }
    }

    function calculateDistance(lat1, lon1, lat2, lon2) {
        if (!lat1 || !lon1 || !lat2 || !lon2) return 0;
        
        const R = 6371; // Radius of the earth in km
        const dLat = deg2rad(lat2 - lat1);
        const dLon = deg2rad(lon2 - lon1); 
        const a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
            Math.sin(dLon/2) * Math.sin(dLon/2); 
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
        return R * c; 
    }

    function deg2rad(deg) {
        return deg * (Math.PI/180);
    }
    
    function updateLocationDetails(lat, lng) {
        document.getElementById('latitude_pengiriman').value = lat;
        document.getElementById('longitude_pengiriman').value = lng;
        
        // Fetch address from Nominatim (OpenStreetMap)
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.address) {
                    document.getElementById('alamat_pengiriman').value = data.display_name;
                    
                    const addr = data.address;
                    document.getElementById('kecamatan').value = addr.suburb || addr.village || addr.neighbourhood || '';
                    document.getElementById('kota').value = addr.city || addr.town || addr.county || '';
                    document.getElementById('provinsi').value = addr.state || addr.region || '';
                    document.getElementById('kode_pos').value = addr.postcode || '';
                    
                    calculateOngkir(lat, lng);
                }
            })
            .catch(err => console.error("Geocoding error", err));
    }
    
    function calculateOngkir(customerLat, customerLng) {
        const ongkirDisplayContainer = document.getElementById('ongkir_display_container');
        const ongkirDisplay = document.getElementById('ongkir_display');
        const ongkirErrorContainer = document.getElementById('ongkir_error_container');
        const biayaOngkirInput = document.getElementById('biaya_ongkir');
        const submitBtn = document.querySelector('button[type="submit"]');
        
        if (!companyLat || !companyLng || !tarifPerKm) {
            // Can't calculate
            return;
        }
        
        const distance = calculateDistance(
            parseFloat(companyLat), 
            parseFloat(companyLng), 
            parseFloat(customerLat), 
            parseFloat(customerLng)
        );
        
        // Misal radius max 30km
        if (distance > 30) {
            ongkirDisplayContainer.style.display = 'none';
            ongkirErrorContainer.style.display = 'block';
            ongkirErrorContainer.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> Jarak pengiriman terlalu jauh (${distance.toFixed(1)} km). Maksimal jarak pengiriman adalah 30 km.`;
            biayaOngkirInput.value = '';
            submitBtn.disabled = true;
        } else {
            // Minimum distance 1km
            const calcDistance = Math.max(1, Math.ceil(distance));
            const ongkir = calcDistance * parseFloat(tarifPerKm);
            
            ongkirDisplayContainer.style.display = 'block';
            ongkirErrorContainer.style.display = 'none';
            ongkirDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(ongkir) + ` (${distance.toFixed(1)} km)`;
            biayaOngkirInput.value = ongkir;
            submitBtn.disabled = false;
        }
    }

    function togglePengambilan() {
        let selectedMetode = '';
        metodePengambilanRadios.forEach(radio => {
            if (radio.checked) {
                selectedMetode = radio.value;
            }
        });
        
        const alamatInput = document.getElementById('alamat_pengiriman');
        const latInput = document.getElementById('latitude_pengiriman');
        const submitBtn = document.querySelector('button[type="submit"]');

        if (selectedMetode === 'delivery' && tipeKompensasi.value === 'barang') {
            infoAmbilDiToko.style.display = 'none';
            formDelivery.style.display = 'block';
            
            if (alamatInput) alamatInput.required = true;
            
            // Init map
            setTimeout(() => {
                initMap();
                if (map) map.invalidateSize();
                
                // Revalidate if location empty
                if (!latInput.value) {
                    submitBtn.disabled = true;
                }
            }, 200);
            
        } else {
            infoAmbilDiToko.style.display = 'block';
            formDelivery.style.display = 'none';
            
            if (alamatInput) alamatInput.required = false;
            submitBtn.disabled = false; // Always enabled for ambil di toko
            document.getElementById('biaya_ongkir').value = 0;
        }
    }

    if (tipeKompensasi) {
        tipeKompensasi.addEventListener('change', function() {
            toggleKompensasiOptions();
            toggleBankDetails();
            togglePengambilan();
        });
        // Init state
        toggleKompensasiOptions();
    }

    metodeRefundRadios.forEach(radio => {
        radio.addEventListener('change', toggleBankDetails);
    });
    
    metodePengambilanRadios.forEach(radio => {
        radio.addEventListener('change', togglePengambilan);
    });
    
    // Init state for options
    toggleBankDetails();
    togglePengambilan();
    
    // Form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (tipeKompensasi.value === 'barang') {
                const method = document.querySelector('input[name="metode_pengambilan_retur"]:checked').value;
                if (method === 'delivery') {
                    const lat = document.getElementById('latitude_pengiriman').value;
                    const ongkir = document.getElementById('biaya_ongkir').value;
                    
                    if (!lat) {
                        e.preventDefault();
                        alert('Silakan pilih lokasi di peta terlebih dahulu');
                        return;
                    }
                    
                    if (ongkir === '' || isNaN(ongkir)) {
                        e.preventDefault();
                        alert('Ongkir tidak valid atau lokasi terlalu jauh');
                        return;
                    }
                }
            }
        });
    }
});
</script>

@endsection
