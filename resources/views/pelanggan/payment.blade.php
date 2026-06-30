@extends('layouts.pelanggan')

@section('content')
<div class="container py-4">
    <!-- Header Title & Steps -->
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill mb-2" style="background: #fdf5eb; color: #8b5a2b; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px;">
            PEMBAYARAN <i class="bi bi-credit-card"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Pilih Metode Pembayaran</h4>
        <p class="text-muted mb-3" style="font-size: 0.85rem;">Selesaikan pesananmu dengan memilih metode pembayaran.</p>
        
        <div class="d-flex justify-content-center flex-wrap gap-2">
            <!-- Step 1 (Inactive) -->
            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-3 bg-white shadow-sm" style="border: 1px solid #f0f0f0;">
                <div class="d-flex align-items-center justify-content-center rounded" style="width: 28px; height: 28px; background: #f8f9fa;">
                    <i class="bi bi-cart text-muted" style="font-size: 0.9rem;"></i>
                </div>
                <span class="text-muted" style="font-weight: 600; font-size: 0.85rem;">Keranjang</span>
            </div>
            
            <!-- Step 2 (Inactive) -->
            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-3 bg-white shadow-sm" style="border: 1px solid #f0f0f0;">
                <div class="d-flex align-items-center justify-content-center rounded" style="width: 28px; height: 28px; background: #f8f9fa;">
                    <i class="bi bi-truck text-muted" style="font-size: 0.9rem;"></i>
                </div>
                <span class="text-muted" style="font-weight: 600; font-size: 0.85rem;">Checkout</span>
            </div>
            
            <!-- Step 3 (Active) -->
            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-3 bg-white shadow-sm" style="border: 1px solid #8b5a2b;">
                <div class="d-flex align-items-center justify-content-center rounded" style="width: 28px; height: 28px; background: #fdf5eb;">
                    <i class="bi bi-receipt" style="color: #8b5a2b; font-size: 0.9rem;"></i>
                </div>
                <span style="color: #8b5a2b; font-weight: 600; font-size: 0.85rem;">Pembayaran</span>
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
                    <form action="{{ route('pelanggan.checkout.payment.process', ['perusahaan_slug' => $perusahaan_slug]) }}" method="POST" id="paymentForm" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Metode Pembayaran -->
                        <div class="d-flex gap-3 mb-4">
                            <div style="color: #8b5a2b; font-size: 1.5rem;"><i class="bi bi-credit-card-2-front"></i></div>
                            <div style="flex: 1;">
                                <h6 style="font-weight: 700; color: #2d3748; margin-bottom: 0.2rem;">Metode Pembayaran</h6>
                                <p style="font-size: 0.8rem; color: #888; margin-bottom: 1rem;">Pilih metode pembayaran yang tersedia</p>
                                
                                <style>
                                    .payment-card {
                                        border: 1px solid #e2e8f0;
                                        transition: all 0.2s ease;
                                        cursor: pointer;
                                    }
                                    .btn-check:checked + .payment-card {
                                        border-color: #8b5a2b !important;
                                        background-color: #fdf5eb !important;
                                        box-shadow: 0 0 0 1px #8b5a2b;
                                    }
                                </style>
                                <div class="mb-3">
                                    <div class="row g-2">
                                        <!-- Tunai -->
                                        <div class="col-sm-6">
                                            <input type="radio" class="btn-check" name="payment_gateway" id="gateway_tunai" value="tunai" required {{ old('payment_gateway') == 'tunai' ? 'checked' : '' }}>
                                            <label class="payment-card w-100 text-start p-3 bg-white" for="gateway_tunai" style="border-radius: 8px; display: block; height: 100%;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-cash-coin fs-5" style="color: #8b5a2b;"></i>
                                                    <div>
                                                        <strong style="display: block; font-size: 0.9rem; color: #2d3748;">Tunai</strong>
                                                        <span style="font-size: 0.75rem; color: #888;">COD / Ambil di Toko</span>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        <!-- Transfer -->
                                        <div class="col-sm-6">
                                            <input type="radio" class="btn-check" name="payment_gateway" id="gateway_transfer" value="transfer" required {{ old('payment_gateway') == 'transfer' ? 'checked' : '' }}>
                                            <label class="payment-card w-100 text-start p-3 bg-white" for="gateway_transfer" style="border-radius: 8px; display: block; height: 100%;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-bank fs-5" style="color: #8b5a2b;"></i>
                                                    <div>
                                                        <strong style="display: block; font-size: 0.9rem; color: #2d3748;">Transfer</strong>
                                                        <span style="font-size: 0.75rem; color: #888;">Midtrans / Manual</span>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    @error('payment_gateway') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                                    
                                    <!-- Info untuk Tunai -->
                                    <div id="tunai-info" style="background: #fff8e1; border: 1px solid #ffecb3; border-radius: 6px; padding: 1rem; margin-top: 1rem; display: none; color: #f57f17;">
                                        <h6 class="mb-3" style="font-weight: 700; font-size: 0.9rem;"><i class="bi bi-cash-coin" style="color: #fbc02d; margin-right: 5px;"></i> Pembayaran Tunai</h6>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="metode_tunai" id="tunai_cod" value="cod" checked>
                                                <label class="form-check-label" style="font-size: 0.85rem; color: #555;" for="tunai_cod">
                                                    Bayar di Tempat (COD)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="metode_tunai" id="tunai_ambil" value="ambil_di_toko">
                                                <label class="form-check-label" style="font-size: 0.85rem; color: #555;" for="tunai_ambil">
                                                    Ambil di Toko
                                                </label>
                                            </div>
                                        </div>
                                        <p class="mb-0 mt-3 text-muted" style="font-size: 0.75rem;">* Pesanan akan diproses dan dibayar langsung secara tunai.</p>
                                    </div>
                                    
                                    <!-- Info untuk Transfer -->
                                    <div id="transfer-info" style="background: #eef8f1; border: 1px solid #d4ecd9; border-radius: 6px; padding: 1rem; margin-top: 1rem; display: none; color: #27ae60;">
                                        <h6 class="mb-3" style="font-weight: 700; font-size: 0.9rem;"><i class="bi bi-bank" style="color: #27ae60; margin-right: 5px;"></i> Pembayaran Transfer</h6>
                                        
                                        <div class="d-flex gap-3 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="metode_transfer" id="transfer_midtrans" value="midtrans" checked>
                                                <label class="form-check-label" style="font-size: 0.85rem; color: #555;" for="transfer_midtrans">
                                                    Otomatis (Midtrans)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="metode_transfer" id="transfer_manual" value="manual_transfer">
                                                <label class="form-check-label" style="font-size: 0.85rem; color: #555;" for="transfer_manual">
                                                    Transfer Manual
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Midtrans Details -->
                                        <div id="transfer_midtrans_details">
                                            <p class="mb-2" style="font-size: 0.85rem;">Pembayaran diverifikasi secara otomatis. Anda bisa membayar menggunakan:</p>
                                            <ul class="mb-0 ps-3" style="font-size: 0.8rem;">
                                                <li>Virtual Account (BCA, BNI, BRI, Mandiri, dll)</li>
                                                <li>E-Wallet (GoPay, ShopeePay, dll)</li>
                                                <li>QRIS</li>
                                            </ul>
                                        </div>

                                        <!-- Manual Details -->
                                        <div id="transfer_manual_details" style="display: none;">
                                            <p class="mb-3" style="font-size: 0.85rem; color: #1565c0;">Silakan transfer sejumlah <strong class="total-pembayaran-text">Rp {{ number_format($total, 0, ',', '.') }}</strong> ke rekening berikut:</p>
                                            
                                            @if(isset($rekeningBanks) && $rekeningBanks->count() > 0)
                                                <div class="rekening-list mb-3">
                                                    @foreach($rekeningBanks as $index => $rekening)
                                                        <div class="form-check mb-2 p-3 bg-white" style="border: 1px solid #bbdefb; border-radius: 6px;">
                                                            <input class="form-check-input ms-1" type="radio" name="rekening_id" id="rekening_{{ $rekening->id }}" value="{{ $rekening->id }}" {{ $index === 0 ? 'checked' : '' }} required>
                                                            <label class="form-check-label w-100 ms-2" for="rekening_{{ $rekening->id }}" style="cursor: pointer; font-size: 0.85rem; color: #1565c0;">
                                                                <div style="font-weight: 600; margin-bottom: 2px;">{{ $rekening->nama_akun }}</div>
                                                                <div style="color: #666;">No Rekening: <strong>{{ $rekening->nomor_rekening }}</strong></div>
                                                                @if($rekening->atas_nama)
                                                                    <div style="color: #666;">Atas Nama: <strong>{{ $rekening->atas_nama }}</strong></div>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div style="background: white; padding: 0.8rem; border-radius: 6px; margin-bottom: 0.8rem; font-size: 0.85rem; border: 1px solid #bbdefb; color: #888;">
                                                    <em>Rekening perusahaan belum diatur. Silakan hubungi admin.</em>
                                                </div>
                                            @endif

                                            <div class="mt-3">
                                                <label class="form-label" style="font-size: 0.85rem; color: #555; font-weight: 600;">Upload Bukti Transfer <span class="text-danger">*</span></label>
                                                <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" class="form-control form-control-sm" accept="image/*,.pdf">
                                                <small class="text-muted" style="font-size: 0.7rem;">Format: JPG, PNG, PDF. Maks 5MB.</small>
                                            </div>
                                            
                                            <p class="mb-0 mt-2 text-muted" style="font-size: 0.75rem;">* Pesanan akan diproses setelah bukti pembayaran diverifikasi oleh admin.</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Info untuk Transfer Bank -->
                                    <div id="manual-info" style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 6px; padding: 1rem; margin-top: 1rem; display: none; color: #1565c0;">
                                        <h6 class="mb-2" style="font-weight: 700; font-size: 0.9rem;"><i class="bi bi-bank" style="color: #2196f3; margin-right: 5px;"></i> Transfer Bank Manual</h6>
                                        <p class="mb-3" style="font-size: 0.85rem;">Silakan transfer sejumlah <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong> ke rekening berikut:</p>
                                        
                                        @if($perusahaan && $perusahaan->nama_bank && $perusahaan->nomor_rekening)
                                        <div style="background: white; padding: 0.8rem; border-radius: 6px; margin-bottom: 0.8rem; font-size: 0.85rem; border: 1px solid #bbdefb;">
                                            <table style="width: 100%;">
                                                <tr>
                                                    <td style="width: 35%; color: #666; padding-bottom: 4px;">Bank</td>
                                                    <td style="padding-bottom: 4px;">: <strong>{{ $perusahaan->nama_bank }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: #666; padding-bottom: 4px;">Nomor Rekening</td>
                                                    <td style="padding-bottom: 4px;">: <strong>{{ $perusahaan->nomor_rekening }}</strong></td>
                                                </tr>
                                                @if($perusahaan->nama_pemilik_rekening)
                                                <tr>
                                                    <td style="color: #666;">Atas Nama</td>
                                                    <td>: <strong>{{ $perusahaan->nama_pemilik_rekening }}</strong></td>
                                                </tr>
                                                @endif
                                            </table>
                                        </div>
                                        @else
                                        <div style="background: white; padding: 0.8rem; border-radius: 6px; margin-bottom: 0.8rem; font-size: 0.85rem; border: 1px solid #bbdefb; color: #888;">
                                            <em>Data rekening perusahaan (Kas Bank) akan diverifikasi oleh Admin. Silakan konfirmasi via WhatsApp setelah pesanan dibuat.</em>
                                        </div>
                                        @endif
                                        
                                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">* Pesanan akan diproses setelah bukti pembayaran diverifikasi oleh admin.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn w-100" id="btn-process-payment" style="background: #a66a38; color: white; border-radius: 8px; padding: 0.8rem; font-weight: 600; font-size: 1rem; box-shadow: 0 4px 10px rgba(139, 90, 43, 0.2);">
                            <i class="bi bi-lock-fill me-2"></i> Buat Pesanan
                        </button>
                        
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
                            <strong style="font-size: 0.9rem; color: #2d3748;">Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</strong>
                        </div>
                        @endforeach
                    </div>

                    <!-- Total Section -->
                    <div class="bg-light p-3 rounded-3 mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span style="font-size: 0.85rem; color: #666;">Subtotal Produk</span>
                            <span style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span style="font-size: 0.85rem; color: #666;">Biaya Kirim</span>
                            <span id="biaya-kirim-display" data-ongkir="{{ $ongkir }}" style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">
                                Rp {{ number_format($ongkir, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <span style="font-size: 0.85rem; color: #666;">PPN (11%)</span>
                            <span style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">Rp {{ number_format($ppn, 0, ',', '.') }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <strong style="font-size: 1rem; color: #2d3748;">Total Pembayaran</strong>
                            <strong id="total-pembayaran-display" data-subtotal="{{ $subtotal }}" data-ppn="{{ $ppn }}" style="font-size: 1.2rem; color: #8b5a2b;">Rp {{ number_format($total, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if(config('midtrans.is_production'))
    <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
@else
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentRadios = document.querySelectorAll('input[name="payment_gateway"]');
        
        function handlePaymentChange(value) {
            const transferInfoBox = document.getElementById('transfer-info');
            const tunaiInfoBox = document.getElementById('tunai-info');
            const btnProcess = document.getElementById('btn-process-payment');
            
            if (value === 'transfer') {
                if (transferInfoBox) transferInfoBox.style.display = 'block';
                if (tunaiInfoBox) tunaiInfoBox.style.display = 'none';
                updateTransferSubMethod();
            } else if (value === 'tunai') {
                if (transferInfoBox) transferInfoBox.style.display = 'none';
                if (tunaiInfoBox) tunaiInfoBox.style.display = 'block';
                btnProcess.innerHTML = '<i class="bi bi-cash-coin me-2"></i> Buat Pesanan (Tunai)';
                
                const inputBukti = document.getElementById('bukti_pembayaran');
                if (inputBukti) inputBukti.removeAttribute('required');
            }
        }
        
        function updateTransferSubMethod() {
            const btnProcess = document.getElementById('btn-process-payment');
            const isMidtrans = document.getElementById('transfer_midtrans').checked;
            const midtransDetails = document.getElementById('transfer_midtrans_details');
            const manualDetails = document.getElementById('transfer_manual_details');
            const inputBukti = document.getElementById('bukti_pembayaran');
            
            if (isMidtrans) {
                midtransDetails.style.display = 'block';
                manualDetails.style.display = 'none';
                inputBukti.removeAttribute('required');
                btnProcess.innerHTML = '<i class="bi bi-phone me-2"></i> Bayar dengan Midtrans';
            } else {
                midtransDetails.style.display = 'none';
                manualDetails.style.display = 'block';
                inputBukti.setAttribute('required', 'required');
                btnProcess.innerHTML = '<i class="bi bi-lock-fill me-2"></i> Buat Pesanan (Transfer Manual)';
            }
        }
        
        const transferRadios = document.querySelectorAll('input[name="metode_transfer"]');
        transferRadios.forEach(radio => {
            radio.addEventListener('change', updateTransferSubMethod);
        });

        const tunaiRadios = document.querySelectorAll('input[name="metode_tunai"]');
        tunaiRadios.forEach(radio => {
            radio.addEventListener('change', updateOngkirDisplay);
        });
        
        function updateOngkirDisplay() {
            const isAmbilDiToko = document.getElementById('tunai_ambil') && document.getElementById('tunai_ambil').checked;
            const isTunai = document.getElementById('gateway_tunai').checked;
            
            const biayaKirimDisplay = document.getElementById('biaya-kirim-display');
            const totalDisplay = document.getElementById('total-pembayaran-display');
            
            const originalOngkir = parseInt(biayaKirimDisplay.dataset.ongkir) || 0;
            const subtotal = parseInt(totalDisplay.dataset.subtotal) || 0;
            const ppn = parseInt(totalDisplay.dataset.ppn) || 0;
            
            let currentOngkir = originalOngkir;
            if (isTunai && isAmbilDiToko) {
                currentOngkir = 0;
            }
            
            const currentTotal = subtotal + ppn + currentOngkir;
            
            biayaKirimDisplay.innerText = 'Rp ' + currentOngkir.toLocaleString('id-ID');
            totalDisplay.innerText = 'Rp ' + currentTotal.toLocaleString('id-ID');
            
            document.querySelectorAll('.total-pembayaran-text').forEach(el => {
                el.innerText = `Rp ${currentTotal.toLocaleString('id-ID')}`;
            });
        }

        // Add form submission loading state
        const paymentForm = document.getElementById('paymentForm');
        if (paymentForm) {
            paymentForm.addEventListener('submit', function(e) {
                const btnProcess = document.getElementById('btn-process-payment');
                
                // Double check validation before showing loading
                if (!paymentForm.checkValidity()) {
                    return; // Let browser show validation errors
                }
                
                // Set loading state
                btnProcess.disabled = true;
                btnProcess.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
            });
        }
        
        // Initialize state
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                    handlePaymentChange(this.value);
                    updateOngkirDisplay();
                });
            });

        if (paymentRadios.length > 0) {
            // Check initial state
            const checkedRadio = document.querySelector('input[name="payment_gateway"]:checked');
            if (checkedRadio) {
                handlePaymentChange(checkedRadio.value);
            }
        }

        // Display Snap Popup if token exists (after redirection from controller)
        @if(session('snap_token'))
            snap.pay('{{ session('snap_token') }}', {
                onSuccess: function(result){
                    window.location.href = "{{ route('pelanggan.orders.show', session('order_id')) }}?payment_status=success";
                },
                onPending: function(result){
                    window.location.href = "{{ route('pelanggan.orders.show', session('order_id')) }}?payment_status=pending";
                },
                onError: function(result){
                    window.location.href = "{{ route('pelanggan.orders.show', session('order_id')) }}?payment_status=error";
                },
                onClose: function(){
                    window.location.href = "{{ route('pelanggan.orders.show', session('order_id')) }}";
                }
            });
        @endif
    });
</script>
@endpush
@endsection
