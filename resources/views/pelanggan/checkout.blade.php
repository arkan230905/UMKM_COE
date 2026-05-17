@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <div style="width: 50px; height: 50px; border-radius: 50%; background: #fdf5eb; color: #8b5a2b; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="bi bi-bag"></i>
        </div>
        <div>
            <h3 class="mb-0 text-dark" style="font-weight: 700;">Checkout</h3>
            <p class="mb-0 text-muted" style="font-size: 0.9rem;">Lengkapi data dan lakukan pembayaran untuk menyelesaikan pesananmu</p>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Left Column: Forms -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4">
                    <form action="{{ route('pelanggan.checkout.process') }}" method="POST" id="checkoutForm">
                        @csrf
                        
                        <!-- Data Pengiriman -->
                        <div class="d-flex gap-3 mb-4">
                            <div style="color: #8b5a2b; font-size: 1.5rem;"><i class="bi bi-truck"></i></div>
                            <div style="flex: 1;">
                                <h6 style="font-weight: 700; color: #2d3748; margin-bottom: 0.2rem;">Data Pengiriman</h6>
                                <p style="font-size: 0.8rem; color: #888; margin-bottom: 1rem;">Pastikan data pengiriman sudah benar</p>
                                
                                <div class="mb-3">
                                    <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">Nama Penerima <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" name="nama_penerima" class="form-control pe-5" value="{{ old('nama_penerima', auth()->user()->name) }}" required style="border-radius: 8px; font-size: 0.9rem; padding: 0.6rem 1rem;">
                                        <i class="bi bi-person position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
                                    </div>
                                    @error('nama_penerima') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">Alamat Lengkap <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" id="alamat_pengiriman" name="alamat_pengiriman" class="form-control" required placeholder="Ketik alamat lengkap (cth: Jl. Braga, Bandung)" style="border-radius: 8px; font-size: 0.9rem; padding: 0.6rem 1rem;">
                                        <i class="bi bi-geo-alt position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
                                        
                                        <!-- Address Suggestions Dropdown -->
                                        <div id="address-suggestions" class="position-absolute w-100" style="top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; border-radius: 0 0 8px 8px; max-height: 300px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                        </div>
                                    </div>
                                    @error('alamat_pengiriman') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">No. Telepon <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" name="telepon_penerima" class="form-control pe-5" value="{{ old('telepon_penerima', auth()->user()->phone) }}" required placeholder="08xxxxxxxxxx" style="border-radius: 8px; font-size: 0.9rem; padding: 0.6rem 1rem;">
                                        <i class="bi bi-telephone position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
                                    </div>
                                    @error('telepon_penerima') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>

                        <hr style="border-color: #eee; margin: 1.5rem 0;">

                        <!-- Metode Pembayaran -->
                        <div class="d-flex gap-3 mb-4">
                            <div style="color: #8b5a2b; font-size: 1.5rem;"><i class="bi bi-credit-card-2-front"></i></div>
                            <div style="flex: 1;">
                                <h6 style="font-weight: 700; color: #2d3748; margin-bottom: 0.2rem;">Metode Pembayaran</h6>
                                <p style="font-size: 0.8rem; color: #888; margin-bottom: 1rem;">Pilih metode pembayaran yang tersedia</p>
                                
                                <div class="mb-3">
                                    <select name="payment_method" class="form-select" required style="border-radius: 8px; font-size: 0.9rem; padding: 0.6rem 1rem;">
                                        <option value="">-- Pilih Metode Pembayaran --</option>
                                        <option value="qris" {{ old('payment_method') == 'qris' ? 'selected' : '' }}>QRIS (Scan & Pay)</option>
                                        <option value="va_bca" {{ old('payment_method') == 'va_bca' ? 'selected' : '' }}>BCA Virtual Account</option>
                                        <option value="va_bni" {{ old('payment_method') == 'va_bni' ? 'selected' : '' }}>BNI Virtual Account</option>
                                        <option value="va_bri" {{ old('payment_method') == 'va_bri' ? 'selected' : '' }}>BRI Virtual Account</option>
                                        <option value="va_mandiri" {{ old('payment_method') == 'va_mandiri' ? 'selected' : '' }}>Mandiri Virtual Account</option>
                                        <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer Bank Manual</option>
                                        <option value="cod" {{ old('payment_method') == 'cod' ? 'selected' : '' }}>COD (Cash On Delivery)</option>
                                        <option value="kasir" {{ old('payment_method') == 'kasir' ? 'selected' : '' }}>Bayar di Kasir</option>
                                    </select>
                                    <small class="text-muted" style="font-size: 0.75rem; display: block; margin-top: 0.5rem;"><i class="bi bi-shield-check text-success"></i> Pembayaran aman melalui Midtrans</small>
                                    
                                    <!-- Info untuk COD -->
                                    <div id="cod-info" style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 0.8rem; margin-top: 0.8rem; display: none; font-size: 0.8rem; color: #856404;">
                                        <i class="bi bi-info-circle" style="color: #ffc107;"></i>
                                        <strong>COD (Cash On Delivery):</strong> Bayar saat barang tiba. Ongkir tetap dihitung berdasarkan jarak pengiriman.
                                    </div>
                                    
                                    <!-- Info untuk Kasir -->
                                    <div id="kasir-info" style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 0.8rem; margin-top: 0.8rem; display: none; font-size: 0.8rem; color: #856404;">
                                        <i class="bi bi-info-circle" style="color: #ffc107;"></i>
                                        <strong>Bayar di Kasir:</strong> Ambil langsung di toko. Tidak ada ongkir. Alamat pengiriman bisa diisi dengan "-".
                                    </div>
                                    
                                    <!-- Info untuk Transfer Bank Manual -->
                                    @if($perusahaan && $perusahaan->nama_bank && $perusahaan->nomor_rekening)
                                    <div id="transfer-info" style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 6px; padding: 0.8rem; margin-top: 0.8rem; display: none; font-size: 0.8rem; color: #1565c0;">
                                        <i class="bi bi-bank" style="color: #2196f3;"></i>
                                        <strong>Info Rekening:</strong><br>
                                        <span style="display: block; margin-top: 0.4rem;">
                                            Bank: <strong>{{ $perusahaan->nama_bank }}</strong><br>
                                            Nomor: <strong>{{ $perusahaan->nomor_rekening }}</strong><br>
                                            @if($perusahaan->nama_pemilik_rekening)
                                            Atas Nama: <strong>{{ $perusahaan->nama_pemilik_rekening }}</strong>
                                            @endif
                                        </span>
                                    </div>
                                    @endif
                                    
                                    @error('payment_method') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>

                        <hr style="border-color: #eee; margin: 1.5rem 0;">

                        <!-- Catatan -->
                        <div class="d-flex gap-3 mb-4">
                            <div style="color: #8b5a2b; font-size: 1.5rem;"><i class="bi bi-file-earmark-text"></i></div>
                            <div style="flex: 1;">
                                <h6 style="font-weight: 700; color: #2d3748; margin-bottom: 0.2rem;">Catatan (Optional)</h6>
                                <p style="font-size: 0.8rem; color: #888; margin-bottom: 1rem;">Tambahkan catatan untuk penjual (jika ada)</p>
                                
                                <div class="position-relative">
                                    <textarea name="catatan" class="form-control pe-5" rows="2" placeholder="Catatan untuk penjual (optional)" style="border-radius: 8px; font-size: 0.9rem; padding: 0.6rem 1rem;">{{ old('catatan') }}</textarea>
                                    <i class="bi bi-pencil position-absolute" style="right: 15px; bottom: 15px; color: #aaa;"></i>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn w-100" style="background: #a66a38; color: white; border-radius: 8px; padding: 0.8rem; font-weight: 600; font-size: 1rem; box-shadow: 0 4px 10px rgba(139, 90, 43, 0.2);">
                            <i class="bi bi-lock-fill me-2"></i> Proses Pembayaran
                        </button>
                        
                        <!-- Hidden input to store ongkir -->
                        <input type="hidden" name="biaya_ongkir" id="biaya_ongkir" value="0">
                        <input type="hidden" name="latitude_pengiriman" id="latitude_pengiriman" value="">
                        <input type="hidden" name="longitude_pengiriman" id="longitude_pengiriman" value="">
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Summary & Support -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="d-flex gap-3 mb-4">
                        <div style="color: #8b5a2b; font-size: 1.5rem;"><i class="bi bi-receipt"></i></div>
                        <div>
                            <h6 style="font-weight: 700; color: #2d3748; margin-bottom: 0.2rem;">Ringkasan Pesanan</h6>
                            <p style="font-size: 0.8rem; color: #888; margin-bottom: 0;">Periksa kembali pesananmu</p>
                        </div>
                    </div>

                    <div style="max-height: 250px; overflow-y: auto; margin-bottom: 1rem; padding-right: 0.5rem;">
                        @foreach($carts as $cart)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width: 50px; height: 50px; border-radius: 8px; overflow: hidden; background: #f8f9fa;">
                                    @if($cart->produk->foto)
                                    <img src="{{ storage_url($cart->produk->foto) }}" alt="{{ $cart->produk->nama_produk }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #ccc;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                    @endif
                                </div>
                                <div>
                                    <strong style="font-size: 0.9rem; color: #2d3748; display: block;">{{ $cart->produk->nama_produk }}</strong>
                                    <small style="color: #888; font-size: 0.8rem;">{{ $cart->qty }} x Rp {{ number_format($cart->harga, 0, ',', '.') }}</small>
                                </div>
                            </div>
                            <span style="font-weight: 600; font-size: 0.9rem; color: #2d3748;">Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                    
                    @php
                        $ongkir = 0; // Will be calculated via AJAX
                        $ppn = $total * 0.11; // 11% PPN
                        $grandTotal = $total + $ongkir + $ppn;
                    @endphp

                    <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 0.85rem;">
                        <span style="color: #666;">Subtotal</span>
                        <span style="color: #2d3748;">Rp <span id="subtotal-display">{{ number_format($total, 0, ',', '.') }}</span></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 0.85rem;">
                        <span style="color: #666;">Ongkos Kirim</span>
                        <span style="color: #2d3748;">Rp <span id="ongkir-display">0</span></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3" style="font-size: 0.85rem;">
                        <span style="color: #666;">PPN (11%)</span>
                        <span style="color: #2d3748;">Rp <span id="ppn-display">0</span></span>
                    </div>

                    <hr style="border-color: #eee; margin: 0.8rem 0;">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span style="font-weight: 700; color: #2d3748; font-size: 1rem;">Total</span>
                        <span style="font-weight: 800; color: #8b5a2b; font-size: 1.2rem;">Rp <span id="grand-total-display">0</span></span>
                    </div>

                    <div id="jarak-info" style="background: #f0f4f8; border-left: 3px solid #8b5a2b; border-radius: 4px; padding: 0.6rem 0.8rem; margin-bottom: 1rem; font-size: 0.8rem; color: #555; display: none;">
                    </div>

                    <div style="background: #eef8f1; border: 1px solid #d4ecd9; border-radius: 8px; padding: 0.8rem; display: flex; align-items: flex-start; gap: 0.8rem; margin-bottom: 1rem;">
                        <i class="bi bi-info-circle" style="color: #3498db; font-size: 1.2rem;"></i>
                        <div>
                            <strong style="font-size: 0.8rem; color: #3498db; display: block;">Cara Perhitungan Ongkir</strong>
                            <span style="font-size: 0.7rem; color: #3498db; opacity: 0.9;">Ongkir dihitung berdasarkan jarak dari toko ke alamat pengiriman Anda menggunakan Google Maps.</span>
                        </div>
                    </div>

                    <div style="background: #eef8f1; border: 1px solid #d4ecd9; border-radius: 8px; padding: 0.8rem; display: flex; align-items: flex-start; gap: 0.8rem;">
                        <i class="bi bi-shield-check" style="color: #27ae60; font-size: 1.2rem;"></i>
                        <div>
                            <strong style="font-size: 0.8rem; color: #27ae60; display: block;">Pembayaran aman dengan Midtrans</strong>
                            <span style="font-size: 0.7rem; color: #27ae60; opacity: 0.8;">Transaksi dilindungi sistem keamanan berlapis</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Block -->
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; background: #fdfaf7; position: relative; overflow: hidden;">
                <!-- Decorative leaves -->
                <i class="bi bi-flower3 position-absolute" style="font-size: 8rem; color: #f4ebd8; right: -20px; bottom: -30px; transform: rotate(-15deg);"></i>
                <div class="card-body p-4 d-flex align-items-center gap-3 position-relative z-1">
                    <div style="width: 45px; height: 45px; border-radius: 50%; border: 2px solid #8b5a2b; color: #8b5a2b; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; background: white;">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div>
                        <strong style="font-size: 0.9rem; color: #2d3748; display: block; margin-bottom: 0.2rem;">Butuh Bantuan?</strong>
                        <span style="font-size: 0.75rem; color: #888; display: block; margin-bottom: 0.6rem;">Hubungi kami jika ada kendala saat checkout.</span>
                        <a href="https://wa.me/{{ $whatsappNumber ?? '' }}" target="_blank" class="btn btn-sm" style="background: white; border: 1px solid #8b5a2b; color: #8b5a2b; border-radius: 50px; font-size: 0.75rem; font-weight: 600; padding: 0.2rem 0.8rem;">
                            <i class="bi bi-whatsapp"></i> Hubungi CS
                        </a>
                    </div>
                </div>
            </div>

            <!-- Trust Badges -->
            <div class="row g-2 mt-2">
                <div class="col-6">
                    <div class="text-center p-2">
                        <i class="bi bi-shield-check mb-1" style="font-size: 1.5rem; color: #8b5a2b; display: block;"></i>
                        <strong style="font-size: 0.7rem; color: #2d3748; display: block;">Transaksi Aman</strong>
                        <span style="font-size: 0.65rem; color: #888; display: block; line-height: 1.2;">Dilindungi sistem keamanan berlapis</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2">
                        <i class="bi bi-truck mb-1" style="font-size: 1.5rem; color: #8b5a2b; display: block;"></i>
                        <strong style="font-size: 0.7rem; color: #2d3748; display: block;">Pengiriman Cepat</strong>
                        <span style="font-size: 0.65rem; color: #888; display: block; line-height: 1.2;">Diproses & dikirim dengan aman</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2">
                        <i class="bi bi-patch-check mb-1" style="font-size: 1.5rem; color: #8b5a2b; display: block;"></i>
                        <strong style="font-size: 0.7rem; color: #2d3748; display: block;">Produk Berkualitas</strong>
                        <span style="font-size: 0.65rem; color: #888; display: block; line-height: 1.2;">Kualitas terbaik pilihan</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2">
                        <i class="bi bi-headset mb-1" style="font-size: 1.5rem; color: #8b5a2b; display: block;"></i>
                        <strong style="font-size: 0.7rem; color: #2d3748; display: block;">Layanan 24/7</strong>
                        <span style="font-size: 0.65rem; color: #888; display: block; line-height: 1.2;">Siap membantu kapan saja</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addressInput = document.getElementById('alamat_pengiriman');
        const suggestionsList = document.getElementById('address-suggestions');
        const paymentMethodSelect = document.querySelector('select[name="payment_method"]');
        const cashInfoBox = document.getElementById('cash-info');
        let timeoutId;

        // Show/hide payment method info and handle ongkir
        if (paymentMethodSelect) {
            paymentMethodSelect.addEventListener('change', function() {
                const codInfoBox = document.getElementById('cod-info');
                const kasirInfoBox = document.getElementById('kasir-info');
                const transferInfoBox = document.getElementById('transfer-info');
                
                if (this.value === 'cod') {
                    if (codInfoBox) codInfoBox.style.display = 'block';
                    if (kasirInfoBox) kasirInfoBox.style.display = 'none';
                    if (transferInfoBox) transferInfoBox.style.display = 'none';
                    // For COD, ongkir is calculated normally
                    addressInput.placeholder = 'Ketik alamat lengkap (cth: Jl. Braga, Bandung)';
                } else if (this.value === 'kasir') {
                    if (codInfoBox) codInfoBox.style.display = 'none';
                    if (kasirInfoBox) kasirInfoBox.style.display = 'block';
                    if (transferInfoBox) transferInfoBox.style.display = 'none';
                    // For Kasir, set ongkir to 0 and clear address
                    currentOngkir = 0;
                    addressInput.value = '-';
                    addressInput.placeholder = 'Ambil di toko (tidak perlu alamat)';
                    document.getElementById('latitude_pengiriman').value = '';
                    document.getElementById('longitude_pengiriman').value = '';
                    document.getElementById('ongkir-display').innerHTML = 'Rp 0';
                    document.getElementById('biaya_ongkir').value = 0;
                    updateTotals();
                } else if (this.value === 'transfer') {
                    if (codInfoBox) codInfoBox.style.display = 'none';
                    if (kasirInfoBox) kasirInfoBox.style.display = 'none';
                    if (transferInfoBox) transferInfoBox.style.display = 'block';
                    // For Transfer, ongkir is calculated normally
                    addressInput.placeholder = 'Ketik alamat lengkap (cth: Jl. Braga, Bandung)';
                } else {
                    if (codInfoBox) codInfoBox.style.display = 'none';
                    if (kasirInfoBox) kasirInfoBox.style.display = 'none';
                    if (transferInfoBox) transferInfoBox.style.display = 'none';
                    // For other methods, ongkir is calculated normally
                    addressInput.placeholder = 'Ketik alamat lengkap (cth: Jl. Braga, Bandung)';
                }
            });
            
            // Check initial state
            if (paymentMethodSelect.value === 'cod') {
                const codInfoBox = document.getElementById('cod-info');
                if (codInfoBox) codInfoBox.style.display = 'block';
            } else if (paymentMethodSelect.value === 'kasir') {
                const kasirInfoBox = document.getElementById('kasir-info');
                if (kasirInfoBox) kasirInfoBox.style.display = 'block';
                // Set initial state for kasir
                currentOngkir = 0;
                addressInput.value = '-';
                addressInput.placeholder = 'Ambil di toko (tidak perlu alamat)';
                document.getElementById('ongkir-display').innerHTML = 'Rp 0';
                document.getElementById('biaya_ongkir').value = 0;
                updateTotals();
            } else if (paymentMethodSelect.value === 'transfer') {
                const transferInfoBox = document.getElementById('transfer-info');
                if (transferInfoBox) transferInfoBox.style.display = 'block';
            }
        }

        addressInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const query = this.value.trim();

            // Don't show suggestions if address is "-" (kasir method)
            if (query === '-' || query.length < 5) {
                suggestionsList.style.display = 'none';
                return;
            }

            // Debounce the API call
            timeoutId = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=15`)
                    .then(response => response.json())
                    .then(data => {
                        suggestionsList.innerHTML = '';
                        
                        if (data.length > 0) {
                            // Add a header item
                            const headerLi = document.createElement('li');
                            headerLi.className = 'list-group-item';
                            headerLi.style.cssText = 'background: #f8f9fa; font-size: 0.75rem; color: #888; font-weight: 600; padding: 0.4rem 1rem; border-bottom: 1px solid #eee;';
                            headerLi.innerText = 'Rekomendasi tempat berdasarkan alamatmu';
                            suggestionsList.appendChild(headerLi);

                            data.forEach(item => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item list-group-item-action d-flex align-items-start gap-2';
                                li.style.cssText = 'cursor: pointer; padding: 0.6rem 1rem; font-size: 0.85rem;';
                                
                                li.innerHTML = `
                                    <i class="bi bi-geo-alt-fill text-muted mt-1"></i>
                                    <div>
                                        <div style="font-weight: 600; color: #2d3748;">${item.display_name.split(',')[0]}</div>
                                        <div style="color: #666; font-size: 0.8rem;">${item.display_name}</div>
                                    </div>
                                `;
                                
                                li.addEventListener('click', function() {
                                    addressInput.value = item.display_name;
                                    document.getElementById('latitude_pengiriman').value = item.lat;
                                    document.getElementById('longitude_pengiriman').value = item.lon;
                                    suggestionsList.style.display = 'none';
                                    calculateOngkir(item.display_name, item.lat, item.lon);
                                });
                                
                                suggestionsList.appendChild(li);
                            });
                            suggestionsList.style.display = 'block';
                        } else {
                            suggestionsList.style.display = 'none';
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching address suggestions:', err);
                        suggestionsList.style.display = 'none';
                    });
            }, 500); // 500ms delay
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target !== addressInput && e.target !== suggestionsList && !suggestionsList.contains(e.target)) {
                suggestionsList.style.display = 'none';
            }
        });

        const baseTotal = parseInt({{ $total }}) || 0;
        let currentOngkir = 0;
        
        console.log('Base Total:', baseTotal, 'Type:', typeof baseTotal);
        
        function formatRupiah(amount) {
            return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function updateTotals() {
            const ppn = baseTotal * 0.11;
            const grandTotal = baseTotal + ppn + currentOngkir;
            
            console.log('Update Totals - Base:', baseTotal, 'PPN:', ppn, 'Ongkir:', currentOngkir, 'Grand:', grandTotal);
            
            document.getElementById('ppn-display').innerText = formatRupiah(Math.round(ppn));
            document.getElementById('grand-total-display').innerText = formatRupiah(Math.round(grandTotal));
        }

        function calculateOngkir(address, lat, lon) {
            const ongkirDisplay = document.getElementById('ongkir-display');
            const jarakText = document.getElementById('jarak-info');
            const ongkirInput = document.getElementById('biaya_ongkir');

            ongkirDisplay.innerHTML = '<span class="spinner-border spinner-border-sm text-primary" role="status"></span> Menghitung...';
            
            fetch('{{ route("pelanggan.checkout.ongkir") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ alamat: address, latitude: lat, longitude: lon })
            })
            .then(res => res.json())
            .then(data => {
                console.log('Ongkir Response:', data);
                
                if (data.success) {
                    currentOngkir = parseInt(data.ongkir) || 0;
                    console.log('Jarak:', data.jarak, 'km | Range:', data.range_km, '| Ongkir:', currentOngkir);
                    
                    ongkirDisplay.innerHTML = formatRupiah(currentOngkir) + '<br><small style="color: #999; font-size: 0.7rem;">(' + data.range_km + ')</small>';
                    ongkirInput.value = currentOngkir;
                    
                    jarakText.style.display = 'none';
                    updateTotals();
                } else {
                    ongkirDisplay.innerHTML = '<span class="text-danger" style="font-size:0.75rem;">' + (data.message || 'Gagal menghitung') + '</span>';
                    jarakText.style.display = 'none';
                    currentOngkir = 0;
                    ongkirInput.value = 0;
                    updateTotals();
                }
            })
            .catch(err => {
                console.error(err);
                ongkirDisplay.innerHTML = '<span class="text-danger" style="font-size:0.75rem;">Error koneksi</span>';
                currentOngkir = 0;
                ongkirInput.value = 0;
                updateTotals();
            });
        }

        // Initialize totals on page load
        updateTotals();
    });
</script>
@endpush

@endsection


