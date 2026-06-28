@extends('layouts.app')

@section('title', 'Tambah Pelunasan Utang')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-credit-card"></i> Tambah Pelunasan Utang</h1>
        <a href="{{ route('transaksi.pelunasan-utang.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle"></i> Tentang Pelunasan Utang</h5>
                <p class="mb-0">
                    Halaman ini digunakan untuk melakukan pembayaran utang dari pembelian <strong>{{ $pembelian->nomor_pembelian }}</strong> yang dilakukan secara kredit atau yang belum dibayar penuh. 
                    COA Pelunasan akan otomatis menggunakan akun <strong>Hutang Usaha (211)</strong>.
                </p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-credit-card"></i> Form Pelunasan Utang</h4>
        </div>
        <form action="{{ route('transaksi.pelunasan-utang.store') }}" method="POST">
            @csrf
            <!-- Hidden field for pembelian_id from URL parameter -->
            <input type="hidden" name="pembelian_id" value="{{ request('pembelian_id') ?? old('pembelian_id') }}">
            
            <div class="card-body">
                <div class="form-group">
                    <label>Tanggal <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('tanggal') is-invalid @enderror" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                    @error('tanggal')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Detail Pembelian -->
                <div id="detail-pembelian">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle"></i> Detail Pembelian</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Vendor:</strong>
                                    <p id="vendor-name">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Nomor Pembelian:</strong>
                                    <p id="nomor-pembelian">-</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Total Pembelian:</strong>
                                    <p id="total-pembelian">-</p>
                                </div>
                                <div class="col-md-3" id="dp-section" style="display: none;">
                                    <strong>DP (Down Payment):</strong>
                                    <p id="dp-amount" class="text-info">-</p>
                                </div>
                                <div class="col-md-3" id="refund-section" style="display: none;">
                                    <strong>Total Refund:</strong>
                                    <p id="total-refund" class="text-success">-</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Sisa Utang:</strong>
                                    <p id="sisa-utang-detail" class="text-danger font-weight-bold">-</p>
                                </div>
                            </div>
                            <div class="row" id="due-date-section" style="display: none;">
                                <div class="col-md-12">
                                    <strong>Tanggal Jatuh Tempo:</strong>
                                    <p id="due-date" class="text-warning font-weight-bold">-</p>
                                </div>
                            </div>
                            <div class="alert alert-info mt-2" id="refund-info" style="display: none;">
                                <i class="fas fa-info-circle"></i>
                                <small>Sisa utang sudah dikurangi dengan total refund dari retur yang disetujui.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Akun Pembayaran <span class="text-danger">*</span></label>
                            <select class="form-control @error('akun_kas_id') is-invalid @enderror" name="akun_kas_id" required>
                                <option value="">Pilih Akun Pembayaran</option>
                                @foreach($akunKas as $akun)
                                    <option value="{{ $akun->id }}" {{ old('akun_kas_id') == $akun->id ? 'selected' : '' }}>
                                        [{{ $akun->kode_akun }}] {{ $akun->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                            @error('akun_kas_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">
                                Pilih akun kas untuk pembayaran tunai atau akun bank untuk pembayaran transfer
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Jumlah Pembayaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        Rp
                                    </div>
                                </div>
                                <input type="text" class="form-control price-input @error('jumlah') is-invalid @enderror" name="jumlah" id="jumlah" value="{{ old('jumlah') }}" placeholder="0" required>
                                <input type="hidden" name="jumlah_raw" id="jumlah_raw" value="{{ old('jumlah') }}">
                                @error('jumlah')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <small class="text-muted">Sisa utang setelah pembayaran: <span id="sisa-utang" class="text-danger">Rp 0</span></small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3" placeholder="Keterangan pembayaran (opsional)">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="{{ route('transaksi.pelunasan-utang.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        // Store the original debt amount
        let originalSisaUtang = 0;
        
        // Format price input with thousand separator
        function setupPriceFormatting() {
            const jumlahInput = document.getElementById('jumlah');
            const jumlahRawInput = document.getElementById('jumlah_raw');
            const sisaUtangSpan = document.getElementById('sisa-utang');
            
            if (jumlahInput) {
                // Format on input and update remaining debt
                jumlahInput.addEventListener('input', function(e) {
                    let value = e.target.value;
                    value = value.replace(/[^0-9]/g, '');
                    const numValue = parseInt(value) || 0;
                    e.target.value = numValue.toLocaleString('id-ID');
                    if (jumlahRawInput) {
                        jumlahRawInput.value = numValue;
                    }
                    
                    // Update remaining debt display
                    updateRemainingDebt(numValue);
                });
                
                // Initial format if there's a value
                if (jumlahInput.value) {
                    const initialValue = Math.floor(parseFloat(jumlahInput.value) || 0);
                    jumlahInput.value = initialValue.toLocaleString('id-ID');
                    if (jumlahRawInput) {
                        jumlahRawInput.value = initialValue;
                    }
                    updateRemainingDebt(initialValue);
                }
            }
            
            // Before form submission, use raw values
            const form = jumlahInput ? jumlahInput.closest('form') : null;
            if (form) {
                form.addEventListener('submit', function() {
                    if (jumlahInput && jumlahRawInput && jumlahRawInput.value) {
                        jumlahInput.value = jumlahRawInput.value;
                    } else if (jumlahInput) {
                        jumlahInput.value = jumlahInput.value.replace(/\./g, '');
                    }
                });
            }
        }
        
        // Update remaining debt display based on payment amount
        function updateRemainingDebt(paymentAmount) {
            const sisaUtangSpan = document.getElementById('sisa-utang');
            const remainingDebt = Math.max(0, originalSisaUtang - paymentAmount);
            
            const formatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            });
            
            sisaUtangSpan.textContent = formatter.format(remainingDebt);
            
            // Change color based on status
            if (remainingDebt === 0) {
                sisaUtangSpan.classList.remove('text-danger', 'text-warning');
                sisaUtangSpan.classList.add('text-success');
            } else if (remainingDebt < originalSisaUtang) {
                sisaUtangSpan.classList.remove('text-danger', 'text-success');
                sisaUtangSpan.classList.add('text-warning');
            } else {
                sisaUtangSpan.classList.remove('text-success', 'text-warning');
                sisaUtangSpan.classList.add('text-danger');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Setup price formatting
            setupPriceFormatting();
            
            // Get pembelian_id from hidden input
            const pembelianIdInput = document.querySelector('input[name="pembelian_id"]');
            const pembelianId = pembelianIdInput ? pembelianIdInput.value : null;
            
            if (pembelianId) {
                // Load pembelian details
                loadPembelianDetails(pembelianId);
            }
        });
        
        function loadPembelianDetails(pembelianId) {
            const detailSection = document.getElementById('detail-pembelian');
            const vendorName = document.getElementById('vendor-name');
            const totalPembelian = document.getElementById('total-pembelian');
            const sisaUtangDetail = document.getElementById('sisa-utang-detail');
            const jumlahInput = document.getElementById('jumlah');
            const jumlahRawInput = document.getElementById('jumlah_raw');
            const sisaUtangSpan = document.getElementById('sisa-utang');
            
            // Make AJAX call to get purchase details
            fetch(`/transaksi/pelunasan-utang/get-pembelian/${pembelianId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show detail section
                        detailSection.style.display = 'block';
                        
                        // Store original sisa utang for calculation
                        originalSisaUtang = Math.floor(data.data.sisa_utang);
                        
                        // Fill in the details
                        vendorName.textContent = data.data.vendor;
                        document.getElementById('nomor-pembelian').textContent = data.data.nomor_pembelian;
                        totalPembelian.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.data.total_pembelian);
                        sisaUtangDetail.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.data.sisa_utang);
                        
                        // Show DP section if there's DP
                        const dpAmount = data.data.dp_amount || 0;
                        const dpSection = document.getElementById('dp-section');
                        
                        if (dpAmount > 0) {
                            dpSection.style.display = 'block';
                            document.getElementById('dp-amount').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(dpAmount);
                        } else {
                            dpSection.style.display = 'none';
                        }
                        
                        // Show due date if exists
                        const dueDate = data.data.tanggal_jatuh_tempo;
                        const dueDateSection = document.getElementById('due-date-section');
                        
                        if (dueDate) {
                            dueDateSection.style.display = 'block';
                            // Format date to Indonesian format
                            const dateObj = new Date(dueDate);
                            const formattedDate = dateObj.toLocaleDateString('id-ID', { 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric' 
                            });
                            document.getElementById('due-date').textContent = formattedDate;
                        } else {
                            dueDateSection.style.display = 'none';
                        }
                        
                        // Show refund section if there's refund
                        const totalRefund = data.data.total_refund || 0;
                        const refundSection = document.getElementById('refund-section');
                        const refundInfo = document.getElementById('refund-info');
                        
                        if (totalRefund > 0) {
                            refundSection.style.display = 'block';
                            refundInfo.style.display = 'block';
                            document.getElementById('total-refund').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalRefund);
                        } else {
                            refundSection.style.display = 'none';
                            refundInfo.style.display = 'none';
                        }
                        
                        // Auto-fill jumlah with sisa utang (formatted)
                        const sisaUtangValue = Math.floor(data.data.sisa_utang);
                        jumlahInput.value = sisaUtangValue.toLocaleString('id-ID');
                        if (jumlahRawInput) {
                            jumlahRawInput.value = sisaUtangValue;
                        }
                        
                        // Update remaining debt display (initially will be 0 since payment = debt)
                        updateRemainingDebt(sisaUtangValue);
                    } else {
                        alert(data.message);
                        window.location.href = '{{ route('transaksi.pelunasan-utang.index') }}';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data pembelian');
                    window.location.href = '{{ route('transaksi.pelunasan-utang.index') }}';
                });
        }
    </script>
@endpush