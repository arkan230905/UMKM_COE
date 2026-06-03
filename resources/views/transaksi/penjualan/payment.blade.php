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
    .badge-theme {
        background-color: var(--brown-light);
        color: white;
        font-weight: 500;
        padding: 6px 12px;
        border-radius: 6px;
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
</style>
@endpush

@section('content')
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
                    <!-- Ringkasan Pesanan -->
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
                                        // Hitung nominal jika belum ada
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
                        @php
                            // Hitung total diskon dari items (fallback jika total_diskon belum ada di session lama)
                            $totalDiskonItems = collect($payment_data['items'])->sum(function($item) {
                                if (isset($item['diskon_nominal']) && $item['diskon_nominal'] > 0) {
                                    return $item['diskon_nominal'];
                                }
                                // Hitung dari diskon_persen jika diskon_nominal belum ada
                                if (isset($item['diskon_persen']) && $item['diskon_persen'] > 0) {
                                    return round(($item['harga_satuan'] * $item['jumlah']) * $item['diskon_persen'] / 100);
                                }
                                return 0;
                            });
                            $totalDiskonTampil = ($payment_data['total_diskon'] ?? 0) > 0
                                ? $payment_data['total_diskon']
                                : $totalDiskonItems;
                            // Subtotal gross = subtotal_produk + total_diskon (karena subtotal_produk sudah net)
                            $subtotalGross = $payment_data['subtotal_produk'] + $totalDiskonTampil;
                        @endphp
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
                                    <div class="fw-bold text-success">Rp {{ number_format($payment_data['subtotal_produk'], 0, ',', '.') }}</div>
                                </div>
                                @endif
                                @if($payment_data['biaya_ongkir'] > 0)
                                <div class="mb-2">
                                    <small class="text-muted">Biaya Ongkir</small>
                                    <div class="fw-bold">Rp {{ number_format($payment_data['biaya_ongkir'], 0, ',', '.') }}</div>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($payment_data['total_ppn'] > 0)
                                <div class="mb-2">
                                    <small class="text-muted">PPN ({{ $payment_data['ppn_persen'] }}%)</small>
                                    <div class="fw-bold">Rp {{ number_format($payment_data['total_ppn'], 0, ',', '.') }}</div>
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

                    <!-- Metode Pembayaran -->
                    <div class="d-flex align-items-center mb-4 p-3 rounded" style="background-color: rgba(138, 107, 72, 0.05); border: 1px dashed var(--brown-light);">
                        <div class="payment-icon-wrapper">
                            @if($payment_data['payment_method'] === 'cash')
                                <i class="fas fa-wallet"></i>
                            @else
                                <i class="fas fa-university"></i>
                            @endif
                        </div>
                        <div>
                            <span class="text-muted d-block" style="font-size: 0.85rem;">Metode Pembayaran</span>
                            <div class="fw-bold fs-6">
                                @if($payment_data['payment_method'] === 'cash')
                                    Tunai
                                @elseif($payment_data['payment_method'] === 'transfer')
                                    Transfer Bank
                                @else
                                    {{ ucfirst($payment_data['payment_method']) }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Specific Content -->
                    @if($payment_data['payment_method'] === 'cash')
                        <!-- CASH PAYMENT -->
                        <div class="card card-modern border mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-theme fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Detail Pembayaran Tunai</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert" style="background-color: rgba(138, 107, 72, 0.1); border-left: 4px solid var(--brown); color: var(--text-primary);">
                                    <i class="fas fa-info-circle me-2 text-theme"></i>
                                    Pembayaran akan diterima di: <strong>{{ $payment_data['sumber_dana_label'] }}</strong>
                                </div>
                                <form id="form-cash-payment" method="POST" action="{{ route('transaksi.penjualan.confirm-payment') }}">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="cash">
                                    <input type="hidden" name="payment_data" value="{{ json_encode($payment_data) }}">
                                    
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

                    @elseif($payment_data['payment_method'] === 'transfer')
                        <!-- TRANSFER PAYMENT -->
                        <div class="card card-modern border mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-theme fw-bold"><i class="fas fa-university me-2"></i>Detail Pembayaran Transfer</h6>
                            </div>
                            <div class="card-body p-4">
                                <!-- Bank Information -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Transfer Ke Rekening Berikut:</h6>
                                    <div class="row">
                                        @forelse($bank_accounts as $bank)
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100 border" style="border-radius: 12px; background-color: #fdfbf7;">
                                                <div class="card-body">
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
                                        </div>
                                        @empty
                                        <div class="col-12">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                Tidak ada data bank yang tersedia
                                            </div>
                                        </div>
                                        @endforelse
                                    </div>
                                </div>

                                <hr>

                                <!-- Upload Bukti Pembayaran -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Bukti Pembayaran</h6>
                                    <form id="form-transfer-payment" method="POST" action="{{ route('transaksi.penjualan.confirm-payment') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="payment_method" value="transfer">
                                        <input type="hidden" name="payment_data" value="{{ json_encode($payment_data) }}">

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
                                    </form>
                                </div>
                            </div>
                        </div>

                    @endif
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
// Format currency input for cash payment
document.getElementById('jumlah_diterima')?.addEventListener('input', function() {
    const value = this.value.replace(/[^\d]/g, '');
    const numValue = parseInt(value) || 0;
    
    // Format display
    this.value = 'Rp ' + numValue.toLocaleString('id-ID');
    
    // Store raw value
    document.getElementById('jumlah_diterima_hidden').value = numValue;
    
    // Calculate kembalian
    const total = {{ $payment_data['total'] }};
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

// Preview image for transfer payment
document.querySelector('input[name="bukti_pembayaran"]')?.addEventListener('change', function(e) {
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
</script>
@endsection
