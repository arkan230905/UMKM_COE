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
                    Halaman ini digunakan untuk melakukan pembayaran utang dari pembelian yang dilakukan secara kredit atau pembelian yang belum dibayar penuh. 
                    Sistem akan menampilkan daftar pembelian yang masih memiliki sisa utang yang perlu dibayar.
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

                <div class="form-group">
                    <label>Pembelian <span class="text-danger">*</span></label>
                    <select class="form-control @error('pembelian_id') is-invalid @enderror" name="pembelian_id" required>
                        <option value="">Pilih Pembelian</option>
                        @forelse($pembayarans as $pembayaran)
                            @php
                                $sisaUtang = ($pembayaran->total_harga ?? 0) - ($pembayaran->terbayar ?? 0);
                            @endphp
                            <option value="{{ $pembayaran->id }}" data-sisa="{{ $sisaUtang }}" {{ old('pembelian_id') == $pembayaran->id ? 'selected' : '' }}>
                                {{ $pembayaran->nomor_pembelian ?? 'PB-' . $pembayaran->id }} - {{ $pembayaran->vendor->nama_vendor ?? 'Vendor tidak diketahui' }} (Sisa: Rp {{ number_format($sisaUtang, 0, ',', '.') }})
                            </option>
                        @empty
                            <option value="" disabled>Tidak ada pembelian yang belum lunas</option>
                        @endforelse
                    </select>
                    @error('pembelian_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                    
                    @if($pembayarans->isEmpty())
                        <div class="alert alert-info mt-2">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong> Saat ini tidak ada pembelian yang memiliki sisa utang. 
                            Pelunasan utang hanya dapat dilakukan untuk pembelian dengan metode pembayaran kredit atau pembelian yang belum dibayar penuh.
                            <br><br>
                            <a href="{{ route('transaksi.pembelian.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Buat Pembelian Baru
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Detail Pembelian -->
                <div id="detail-pembelian" style="display: none;">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle"></i> Detail Pembelian</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Vendor:</strong>
                                    <p id="vendor-name">-</p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Total Pembelian:</strong>
                                    <p id="total-pembelian">-</p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Sisa Utang:</strong>
                                    <p id="sisa-utang-detail" class="text-danger font-weight-bold">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Akun Pembayaran <span class="text-danger">*</span></label>
                            <select class="form-control @error('akun_kas_id') is-invalid @enderror" name="akun_kas_id" required>
                                <option value="">Pilih Akun Pembayaran</option>
                                @foreach($akunKas as $akun)
                                    <option value="{{ $akun->id }}" {{ old('akun_kas_id') == $akun->id ? 'selected' : '' }}>
                                        {{ $akun->kode_akun }} - {{ $akun->nama_akun }}
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>COA Pelunasan <span class="text-danger">*</span></label>
                            <select class="form-control @error('coa_pelunasan_id') is-invalid @enderror" name="coa_pelunasan_id" required>
                                <option value="">Pilih COA Pelunasan</option>
                                @foreach($coaPelunasan as $coa)
                                    <option value="{{ $coa->id }}" {{ old('coa_pelunasan_id') == $coa->id ? 'selected' : '' }}>
                                        {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                            @error('coa_pelunasan_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">
                                Pilih akun COA untuk pelunasan utang
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Jumlah Pembayaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        Rp
                                    </div>
                                </div>
                                <input type="number" class="form-control @error('jumlah') is-invalid @enderror" name="jumlah" id="jumlah" value="{{ old('jumlah') }}" min="1" step="0.01" required>
                                @error('jumlah')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <small class="text-muted">Sisa utang: <span id="sisa-utang">Rp 0</span></small>
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
                <button type="submit" class="btn btn-primary" {{ $pembayarans->isEmpty() ? 'disabled' : '' }}>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Handle pembelian selection
            const pembelianSelect = document.querySelector('select[name="pembelian_id"]');
            const detailSection = document.getElementById('detail-pembelian');
            const vendorName = document.getElementById('vendor-name');
            const sisaUtangDetail = document.getElementById('sisa-utang-detail');
            const jumlahInput = document.getElementById('jumlah');
            const sisaUtangSpan = document.getElementById('sisa-utang');
            
            if (pembelianSelect) {
                pembelianSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const sisaUtang = selectedOption.getAttribute('data-sisa') || 0;
                    
                    if (sisaUtang > 0) {
                        // Show detail section
                        detailSection.style.display = 'block';
                        
                        // Fill in the details
                        const text = selectedOption.text;
                        const parts = text.split(' - ');
                        const vendorPart = parts[1] ? parts[1].split(' (')[0] : 'Vendor tidak diketahui';
                        
                        vendorName.textContent = vendorPart;
                        sisaUtangDetail.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(sisaUtang);
                        
                        // Auto-fill jumlah with sisa utang
                        jumlahInput.value = sisaUtang;
                    } else {
                        detailSection.style.display = 'none';
                        jumlahInput.value = '';
                    }
                    
                    // Format and display remaining debt
                    const formatter = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0,
                    });
                    
                    sisaUtangSpan.textContent = formatter.format(sisaUtang);
                });

                // Trigger change event on page load if there's a selected pembelian
                @if(old('pembelian_id'))
                    pembelianSelect.dispatchEvent(new Event('change'));
                @endif
            }
        });
    </script>
@endpush