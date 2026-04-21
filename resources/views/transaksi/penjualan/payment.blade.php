@extends('layouts.app')

@section('title', 'Konfirmasi Pembayaran')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Konfirmasi Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Ringkasan Pesanan -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Ringkasan Pesanan</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payment_data['items'] as $item)
                                    <tr>
                                        <td>
                                            @php
                                                $produk = \App\Models\Produk::find($item['produk_id']);
                                            @endphp
                                            {{ $produk->nama_produk ?? 'Produk Tidak Ditemukan' }}
                                        </td>
                                        <td class="text-end">{{ $item['jumlah'] }}</td>
                                        <td class="text-end">Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
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
                                    <small class="text-muted">Subtotal Produk</small>
                                    <div class="fw-bold">Rp {{ number_format($payment_data['subtotal_produk'], 0, ',', '.') }}</div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Biaya Ongkir</small>
                                    <div class="fw-bold">Rp {{ number_format($payment_data['biaya_ongkir'], 0, ',', '.') }}</div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Biaya Service</small>
                                    <div class="fw-bold">Rp {{ number_format($payment_data['biaya_service'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <small class="text-muted">PPN ({{ $payment_data['ppn_persen'] }}%)</small>
                                    <div class="fw-bold">Rp {{ number_format($payment_data['total_ppn'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Total -->
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-6 ms-auto">
                                <div class="p-3 bg-light rounded">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold">Total Pembayaran:</span>
                                        <span class="fw-bold fs-5 text-primary">Rp {{ number_format($payment_data['total'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="alert alert-info">
                        <strong>Metode Pembayaran:</strong> 
                        @if($payment_data['payment_method'] === 'cash')
                            <span class="badge bg-success">Tunai</span>
                        @elseif($payment_data['payment_method'] === 'transfer')
                            <span class="badge bg-primary">Transfer Bank</span>
                        @else
                            <span class="badge bg-warning">{{ ucfirst($payment_data['payment_method']) }}</span>
                        @endif
                    </div>

                    <!-- Payment Method Specific Content -->
                    @if($payment_data['payment_method'] === 'cash')
                        <!-- CASH PAYMENT -->
                        <div class="card border-success mb-4">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-money-bill me-2"></i>Pembayaran Tunai</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Pembayaran akan diterima di: <strong>{{ $payment_data['sumber_dana_label'] }}</strong>
                                </div>
                                <form id="form-cash-payment" method="POST" action="{{ route('transaksi.penjualan.confirm-payment') }}">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="cash">
                                    <input type="hidden" name="payment_data" value="{{ json_encode($payment_data) }}">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Jumlah Uang Diterima</label>
                                        <input type="text" id="jumlah_diterima" class="form-control form-control-lg" 
                                               placeholder="Rp 0" required>
                                        <input type="hidden" name="jumlah_diterima" id="jumlah_diterima_hidden">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Kembalian</label>
                                        <input type="text" id="kembalian" class="form-control form-control-lg" 
                                               value="Rp 0" readonly>
                                    </div>

                                    <div class="alert alert-warning" id="warning-kembalian" style="display: none;">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Jumlah uang yang diterima kurang dari total pembayaran!
                                    </div>

                                    <div class="d-flex gap-2">
                                        <a href="{{ route('transaksi.penjualan.create') }}" class="btn btn-secondary">Kembali</a>
                                        <button type="submit" class="btn btn-success" id="btn-confirm-cash">
                                            <i class="fas fa-check me-2"></i>Konfirmasi Pembayaran
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    @elseif($payment_data['payment_method'] === 'transfer')
                        <!-- TRANSFER PAYMENT -->
                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-university me-2"></i>Pembayaran Transfer Bank</h6>
                            </div>
                            <div class="card-body">
                                <!-- Bank Information -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Data Bank Perusahaan</h6>
                                    <div class="row">
                                        @forelse($bank_accounts as $bank)
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-info">
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
                                                    <div class="mb-2">
                                                        <small class="text-muted">Saldo</small>
                                                        <div class="fw-bold text-success">Rp {{ number_format($bank->saldo_awal ?? 0, 0, ',', '.') }}</div>
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

                                        <div class="d-flex gap-2">
                                            <a href="{{ route('transaksi.penjualan.create') }}" class="btn btn-secondary">Kembali</a>
                                            <button type="submit" class="btn btn-primary" id="btn-confirm-transfer">
                                                <i class="fas fa-check me-2"></i>Konfirmasi Pembayaran
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
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Informasi Transaksi</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Tanggal</small>
                        <div class="fw-bold">{{ \Carbon\Carbon::parse($payment_data['tanggal'])->isoFormat('dddd, D MMMM YYYY') }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Waktu</small>
                        <div class="fw-bold">{{ $payment_data['waktu'] }}</div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted">Total Pembayaran</small>
                        <div class="fw-bold fs-5 text-primary">Rp {{ number_format($payment_data['total'], 0, ',', '.') }}</div>
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
