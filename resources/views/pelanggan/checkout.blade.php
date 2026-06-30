@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <!-- Header Title & Steps -->
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill mb-2" style="background: #fdf5eb; color: #8b5a2b; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px;">
            CHECKOUT <i class="bi bi-credit-card"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Konfirmasi Pesananmu</h4>
        <p class="text-muted mb-3" style="font-size: 0.85rem;">Selesaikan detail pengiriman, pilih metode pembayaran, dan pesananmu siap diproses.</p>
        
        <div class="d-flex justify-content-center flex-wrap gap-2">
            <!-- Step 1 (Inactive) -->
            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-3 bg-white shadow-sm" style="border: 1px solid #f0f0f0;">
                <div class="d-flex align-items-center justify-content-center rounded" style="width: 28px; height: 28px; background: #f8f9fa;">
                    <i class="bi bi-cart text-muted" style="font-size: 0.9rem;"></i>
                </div>
                <span class="text-muted" style="font-weight: 600; font-size: 0.85rem;">Keranjang</span>
            </div>
            
            <!-- Step 2 (Active) -->
            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-3 bg-white shadow-sm" style="border: 1px solid #8b5a2b;">
                <div class="d-flex align-items-center justify-content-center rounded" style="width: 28px; height: 28px; background: #fdf5eb;">
                    <i class="bi bi-truck" style="color: #8b5a2b; font-size: 0.9rem;"></i>
                </div>
                <span style="color: #8b5a2b; font-weight: 600; font-size: 0.85rem;">Checkout</span>
            </div>
            
            <!-- Step 3 (Inactive) -->
            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-3 bg-white shadow-sm" style="border: 1px solid #f0f0f0;">
                <div class="d-flex align-items-center justify-content-center rounded" style="width: 28px; height: 28px; background: #f8f9fa;">
                    <i class="bi bi-receipt text-muted" style="font-size: 0.9rem;"></i>
                </div>
                <span class="text-muted" style="font-weight: 600; font-size: 0.85rem;">Pembayaran</span>
            </div>
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
                    <form action="{{ url("/" . $perusahaan_slug . "/pelanggan/checkout/process") }}" method="POST" id="checkoutForm">
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
                                    <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">Pilih Lokasi di Peta <span class="text-danger">*</span></label>
                                    <div class="position-relative mb-2">
                                        <input type="text" id="map_search" class="form-control" placeholder="Cari alamat di peta..." style="border-radius: 8px; font-size: 0.9rem; padding: 0.6rem 1rem;">
                                        <i class="bi bi-search position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
                                        <!-- Map Search Suggestions Dropdown -->
                                        <div id="map-suggestions" class="position-absolute w-100" style="top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-top: none; border-radius: 0 0 8px 8px; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                        </div>
                                    </div>
                                    <div id="map" style="height: 300px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 1rem; z-index: 1;"></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">Alamat Lengkap <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" id="alamat_pengiriman" name="alamat_pengiriman" class="form-control" readonly required placeholder="Alamat terisi otomatis dari peta" style="border-radius: 8px; font-size: 0.9rem; padding: 0.6rem 1rem; background: #f8f9fa;">
                                        <i class="bi bi-geo-alt position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
                                    </div>
                                    @error('alamat_pengiriman') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">Kecamatan</label>
                                        <input type="text" id="kecamatan" name="kecamatan" class="form-control" placeholder="Kecamatan" style="border-radius: 8px; font-size: 0.9rem; padding: 0.6rem 1rem;">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">Kota/Kabupaten</label>
                                        <input type="text" id="kota" name="kota" class="form-control" placeholder="Kota" style="border-radius: 8px; font-size: 0.9rem; padding: 0.6rem 1rem;">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">Kode Pos</label>
                                        <input type="text" id="kode_pos" name="kode_pos" class="form-control" placeholder="Kode Pos" style="border-radius: 8px; font-size: 0.9rem; padding: 0.6rem 1rem;">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">Detail Alamat (Patokan, dll)</label>
                                    <textarea name="detail_alamat" class="form-control" rows="2" placeholder="Cth: Rumah pagar hitam, depan masjid..." style="border-radius: 8px; font-size: 0.9rem; padding: 0.6rem 1rem;"></textarea>
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
                            <i class="bi bi-arrow-right-circle me-2"></i> Lanjut ke Pembayaran
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
                                    <img src="{{ Storage::url($cart->produk->foto) }}" alt="{{ $cart->produk->nama_produk }}" style="width: 100%; height: 100%; object-fit: cover;">
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

<!-- Modal Konfirmasi Alamat -->
<div class="modal fade" id="modalKonfirmasiAlamat" tabindex="-1" aria-labelledby="modalKonfirmasiAlamatLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: #fdf5eb; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" id="modalKonfirmasiAlamatLabel" style="color: #8b5a2b; font-weight: 700; font-size: 1.1rem;">
                    <i class="bi bi-geo-alt-fill me-2"></i>Alamat Pin Point Pada Peta
                </h5>
                <button type="button" class="btn-close" id="btn-tutup-alamat" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3 text-center">
                    <div id="modal-map-preview" style="height: 150px; width: 100%; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 1rem;"></div>
                    <strong style="font-size: 0.95rem; color: #2d3748; display: block; margin-bottom: 0.5rem;" id="modal-alamat-text">Sedang memuat alamat...</strong>
                </div>
                
                <div style="background: #fff3cd; border: 1px solid #ffecb5; border-radius: 8px; padding: 0.8rem; font-size: 0.8rem; color: #856404;">
                    <ul class="mb-0 ps-3 text-start">
                        <li class="mb-1">Pastikan lokasi anda sesuai dengan peta diatas</li>
                        <li class="mb-1">Jika terjadi kesalahan pemilihan pada peta di luar tanggung jawab kami</li>
                        <li>Jika salah silahkan klik tombol tutup dan silahkan ubah lokasi pada peta utama</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #f0f0f0;">
                <button type="button" class="btn btn-light" id="btn-batal-alamat" style="border-radius: 8px; font-weight: 600;">Tutup</button>
                <button type="button" class="btn text-white" id="btn-simpan-alamat" style="background: #a66a38; border-radius: 8px; font-weight: 600;">Simpan</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addressInput = document.getElementById('alamat_pengiriman');
        const searchInput = document.getElementById('map_search');
        const suggestionsList = document.getElementById('map-suggestions');
        const latInput = document.getElementById('latitude_pengiriman');
        const lonInput = document.getElementById('longitude_pengiriman');
        let timeoutId;
        
        const konfirmasiModal = new bootstrap.Modal(document.getElementById('modalKonfirmasiAlamat'));
        let pendingLat = null, pendingLon = null, pendingAddress = '', pendingKecamatan = '', pendingKota = '', pendingKodePos = '';
        let alamatDikonfirmasi = false;
        let ongkirValid = false;
        let miniMap = null;
        let miniMarker = null;

        // Form Submission Validation
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            if (!alamatDikonfirmasi) {
                e.preventDefault();
                alert('Silakan konfirmasi titik alamat pengiriman di peta terlebih dahulu.');
                return;
            }
            if (!ongkirValid) {
                e.preventDefault();
                alert('Gagal memproses ongkos kirim. Pastikan alamat valid dan lokasi toko penjual telah diatur oleh admin.');
            }
        });
        
        // Leaflet Map Initialization
        // Default to Bandung if location not found
        let defaultLat = -6.914744;
        let defaultLon = 107.609810;
        
        const map = L.map('map').setView([defaultLat, defaultLon], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let marker = L.marker([defaultLat, defaultLon], {draggable: true}).addTo(map);

        function updateLocation(lat, lng, fetchAddress = true, predefinedAddress = null, preAddrDetails = null) {
            alamatDikonfirmasi = false; // Reset status konfirmasi
            const newLatLng = new L.LatLng(lat, lng);
            marker.setLatLng(newLatLng);
            map.panTo(newLatLng);
            
            if (fetchAddress || predefinedAddress) {
                pendingLat = lat;
                pendingLon = lng;
                reverseGeocodeAndConfirm(lat, lng, predefinedAddress, preAddrDetails);
            }
        }

        // On Marker Drag
        marker.on('dragend', function(e) {
            const position = marker.getLatLng();
            updateLocation(position.lat, position.lng);
        });

        // On Map Click
        map.on('click', function(e) {
            updateLocation(e.latlng.lat, e.latlng.lng);
        });

        // Try getting user's current location
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                // Initialize map with user's position but don't force confirmation yet
                const newLatLng = new L.LatLng(position.coords.latitude, position.coords.longitude);
                marker.setLatLng(newLatLng);
                map.panTo(newLatLng);
            }, function(error) {
                console.log("Geolocation error:", error);
            });
        }

        function formatNominatimAddress(addr, placeName = null) {
            let parts = [];
            
            // Nama Tempat (POI)
            let tempat = placeName || addr.amenity || addr.building || addr.shop || addr.office || addr.tourism || '';
            
            // Nama Jalan & Nomor
            let jalan = addr.road || addr.pedestrian || addr.street || addr.path || '';
            let nomor = addr.house_number || '';
            let jalanLengkap = jalan;
            if (jalan && nomor) {
                jalanLengkap = jalan + ' No. ' + nomor;
            } else if (!jalan && addr.hamlet) {
                // Seringkali jalan tidak ada tapi hamlet/dusun ada
                jalanLengkap = addr.hamlet;
            }
            
            if (tempat && tempat !== jalanLengkap) parts.push(tempat);
            if (jalanLengkap && jalanLengkap !== tempat) parts.push(jalanLengkap);
            
            // Kelurahan / Desa
            let kelurahan = addr.village || addr.suburb || addr.neighbourhood || addr.residential || '';
            if (kelurahan && kelurahan !== jalanLengkap && kelurahan !== tempat) parts.push(kelurahan);
            
            // Kecamatan
            let kecamatan = addr.city_district || addr.district || addr.subdistrict || '';
            if (kecamatan && kecamatan !== kelurahan) parts.push(kecamatan);
            
            // Kota / Kabupaten
            let kota = addr.city || addr.town || addr.municipality || addr.county || '';
            if (kota && kota !== kecamatan) parts.push(kota);
            
            // Provinsi & Kode Pos
            let provinsi = addr.state || addr.region || addr.province || '';
            let kodepos = addr.postcode || '';
            let provPos = provinsi;
            if (provinsi && kodepos) {
                provPos = provinsi + ' ' + kodepos;
            } else if (kodepos) {
                provPos = kodepos;
            }
            if (provPos) parts.push(provPos);
            
            // Negara
            let negara = addr.country || 'Indonesia';
            if (negara) parts.push(negara);
            
            // Gabungkan dengan koma
            return parts.join(', ');
        }

        function reverseGeocodeAndConfirm(lat, lon, predefinedAddress = null, preAddrDetails = null) {
            document.getElementById('modal-alamat-text').innerText = predefinedAddress ? predefinedAddress : 'Sedang memuat alamat...';
            konfirmasiModal.show();
            
            // Initialize mini map if not yet
            setTimeout(() => {
                if (!miniMap) {
                    miniMap = L.map('modal-map-preview').setView([lat, lon], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMap);
                    miniMarker = L.marker([lat, lon]).addTo(miniMap);
                } else {
                    const newLatLng = new L.LatLng(lat, lon);
                    miniMap.setView(newLatLng, 15);
                    miniMarker.setLatLng(newLatLng);
                    miniMap.invalidateSize();
                }
            }, 300);

            if (predefinedAddress && preAddrDetails) {
                pendingAddress = predefinedAddress;
                pendingKecamatan = preAddrDetails.city_district || preAddrDetails.district || preAddrDetails.subdistrict || '';
                pendingKota = preAddrDetails.city || preAddrDetails.town || preAddrDetails.municipality || preAddrDetails.county || '';
                pendingKodePos = preAddrDetails.postcode || '';
                return;
            }

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.address) {
                        const addr = data.address;
                        let formattedAddress = formatNominatimAddress(addr, data.name || null);
                        
                        if (!formattedAddress) {
                            formattedAddress = data.display_name;
                        }
                        
                        pendingAddress = formattedAddress;
                        document.getElementById('modal-alamat-text').innerText = formattedAddress;
                        
                        pendingKecamatan = addr.city_district || addr.district || addr.subdistrict || '';
                        pendingKota = addr.city || addr.town || addr.municipality || addr.county || '';
                        pendingKodePos = addr.postcode || '';
                    } else {
                        pendingAddress = "Lokasi yang dipilih pada peta";
                        document.getElementById('modal-alamat-text').innerText = pendingAddress;
                    }
                })
                .catch(err => {
                    console.error('Reverse Geocode Error:', err);
                    document.getElementById('modal-alamat-text').innerText = 'Gagal memuat alamat';
                });
        }

        document.getElementById('btn-simpan-alamat').addEventListener('click', function() {
            alamatDikonfirmasi = true;
            latInput.value = pendingLat;
            lonInput.value = pendingLon;
            addressInput.value = pendingAddress;
            document.getElementById('kecamatan').value = pendingKecamatan;
            document.getElementById('kota').value = pendingKota;
            document.getElementById('kode_pos').value = pendingKodePos;
            
            calculateOngkir(pendingAddress, pendingLat, pendingLon);
            konfirmasiModal.hide();
        });
        
        const closeModalHandler = function() {
            konfirmasiModal.hide();
        };
        document.getElementById('btn-batal-alamat').addEventListener('click', closeModalHandler);
        document.getElementById('btn-tutup-alamat').addEventListener('click', closeModalHandler);

        // Search Autocomplete
        searchInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const query = this.value.trim();

            if (query.length < 3) {
                suggestionsList.style.display = 'none';
                return;
            }

            timeoutId = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=15&addressdetails=1&dedupe=0`)
                    .then(response => response.json())
                    .then(data => {
                        suggestionsList.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                // Ambil nama tempat dari display_name (biasanya sebelum koma pertama)
                                let placeName = item.name || item.display_name.split(',')[0];
                                let alamatLengkap = item.address ? formatNominatimAddress(item.address, placeName) : item.display_name;
                                const li = document.createElement('li');
                                li.className = 'list-group-item list-group-item-action';
                                li.style.cssText = 'cursor: pointer; padding: 0.6rem 1rem; font-size: 0.85rem;';
                                li.innerHTML = `<i class="bi bi-geo-alt-fill text-muted me-2"></i> ${alamatLengkap}`;
                                
                                li.addEventListener('click', function() {
                                    searchInput.value = alamatLengkap;
                                    suggestionsList.style.display = 'none';
                                    updateLocation(item.lat, item.lon, false, alamatLengkap, item.address || null);
                                });
                                suggestionsList.appendChild(li);
                            });
                            suggestionsList.style.display = 'block';
                        } else {
                            const li = document.createElement('li');
                            li.className = 'list-group-item text-muted text-center';
                            li.style.cssText = 'padding: 0.6rem 1rem; font-size: 0.85rem;';
                            li.innerText = 'Alamat tidak ditemukan. Coba gunakan kata kunci yang lebih lengkap.';
                            suggestionsList.appendChild(li);
                            suggestionsList.style.display = 'block';
                        }
                    })
                    .catch(err => {
                        console.error('Geocoding error:', err);
                    });
            }, 500);
        });

        document.addEventListener('click', function(e) {
            if (e.target !== searchInput && !suggestionsList.contains(e.target)) {
                suggestionsList.style.display = 'none';
            }
        });

        const baseTotal = parseInt({{ $total }}) || 0;
        let currentOngkir = 0;
        
        function formatRupiah(amount) {
            return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function updateTotals() {
            const ppn = baseTotal * 0.11;
            const grandTotal = baseTotal + ppn + currentOngkir;
            
            document.getElementById('ppn-display').innerText = formatRupiah(Math.round(ppn));
            document.getElementById('grand-total-display').innerText = formatRupiah(Math.round(grandTotal));
        }

        function calculateOngkir(address, lat, lon) {
            const ongkirDisplay = document.getElementById('ongkir-display');
            const jarakText = document.getElementById('jarak-info');
            const ongkirInput = document.getElementById('biaya_ongkir');

            ongkirDisplay.innerHTML = '<span class="spinner-border spinner-border-sm text-primary" role="status"></span> Menghitung...';
            ongkirValid = false;
            
            fetch('{{ route("pelanggan.checkout.ongkir", ["perusahaan_slug" => request()->route("perusahaan_slug")]) }}', {
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
                if (data.success) {
                    currentOngkir = parseInt(data.ongkir) || 0;
                    ongkirDisplay.innerHTML = formatRupiah(currentOngkir) + '<br><small style="color: #999; font-size: 0.7rem;">(' + data.range_km + ')</small>';
                    ongkirInput.value = currentOngkir;
                    ongkirValid = true;
                    
                    if(jarakText) jarakText.style.display = 'none';
                    updateTotals();
                } else {
                    ongkirDisplay.innerHTML = '<span class="text-danger" style="font-size:0.75rem;">' + (data.message || 'Gagal menghitung') + '</span>';
                    if(jarakText) jarakText.style.display = 'none';
                    currentOngkir = 0;
                    ongkirInput.value = '';
                    ongkirValid = false;
                    updateTotals();
                }
            })
            .catch(err => {
                ongkirDisplay.innerHTML = '<span class="text-danger" style="font-size:0.75rem;">Error koneksi</span>';
                currentOngkir = 0;
                ongkirInput.value = '';
                ongkirValid = false;
                updateTotals();
            });
        }

        // Initialize totals on page load
        updateTotals();
        
        // Wait a bit for modal/container to fully render before invalidating map size
        setTimeout(() => {
            map.invalidateSize();
        }, 500);
    });
</script>
@endpush

@endsection


