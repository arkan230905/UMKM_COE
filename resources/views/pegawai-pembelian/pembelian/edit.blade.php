@extends('layouts.pegawai-pembelian')

@section('title', 'Edit Pembelian')

@push('styles')
<style>
#vendorSelect {
    position: relative !important;
}

/* Force Bootstrap select dropdown to open downward */
.form-select {
    position: relative !important;
}

.form-select:focus {
    position: relative !important;
    z-index: 1 !important;
}

/* Prevent dropdown from moving up */
select.form-select {
    appearance: none !important;
    position: relative !important;
}

/* Ensure dropdown options stay below */
select.form-select option {
    position: static !important;
}

/* Container to prevent layout shift */
.vendor-select-container {
    position: relative !important;
    min-height: 80px !important;
}

/* Formatting untuk input total */
input[name="total[]"], input[name="total_pendukung[]"] {
    font-weight: bold;
    color: #2c3e50;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}

input[name="total[]"]:focus, input[name="total_pendukung[]"]:focus {
    background-color: #fff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-shopping-cart me-2"></i>Edit Pembelian
        </h2>
        <a href="{{ route('pegawai-pembelian.pembelian.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-edit me-2"></i>Edit Data Pembelian
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('pegawai-pembelian.pembelian.update', $pembelian->id) }}" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="vendor_id" class="form-label">Supplier</label>
                        <select name="vendor_id" id="vendor_id" class="form-select" required>
                            <option value="">Pilih Supplier</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ $pembelian->vendor_id == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ $pembelian->tanggal->format('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="bank_id" class="form-label">Metode Pembayaran</label>
                        <select name="bank_id" id="bank_id" class="form-select" required>
                            <option value="">Pilih Metode Pembayaran</option>
                            @foreach($kasbank as $bank)
                                <option value="{{ $bank->id }}" {{ $pembelian->bank_id == $bank->id ? 'selected' : '' }}>
                                    {{ $bank->nama_akun }} - {{ $bank->kode_akun }} (Saldo Akhir: Rp {{ number_format($currentBalances[$bank->kode_akun] ?? 0, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" class="form-control" rows="3">{{ $pembelian->keterangan }}</textarea>
                    </div>
                </div>

                <!-- Bahan Baku Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-box me-2"></i>Bahan Baku
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="bahanBakuTable">
                                <thead>
                                    <tr>
                                        <th>Bahan Baku</th>
                                        <th>Satuan</th>
                                        <th>Jumlah</th>
                                        <th>Harga Satuan</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pembelian->details->where('bahan_baku_id', '!=', null) as $index => $detail)
                                        <tr>
                                            <td>
                                                <select name="bahan_baku_id[]" class="form-select bahanBakuSelect" required>
                                                    <option value="">Pilih Bahan Baku</option>
                                                    @foreach($bahanBakus as $bahanBaku)
                                                        <option value="{{ $bahanBaku->id }}" {{ $detail->bahan_baku_id == $bahanBaku->id ? 'selected' : '' }} data-harga="{{ $bahanBaku->harga_satuan }}" data-satuan="{{ $bahanBaku->satuan->nama ?? '' }}">
                                                            {{ $bahanBaku->nama_bahan }} (Harga: Rp {{ number_format($bahanBaku->harga_satuan, 0, ',', '.') }}/{{ $bahanBaku->satuan->nama ?? '' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <span class="satuanText">{{ $detail->bahanBaku->satuan->nama ?? '' }}</span>
                                            </td>
                                            <td>
                                                <input type="number" name="jumlah[]" class="form-control jumlahInput" value="{{ $detail->jumlah }}" step="0.01" min="0" required>
                                            </td>
                                            <td>
                                                <input type="number" name="harga_satuan[]" class="form-control hargaInput" value="{{ $detail->harga_satuan }}" step="0.01" min="0" required>
                                            </td>
                                            <td>
                                                <span class="totalText">{{ number_format($detail->jumlah * $detail->harga_satuan, 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Bahan Pendukung Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-tools me-2"></i>Bahan Pendukung
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="bahanPendukungTable">
                                <thead>
                                    <tr>
                                        <th>Bahan Pendukung</th>
                                        <th>Satuan</th>
                                        <th>Jumlah</th>
                                        <th>Harga Satuan</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pembelian->details->where('bahan_pendukung_id', '!=', null) as $index => $detail)
                                        <tr>
                                            <td>
                                                <select name="bahan_pendukung_id[]" class="form-select bahanPendukungSelect" required>
                                                    <option value="">Pilih Bahan Pendukung</option>
                                                    @foreach($bahanPendukungs as $bahanPendukung)
                                                        <option value="{{ $bahanPendukung->id }}" {{ $detail->bahan_pendukung_id == $bahanPendukung->id ? 'selected' : '' }} data-harga="{{ $bahanPendukung->harga_satuan }}" data-satuan="{{ $bahanPendukung->satuanRelation->nama ?? '' }}">
                                                            {{ $bahanPendukung->nama_bahan }} (Harga: Rp {{ number_format($bahanPendukung->harga_satuan, 0, ',', '.') }}/{{ $bahanPendukung->satuanRelation->nama ?? '' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <span class="satuanPendukungText">{{ $detail->bahanPendukung->satuanRelation->nama ?? '' }}</span>
                                            </td>
                                            <td>
                                                <input type="number" name="jumlah_pendukung[]" class="form-control jumlahPendukungInput" value="{{ $detail->jumlah }}" step="0.01" min="0" required>
                                            </td>
                                            <td>
                                                <input type="number" name="harga_satuan_pendukung[]" class="form-control hargaPendukungInput" value="{{ $detail->harga_satuan }}" step="0.01" min="0" required>
                                            </td>
                                            <td>
                                                <span class="totalPendukungText">{{ number_format($detail->jumlah * $detail->harga_satuan, 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Pembelian
                        </button>
                        <a href="{{ route('pegawai-pembelian.pembelian.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update Satuan Text for Bahan Baku
    function updateSatuanText() {
        document.querySelectorAll('.bahanBakuSelect').forEach(select => {
            select.addEventListener('change', function() {
                const selectedOption = select.options[select.selectedIndex];
                const satuanText = select.closest('tr').querySelector('.satuanText');
                const hargaInput = select.closest('tr').querySelector('.hargaInput');
                
                // Update satuan text
                satuanText.textContent = selectedOption.getAttribute('data-satuan') || '';
                
                // Update harga input dengan harga saat ini
                const currentHarga = selectedOption.getAttribute('data-harga');
                if (currentHarga && !hargaInput.value) {
                    hargaInput.value = currentHarga;
                }
                
                // Recalculate total
                calculateTotal();
            });
        });
    }

    // Update Satuan Text for Bahan Pendukung
    function updateSatuanPendukungText() {
        document.querySelectorAll('.bahanPendukungSelect').forEach(select => {
            select.addEventListener('change', function() {
                const selectedOption = select.options[select.selectedIndex];
                const satuanText = select.closest('tr').querySelector('.satuanPendukungText');
                const hargaInput = select.closest('tr').querySelector('.hargaPendukungInput');
                
                // Update satuan text
                satuanText.textContent = selectedOption.getAttribute('data-satuan') || '';
                
                // Update harga input dengan harga saat ini
                const currentHarga = selectedOption.getAttribute('data-harga');
                if (currentHarga && !hargaInput.value) {
                    hargaInput.value = currentHarga;
                }
                
                // Recalculate total
                calculateTotalPendukung();
            });
        });
    }

    // Calculate Total for Bahan Baku
    function calculateTotal() {
        document.querySelectorAll('tr').forEach(row => {
            const jumlahInput = row.querySelector('.jumlahInput');
            const hargaInput = row.querySelector('.hargaInput');
            const totalText = row.querySelector('.totalText');
            
            if (jumlahInput && hargaInput && totalText) {
                const jumlah = parseFloat(jumlahInput.value) || 0;
                const harga = parseFloat(hargaInput.value) || 0;
                const total = jumlah * harga;
                totalText.textContent = total.toLocaleString('id-ID');
            }
        });
    }

    // Calculate Total for Bahan Pendukung
    function calculateTotalPendukung() {
        document.querySelectorAll('tr').forEach(row => {
            const jumlahInput = row.querySelector('.jumlahPendukungInput');
            const hargaInput = row.querySelector('.hargaPendukungInput');
            const totalText = row.querySelector('.totalPendukungText');
            
            if (jumlahInput && hargaInput && totalText) {
                const jumlah = parseFloat(jumlahInput.value) || 0;
                const harga = parseFloat(hargaInput.value) || 0;
                const total = jumlah * harga;
                totalText.textContent = total.toLocaleString('id-ID');
            }
        });
    }

    // Event listeners for input changes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('jumlahInput') || e.target.classList.contains('hargaInput')) {
            calculateTotal();
        }
        if (e.target.classList.contains('jumlahPendukungInput') || e.target.classList.contains('hargaPendukungInput')) {
            calculateTotalPendukung();
        }
    });

    // Initialize
    updateSatuanText();
    updateSatuanPendukungText();
    calculateTotal();
    calculateTotalPendukung();
});
</script>
@endsection
