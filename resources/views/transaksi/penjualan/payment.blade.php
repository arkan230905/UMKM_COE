@extends('layouts.app')

@section('title', 'Konfirmasi Pembayaran')

@push('styles')
<style>
    .bg-theme-gradient {
        background: linear-gradient(135deg, var(--sidebar-bg), var(--brown)) !important;
        color: white;
    }
    .text-theme {
        color: var(--brown) !important;
    }
    .card-modern {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .card-modern .card-header {
        border-bottom: none;
        padding: 1.25rem 1.5rem;
    }
    .table-modern {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border);
        margin-bottom: 0;
    }
    .table-modern th {
        background-color: #f8f9fa;
        border-bottom: 2px solid var(--border) !important;
        font-weight: 600;
        color: var(--text-secondary);
        padding: 12px 16px;
    }
    .table-modern td {
        padding: 16px;
        vertical-align: middle;
        border-bottom: 1px solid var(--border);
    }
    .btn-theme {
        background-color: var(--brown) !important;
        color: white !important;
        border: none;
        border-radius: 8px;
        padding: 10px 24px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .btn-theme:hover {
        background-color: var(--brown-light) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(92, 61, 46, 0.2);
    }
    .btn-outline-theme {
        border: 2px solid var(--brown) !important;
        color: var(--brown) !important;
        border-radius: 8px;
        padding: 8px 24px;
        font-weight: 500;
        transition: all 0.3s ease;
        background: transparent;
    }
    .btn-outline-theme:hover {
        background-color: var(--brown) !important;
        color: white !important;
    }
    .summary-box {
        background: linear-gradient(145deg, #ffffff, #f9f6f0);
        border: 1px solid #e8e2d9;
        border-radius: 12px;
        padding: 20px;
    }
    .payment-method-card {
        transition: all 0.2s ease-in-out;
        border-radius: 12px;
        background-color: #fdfbf7;
    }
    .payment-method-card:hover {
        border-color: var(--brown-light) !important;
    }
    .payment-method-selector:checked + .payment-method-card {
        border-color: var(--brown) !important;
        background-color: rgba(138, 107, 72, 0.05) !important;
        box-shadow: 0 4px 12px rgba(138, 107, 72, 0.15);
    }
    .payment-method-selector:checked + .payment-method-card .check-circle {
        background-color: var(--brown);
        border-color: var(--brown) !important;
    }
    .payment-method-selector:checked + .payment-method-card .check-circle i {
        display: block !important;
    }
    .bank-card { 
        transition: all 0.2s ease-in-out; 
        border-radius: 12px;
        background-color: #fdfbf7;
    }
    .bank-selector:checked + .bank-card {
        border-color: var(--brown) !important;
        background-color: rgba(138, 107, 72, 0.05) !important;
        box-shadow: 0 4px 12px rgba(138, 107, 72, 0.15);
    }
    .bank-selector:checked + .bank-card .check-circle {
        background-color: var(--brown);
        border-color: var(--brown) !important;
    }
    .bank-selector:checked + .bank-card .check-circle i {
        display: block !important;
    }
    .payment-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(138, 107, 72, 0.1);
        color: var(--brown);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 15px;
    }
    .payment-method-content {
        display: none;
    }
    .payment-method-content.active {
        display: block;
        animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
@php
    $subtotalGross = collect($payment_data['items'])->sum(function($item) {
        return $item['harga_satuan'] * $item['jumlah'];
    });
    
    $totalDiskonItems = collect($payment_data['items'])->sum(function($item) {
        if (isset($item['diskon_nominal']) && $item['diskon_nominal'] > 0) {
            return $item['diskon_nominal'];
        }
        if (isset($item['diskon_persen']) && $item['diskon_persen'] > 0) {
            return round(($item['harga_satuan'] * $item['jumlah']) * $item['diskon_persen'] / 100);
        }
        return 0;
    });
    
    $totalDiskonTampil = ($payment_data['total_diskon'] ?? 0) > 0
        ? $payment_data['total_diskon']
        : $totalDiskonItems;
        
    $subtotalNet = $subtotalGross - $totalDiskonTampil;
    
    $ppnPersen = $payment_data['ppn_persen'] ?? 0;
    // PPN dihitung 11% dari subtotal setelah diskon
    $totalPpn = ($payment_data['total_ppn'] > 0 && $ppnPersen > 0) 
        ? round($subtotalNet * $ppnPersen / 100) 
        : 0;
        
    $biayaOngkir = $payment_data['biaya_ongkir'] ?? 0;
    
    $totalPembayaran = $subtotalNet + $totalPpn + $biayaOngkir;

    // Update data payment_data agar konsisten untuk form submit
    $payment_data['subtotal_produk'] = $subtotalNet;
    $payment_data['total_diskon'] = $totalDiskonTampil;
    $payment_data['total_ppn'] = $totalPpn;
    $payment_data['total'] = $totalPembayaran;
@endphp
<div class="container-fluid page-wrapper">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-modern mb-4">
                <div class="card-header bg-theme-gradient text-white">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-credit-card me-2"></i>Konfirmasi Pembayaran
                    </h5>
                </div>
                <div class="card-body">

                    
                    <!-- RINGKASAN PESANAN -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Ringkasan Pesanan</h6>
                        @php
                            $adaDiskon = collect($payment_data['items'])->contains(function($item) {
                                return ($item['diskon_persen'] ?? 0) > 0 || ($item['diskon_nominal'] ?? 0) > 0;
                            });
                        @endphp
                        <div class="table-responsive rounded border mb-0">
                            <table class="table table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Harga</th>
                                        @if($adaDiskon)
                                        <th class="text-end">Diskon</th>
                                        @endif
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payment_data['items'] as $item)
                                    @php
                                        $diskonPersen  = $item['diskon_persen'] ?? 0;
                                        $diskonNominal = $item['diskon_nominal'] ?? 0;
                                        if ($diskonNominal == 0 && $diskonPersen > 0) {
                                            $diskonNominal = round(($item['harga_satuan'] * $item['jumlah']) * $diskonPersen / 100);
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            @php $produk = \App\Models\Produk::find($item['produk_id']); @endphp
                                            {{ $produk->nama_produk ?? 'Produk Tidak Ditemukan' }}
                                        </td>
                                        <td class="text-end">{{ $item['jumlah'] }}</td>
                                        <td class="text-end">Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                                        @if($adaDiskon)
                                        <td class="text-end">
                                            @if($diskonPersen > 0 || $diskonNominal > 0)
                                                <span class="text-danger fw-semibold">
                                                    {{ $diskonPersen > 0 ? number_format($diskonPersen, 0).'%' : '' }}
                                                    @if($diskonNominal > 0)
                                                        <br><small>- Rp {{ number_format($diskonNominal, 0, ',', '.') }}</small>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        @endif
                                        <td class="text-end">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr>
                    <!-- Detail Pembayaran -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Detail Pembayaran</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <small class="text-muted">Subtotal Produk (Gross)</small>
                                    <div class="fw-bold">Rp {{ number_format($subtotalGross, 0, ',', '.') }}</div>
                                </div>
                                @if($totalDiskonTampil > 0)
                                <div class="mb-2">
                                    <small class="text-muted">Total Diskon</small>
                                    <div class="fw-bold text-danger">- Rp {{ number_format($totalDiskonTampil, 0, ',', '.') }}</div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Subtotal Setelah Diskon</small>
                                    <div class="fw-bold text-success">Rp {{ number_format($subtotalNet, 0, ',', '.') }}</div>
                                </div>
                                @endif
                                @if($biayaOngkir > 0)
                                <div class="mb-2">
                                    <small class="text-muted">Biaya Ongkir</small>
                                    <div class="fw-bold">Rp {{ number_format($biayaOngkir, 0, ',', '.') }}</div>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($totalPpn > 0)
                                <div class="mb-2">
                                    <small class="text-muted">PPN ({{ $ppnPersen }}%)</small>
                                    <div class="fw-bold">Rp {{ number_format($totalPpn, 0, ',', '.') }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Total -->
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-7 ms-auto">
                                <div class="summary-box">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-secondary">Total Pembayaran:</span>
                                        <span class="fw-bold fs-4 text-theme">Rp {{ number_format($payment_data['total'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- PILIHAN METODE PEMBAYARAN -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Pilih Metode Pembayaran</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="w-100" style="cursor: pointer;">
                                    <input type="radio" name="payment_method" value="cash" class="d-none payment-method-selector" id="method-cash" required>
                                    <div class="card h-100 border payment-method-card" style="border-radius: 12px; background-color: #fdfbf7;">
                                        <div class="card-body position-relative text-center">
                                            <div class="position-absolute top-0 end-0 p-3">
                                                <div class="check-circle" style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid #ccc; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-check text-white" style="font-size: 12px; display: none;"></i>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <i class="fas fa-wallet" style="font-size: 2rem; color: var(--brown);"></i>
                                            </div>
                                            <div class="fw-bold fs-6 mb-1">Tunai</div>
                                            <small class="text-muted">Bayar dengan uang tunai</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="w-100" style="cursor: pointer;">
                                    <input type="radio" name="payment_method" value="transfer" class="d-none payment-method-selector" id="method-transfer" required>
                                    <div class="card h-100 border payment-method-card" style="border-radius: 12px; background-color: #fdfbf7;">
                                        <div class="card-body position-relative text-center">
                                            <div class="position-absolute top-0 end-0 p-3">
                                                <div class="check-circle" style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid #ccc; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-check text-white" style="font-size: 12px; display: none;"></i>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <i class="fas fa-university" style="font-size: 2rem; color: var(--brown);"></i>
                                            </div>
                                            <div class="fw-bold fs-6 mb-1">Transfer Bank</div>
                                            <small class="text-muted">Bayar via transfer ke rekening</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Payment Method Content Container -->
                    <div id="payment-method-content">
                        <!-- Will be populated by JavaScript -->
                    </div>

                    <!-- Hidden Template untuk Cash Payment -->
                    <template id="cash-payment-template">
                        <div class="card card-modern border mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-theme fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Detail Pembayaran Tunai</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert" style="background-color: rgba(138, 107, 72, 0.1); border-left: 4px solid var(--brown); color: var(--text-primary);">
                                    <i class="fas fa-info-circle me-2 text-theme"></i>
                                    Pembayaran akan diterima sebagai: <strong>Kas / Tunai</strong>
                                </div>
                                <form id="form-cash-payment" method="POST" action="{{ route('transaksi.penjualan.confirm-payment') }}">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="cash">
                                    <input type="hidden" name="payment_data" value="{{ json_encode($payment_data) }}">
                                    <input type="hidden" name="sumber_dana" value="">
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Jumlah Uang Diterima</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-money-bill-alt text-muted"></i></span>
                                            <input type="text" id="jumlah_diterima" class="form-control border-start-0 ps-0" 
                                                placeholder="Rp 0" required style="font-size: 1.25rem; font-weight: 500;">
                                        </div>
                                        <input type="hidden" name="jumlah_diterima" id="jumlah_diterima_hidden">
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Kembalian</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-coins text-muted"></i></span>
                                            <input type="text" id="kembalian" class="form-control border-start-0 ps-0 text-success" 
                                                value="Rp 0" readonly style="font-size: 1.25rem; font-weight: 600; background-color: #f8f9fa;">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Catatan (Opsional)</label>
                                        <textarea name="catatan" class="form-control" rows="2" placeholder="Tambahkan catatan jika ada..."></textarea>
                                    </div>

                                    <div class="alert alert-danger" id="warning-kembalian" style="display: none; border-left: 4px solid #dc3545;">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Jumlah uang yang diterima kurang dari total pembayaran!
                                    </div>

                                    <div class="d-flex gap-3 mt-4 pt-3 border-top">
                                        <a href="{{ route('transaksi.penjualan.create') }}" class="btn btn-outline-theme px-4">
                                            <i class="fas fa-arrow-left me-2"></i>Kembali
                                        </a>
                                        <button type="submit" class="btn btn-theme flex-grow-1" id="btn-confirm-cash">
                                            <i class="fas fa-check-circle me-2"></i>Konfirmasi Pembayaran
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </template>

                    <!-- Hidden Template untuk Transfer Payment -->
                    <template id="transfer-payment-template">
                        <div class="card card-modern border mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-theme fw-bold"><i class="fas fa-university me-2"></i>Detail Pembayaran Transfer</h6>
                            </div>
                            <div class="card-body p-4">
                                <form id="form-transfer-payment" method="POST" action="{{ route('transaksi.penjualan.confirm-payment') }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="transfer">
                                    <input type="hidden" name="payment_data" value="{{ json_encode($payment_data) }}">

                                    <!-- Bank Information -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">Pilih Rekening Tujuan Transfer:</h6>
                                        <div class="row">
                                            @forelse($bank_accounts as $index => $bank)
                                            <div class="col-md-6 mb-3">
                                                <label class="w-100" style="cursor: pointer;">
                                                    <input type="radio" name="sumber_dana" value="{{ $bank->kode_akun }}" class="d-none bank-selector" required {{ $index === 0 ? 'checked' : '' }}>
                                                    <div class="card h-100 border bank-card">
                                                        <div class="card-body position-relative">
                                                            <div class="position-absolute top-0 end-0 p-3">
                                                                <div class="check-circle" style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid #ccc; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="fas fa-check text-white" style="font-size: 12px; display: none;"></i>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2">
                                                                <small class="text-muted">Bank</small>
                                                                <div class="fw-bold">{{ $bank->nama_akun }}</div>
                                                            </div>
                                                            <div class="mb-2">
                                                                <small class="text-muted">Nomor Rekening</small>
                                                                <div class="fw-bold font-monospace">{{ $bank->nomor_rekening ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="mb-2">
                                                                <small class="text-muted">Atas Nama</small>
                                                                <div class="fw-bold">{{ $bank->atas_nama ?? 'N/A' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                            @empty
                                            <div class="col-12">
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    Tidak ada data bank yang tersedia. Silakan lengkapi nomor rekening di menu Tentang Perusahaan.
                                                </div>
                                            </div>
                                            @endforelse
                                        </div>
                                    </div>

                                    <hr>

                                    <!-- Upload Bukti Pembayaran -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">Bukti Pembayaran</h6>
                                        <div class="mb-3">
                                            <label class="form-label">Upload Bukti Transfer</label>
                                            <input type="file" name="bukti_pembayaran" class="form-control" 
                                                   accept="image/*,.pdf" required>
                                            <small class="text-muted">Format: JPG, PNG, PDF (Max 5MB)</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Catatan (Opsional)</label>
                                            <textarea name="catatan" class="form-control" rows="3" 
                                                      placeholder="Contoh: Transfer dari rekening pribadi, referensi: ..."></textarea>
                                        </div>

                                        <div id="preview-bukti" class="mb-3" style="display: none;">
                                            <label class="form-label">Preview Bukti</label>
                                            <div class="border rounded p-2">
                                                <img id="preview-image" src="" alt="Preview" style="max-width: 100%; max-height: 300px;">
                                            </div>
                                        </div>

                                        <div class="d-flex gap-3 mt-4 pt-3 border-top">
                                            <a href="{{ route('transaksi.penjualan.create') }}" class="btn btn-outline-theme px-4">
                                                <i class="fas fa-arrow-left me-2"></i>Kembali
                                            </a>
                                            <button type="submit" class="btn btn-theme flex-grow-1" id="btn-confirm-transfer">
                                                <i class="fas fa-check-circle me-2"></i>Konfirmasi Pembayaran
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </template>

                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-md-4">
            <div class="card card-modern sticky-top" style="top: 90px;">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-muted"></i>Informasi Transaksi</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex mb-3 align-items-center">
                        <div class="payment-icon-wrapper" style="width: 40px; height: 40px; font-size: 1.2rem; margin-right: 12px;">
                            <i class="far fa-calendar-alt"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Tanggal</small>
                            <div class="fw-bold">{{ \Carbon\Carbon::parse($payment_data['tanggal'])->isoFormat('dddd, D MMMM YYYY') }}</div>
                        </div>
                    </div>
                    <div class="d-flex mb-3 align-items-center">
                        <div class="payment-icon-wrapper" style="width: 40px; height: 40px; font-size: 1.2rem; margin-right: 12px;">
                            <i class="far fa-clock"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Waktu</small>
                            <div class="fw-bold">{{ $payment_data['waktu'] }}</div>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="text-center p-3 rounded" style="background-color: rgba(138, 107, 72, 0.05); border: 1px dashed var(--brown-light);">
                        <small class="text-muted d-block mb-1">Total Pembayaran</small>
                        <div class="fw-bold fs-4 text-theme">Rp {{ number_format($payment_data['total'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle payment method selection
document.addEventListener('DOMContentLoaded', function() {
    const methodSelectors = document.querySelectorAll('.payment-method-selector');
    const contentContainer = document.getElementById('payment-method-content');
    const cashTemplate = document.getElementById('cash-payment-template');
    const transferTemplate = document.getElementById('transfer-payment-template');
    
    methodSelectors.forEach(selector => {
        selector.addEventListener('change', function() {
            contentContainer.innerHTML = '';
            
            if (this.value === 'cash') {
                const clone = cashTemplate.content.cloneNode(true);
                contentContainer.appendChild(clone);
                
                // Setup cash payment handlers
                setTimeout(() => {
                    setupCashPaymentHandlers();
                }, 0);
            } else if (this.value === 'transfer') {
                const clone = transferTemplate.content.cloneNode(true);
                contentContainer.appendChild(clone);
                
                // Setup file preview handlers
                setTimeout(() => {
                    setupTransferPaymentHandlers();
                }, 0);
            }
        });
    });
});

function setupCashPaymentHandlers() {
    const jumlahInput = document.getElementById('jumlah_diterima');
    const total = {{ $payment_data['total'] }};
    
    if (jumlahInput) {
        jumlahInput.addEventListener('input', function() {
            const value = this.value.replace(/[^\d]/g, '');
            const numValue = parseInt(value) || 0;
            
            this.value = numValue > 0 ? 'Rp ' + numValue.toLocaleString('id-ID') : 'Rp 0';
            document.getElementById('jumlah_diterima_hidden').value = numValue;
            
            const kembalian = numValue - total;
            const kembalianInput = document.getElementById('kembalian');
            const warningKembalian = document.getElementById('warning-kembalian');
            const btnConfirm = document.getElementById('btn-confirm-cash');
            
            if (kembalian < 0) {
                kembalianInput.value = 'Rp ' + Math.abs(kembalian).toLocaleString('id-ID') + ' (kurang)';
                kembalianInput.classList.add('border-danger');
                warningKembalian.style.display = 'block';
                btnConfirm.disabled = true;
            } else {
                kembalianInput.value = 'Rp ' + kembalian.toLocaleString('id-ID');
                kembalianInput.classList.remove('border-danger');
                warningKembalian.style.display = 'none';
                btnConfirm.disabled = false;
            }
        });
    }
}

function setupTransferPaymentHandlers() {
    const fileInput = document.querySelector('input[name="bukti_pembayaran"]');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('preview-bukti');
                    const previewImage = document.getElementById('preview-image');
                    
                    if (file.type.startsWith('image/')) {
                        previewImage.src = event.target.result;
                        preview.style.display = 'block';
                    } else {
                        preview.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

// Set sumber_dana to Kas for cash payment if needed
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('payment-method-selector')) {
        if (e.target.value === 'cash') {
            // Sumber_dana for cash is already set in the change event handler above
            // This is just a fallback handler
        }
    }
});
</script>

@endsection
