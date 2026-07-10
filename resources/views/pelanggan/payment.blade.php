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
                                    
                                    <!-- Info untuk Tunai dihapus karena sudah jelas dari pilihan checkout sebelumnya -->
                                    
                                    <!-- Info untuk Transfer -->
                                    <div id="transfer-info" style="margin-top: 1rem; display: none;">
                                        <h6 class="mb-3" style="font-weight: 700; font-size: 0.9rem;">Pilih Jenis Transfer</h6>
                                        
                                        <div class="row g-2 mb-3">
                                            <div class="col-sm-6">
                                                <input class="btn-check" type="radio" name="metode_transfer" id="transfer_midtrans" value="midtrans_va" checked>
                                                <label class="payment-card w-100 text-start p-3 bg-white" for="transfer_midtrans" style="border-radius: 8px; display: block; height: 100%;">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-robot fs-5" style="color: #27ae60;"></i>
                                                        <div>
                                                            <strong style="display: block; font-size: 0.9rem; color: #2d3748;">Virtual Account Midtrans</strong>
                                                            <span style="font-size: 0.75rem; color: #888;">Bayar otomatis melalui VA Midtrans</span>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="col-sm-6">
                                                <input class="btn-check" type="radio" name="metode_transfer" id="transfer_manual" value="manual">
                                                <label class="payment-card w-100 text-start p-3 bg-white" for="transfer_manual" style="border-radius: 8px; display: block; height: 100%;">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-person-lines-fill fs-5" style="color: #1565c0;"></i>
                                                        <div>
                                                            <strong style="display: block; font-size: 0.9rem; color: #2d3748;">Transfer Manual</strong>
                                                            <span style="font-size: 0.75rem; color: #888;">Transfer ke rekening perusahaan lalu upload bukti bayar</span>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Midtrans Details -->
                                        <div id="transfer_midtrans_details" style="background: #eef8f1; border: 1px solid #d4ecd9; border-radius: 6px; padding: 1rem; color: #27ae60;">
                                            @if(!$midtransEnabled)
                                                <div style="background: #fff3cd; padding: 0.8rem; border-radius: 6px; margin-bottom: 0.8rem; font-size: 0.85rem; border: 1px solid #ffeeba; color: #856404;">
                                                    <em>Metode pembayaran Virtual Account Midtrans saat ini belum diaktifkan. Silakan gunakan Transfer Manual.</em>
                                                </div>
                                            @else
                                                <div class="mb-2">
                                                    <label class="form-label mb-1" style="font-weight: 500; font-size: 0.9rem;">Pilih Bank Virtual Account <span class="text-danger">*</span></label>
                                                    <select name="bank_va" id="bank_va" class="form-select" style="font-size: 0.9rem;">
                                                        <option value="">Pilih Bank</option>
                                                        @foreach($supportedVABanks as $bank)
                                                            <option value="{{ $bank['code'] }}">{{ $bank['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div style="font-size: 0.8rem; margin-top: 10px; color: #555;">
                                                    <i class="fas fa-info-circle me-1 text-primary"></i> 
                                                    Nomor Virtual Account akan diberikan setelah Anda menekan tombol "Buat Pesanan".
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Manual Details -->
                                        <div id="transfer_manual_details" style="display: none; background: #e3f2fd; border: 1px solid #bbdefb; border-radius: 6px; padding: 1rem; color: #1565c0;">
                                            <p class="mb-3" style="font-size: 0.85rem; color: #1565c0;">Silakan transfer sejumlah <strong class="total-pembayaran-text">Rp {{ number_format($total, 0, ',', '.') }}</strong> ke rekening berikut:</p>
                                            
                                            @if(isset($rekeningBanks) && $rekeningBanks->count() > 0)
                                                <div class="rekening-list mb-3">
                                                    @foreach($rekeningBanks as $index => $rekening)
                                                        <div class="form-check mb-2 p-3 bg-white" style="border: 1px solid #bbdefb; border-radius: 6px;">
                                                            <input class="form-check-input ms-1" type="radio" name="rekening_id" id="rekening_{{ $rekening->id }}" value="{{ $rekening->id }}" {{ $index === 0 ? 'checked' : '' }} required>
                                                            <label class="form-check-label w-100 ms-2" for="rekening_{{ $rekening->id }}" style="cursor: pointer; font-size: 0.85rem; color: #1565c0;">
                                                                <div style="font-weight: 600; margin-bottom: 2px;">{{ $rekening->nama_akun }}</div>
                                                                <div style="color: #666; display: flex; align-items: center; gap: 8px;">
                                                                    <span>No Rekening: <strong>{{ $rekening->nomor_rekening }}</strong></span>
                                                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem; border-color: #bbdefb; color: #a66a38; border-radius: 4px;" onclick="copyToClipboard(this, '{{ $rekening->nomor_rekening }}', event)" title="Copy Nomor Rekening">
                                                                        <i class="bi bi-clipboard"></i> Copy
                                                                    </button>
                                                                </div>
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
                                                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Upload bukti transfer agar pesanan dapat diverifikasi oleh penjual.</small>
                                            </div>
                                        </div>
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
    function copyToClipboard(btn, text, event) {
        event.preventDefault(); // Prevent triggering radio select if button is clicked inside label
        event.stopPropagation();
        
        const originalHtml = btn.innerHTML;
        const originalColor = btn.style.color;
        
        const onSuccess = () => {
            btn.innerHTML = '<i class="bi bi-check2"></i> Tersalin';
            btn.style.color = '#198754'; // Bootstrap success color
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.style.color = originalColor;
            }, 2000);
        };

        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(onSuccess).catch(err => {
                console.error('Failed to copy text: ', err);
            });
        } else {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            // Avoid scrolling to bottom
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                onSuccess();
            } catch (err) {
                console.error('Fallback: Oops, unable to copy', err);
            }
            document.body.removeChild(textArea);
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentRadios = document.querySelectorAll('input[name="payment_gateway"]');
        
        function handlePaymentChange(value) {
            const transferInfoBox = document.getElementById('transfer-info');
            const btnProcess = document.getElementById('btn-process-payment');
            const inputBukti = document.getElementById('bukti_pembayaran');
            
            if (value === 'transfer') {
                if (transferInfoBox) transferInfoBox.style.display = 'block';
                updateTransferSubMethod();
            } else if (value === 'tunai') {
                if (transferInfoBox) transferInfoBox.style.display = 'none';
                btnProcess.innerHTML = '<i class="bi bi-cash-coin me-2"></i> Buat Pesanan (Tunai)';
                if (inputBukti) inputBukti.removeAttribute('required');
                document.querySelectorAll('input[name="rekening_id"]').forEach(r => r.removeAttribute('required'));
                document.querySelectorAll('input[name="bank_va"]').forEach(r => r.removeAttribute('required'));
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
                if (inputBukti) inputBukti.removeAttribute('required');
                document.querySelectorAll('input[name="rekening_id"]').forEach(r => r.removeAttribute('required'));
                document.querySelectorAll('input[name="bank_va"]').forEach(r => r.setAttribute('required', 'required'));
                btnProcess.innerHTML = '<i class="bi bi-phone me-2"></i> Buat Pesanan';
            } else {
                midtransDetails.style.display = 'none';
                manualDetails.style.display = 'block';
                if (inputBukti) inputBukti.setAttribute('required', 'required');
                document.querySelectorAll('input[name="rekening_id"]').forEach(r => r.setAttribute('required', 'required'));
                document.querySelectorAll('input[name="bank_va"]').forEach(r => r.removeAttribute('required'));
                btnProcess.innerHTML = '<i class="bi bi-lock-fill me-2"></i> Buat Pesanan (Transfer Manual)';
            }
        }
        
        const transferRadios = document.querySelectorAll('input[name="metode_transfer"]');
        transferRadios.forEach(radio => {
            radio.addEventListener('change', updateTransferSubMethod);
        });

        function updateOngkirDisplay() {
            // Remove tunai_ambil dependency as it's determined at checkout page
            const biayaKirimDisplay = document.getElementById('biaya-kirim-display');
            const totalDisplay = document.getElementById('total-pembayaran-display');
            
            const currentOngkir = parseInt(biayaKirimDisplay.dataset.ongkir) || 0;
            const subtotal = parseInt(totalDisplay.dataset.subtotal) || 0;
            const ppn = parseInt(totalDisplay.dataset.ppn) || 0;
            
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
                
                e.preventDefault(); // Prevent standard form submission

                // Set loading state
                btnProcess.disabled = true;
                btnProcess.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';

                // Send AJAX request
                const formData = new FormData(paymentForm);
                
                fetch(paymentForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.snap_token) {
                            // Midtrans Flow
                            snap.pay(data.snap_token, {
                                onSuccess: function(result){
                                    window.location.href = data.redirect_url + "?payment_status=success";
                                },
                                onPending: function(result){
                                    window.location.href = data.redirect_url + "?payment_status=pending";
                                },
                                onError: function(result){
                                    window.location.href = data.redirect_url + "?payment_status=error";
                                },
                                onClose: function(){
                                    alert("Pembayaran belum diselesaikan. Silakan lanjutkan pembayaran pada pesanan Anda.");
                                    window.location.href = data.redirect_url + "?payment_status=pending";
                                }
                            });
                        } else {
                            // Manual/Tunai Flow
                            window.location.href = data.redirect_url;
                        }
                    } else {
                        alert(data.message || 'Gagal memproses pembayaran.');
                        btnProcess.disabled = false;
                        btnProcess.innerHTML = '<i class="bi bi-lock-fill me-2"></i> Buat Pesanan';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan pada server. Silakan coba lagi.');
                    btnProcess.disabled = false;
                    btnProcess.innerHTML = '<i class="bi bi-lock-fill me-2"></i> Buat Pesanan';
                });
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
    });
</script>
@endpush
@endsection
