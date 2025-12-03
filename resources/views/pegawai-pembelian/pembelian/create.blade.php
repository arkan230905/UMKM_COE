@extends('layouts.pegawai-pembelian')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">
            <i class="bi bi-cart-plus"></i> Tambah Pembelian
        </h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('pegawai-pembelian.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pegawai-pembelian.pembelian.index') }}">Pembelian</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <strong>Terjadi kesalahan:</strong>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('pegawai-pembelian.pembelian.store') }}" method="POST">
    @csrf
    
    <div class="card mb-3">
        <div class="card-header">
            <i class="bi bi-info-circle"></i> Informasi Pembelian
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="vendor_id" class="form-label">Vendor <span class="text-danger">*</span></label>
                    <select name="vendor_id" id="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Vendor --</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->nama_vendor }}
                            </option>
                        @endforeach
                    </select>
                    @error('vendor_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="tanggal" class="form-label">Tanggal Pembelian <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" 
                           class="form-control @error('tanggal') is-invalid @enderror" 
                           value="{{ old('tanggal', date('Y-m-d')) }}" required>
                    @error('tanggal')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select name="payment_method" id="payment_method" 
                            class="form-select @error('payment_method') is-invalid @enderror" required>
                        <option value="cash" {{ old('payment_method', 'cash') === 'cash' ? 'selected' : '' }}>Tunai</option>
                        <option value="transfer" {{ old('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        <option value="credit" {{ old('payment_method') === 'credit' ? 'selected' : '' }}>Kredit (Hutang)</option>
                    </select>
                    @error('payment_method')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2" 
                              class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul"></i> Detail Pembelian</span>
            <button type="button" class="btn btn-success btn-sm" id="addRow">
                <i class="bi bi-plus-circle"></i> Tambah Baris
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="detailTable">
                    <thead class="table-light">
                        <tr>
                            <th width="35%">Bahan Baku</th>
                            <th width="15%">Jumlah</th>
                            <th width="15%">Satuan</th>
                            <th width="20%">Harga per Satuan</th>
                            <th width="20%">Subtotal</th>
                            <th width="5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="bahan_baku_id[]" class="form-select form-select-sm bahan-baku" required>
                                    <option value="">-- Pilih Bahan Baku --</option>
                                    @foreach ($bahanBakus as $bahan)
                                        <option value="{{ $bahan->id }}" data-satuan="{{ $bahan->satuan->nama_satuan ?? '' }}">
                                            {{ $bahan->nama_bahan }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="jumlah[]" class="form-control form-control-sm jumlah" 
                                       min="0.01" step="0.01" value="1" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm satuan-display" readonly>
                            </td>
                            <td>
                                <input type="number" name="harga_satuan[]" class="form-control form-control-sm harga" 
                                       min="0" step="0.01" value="0" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm subtotal" readonly>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm removeRow" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="4" class="text-end fw-bold">TOTAL:</td>
                            <td colspan="2">
                                <input type="text" id="total" class="form-control form-control-sm fw-bold" readonly>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="alert alert-info py-2 mb-0">
                <i class="bi bi-info-circle"></i>
                <small>
                    <strong>Catatan:</strong> Stok bahan baku akan otomatis bertambah setelah pembelian disimpan. 
                    Pastikan data yang diinput sudah benar.
                </small>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> Simpan Pembelian
        </button>
        <a href="{{ route('pegawai-pembelian.pembelian.index') }}" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> Batal
        </a>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function formatRupiah(angka) {
        return 'Rp ' + angka.toLocaleString('id-ID');
    }

    function updateSatuan(row) {
        const select = row.querySelector('.bahan-baku');
        const satuanDisplay = row.querySelector('.satuan-display');
        const selectedOption = select.options[select.selectedIndex];
        const satuan = selectedOption.getAttribute('data-satuan') || '';
        satuanDisplay.value = satuan;
    }

    function updateSubtotal(row) {
        const jumlah = parseFloat(row.querySelector('.jumlah').value) || 0;
        const harga = parseFloat(row.querySelector('.harga').value) || 0;
        const subtotal = jumlah * harga;
        row.querySelector('.subtotal').value = formatRupiah(subtotal);
        updateTotal();
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(input => {
            const value = input.value.replace(/[^0-9]/g, '');
            total += parseFloat(value) || 0;
        });
        document.getElementById('total').value = formatRupiah(total);
    }

    // Tambah baris baru
    document.getElementById('addRow').addEventListener('click', function() {
        const tbody = document.querySelector('#detailTable tbody');
        const newRow = tbody.rows[0].cloneNode(true);
        
        // Reset values
        newRow.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
        newRow.querySelector('.jumlah').value = 1;
        newRow.querySelector('.harga').value = 0;
        newRow.querySelector('.satuan-display').value = '';
        newRow.querySelector('.subtotal').value = formatRupiah(0);
        
        tbody.appendChild(newRow);
    });

    // Hapus baris
    document.querySelector('#detailTable').addEventListener('click', function(e) {
        if(e.target && (e.target.classList.contains('removeRow') || e.target.closest('.removeRow'))) {
            const rows = document.querySelectorAll('#detailTable tbody tr');
            if(rows.length > 1) {
                e.target.closest('tr').remove();
                updateTotal();
            } else {
                alert('Minimal harus ada 1 baris item!');
            }
        }
    });

    // Update satuan saat bahan baku dipilih
    document.querySelector('#detailTable').addEventListener('change', function(e) {
        if(e.target && e.target.classList.contains('bahan-baku')) {
            const row = e.target.closest('tr');
            updateSatuan(row);
        }
    });

    // Update subtotal saat input berubah
    document.querySelector('#detailTable').addEventListener('input', function(e) {
        if(e.target && (e.target.classList.contains('jumlah') || e.target.classList.contains('harga'))) {
            const row = e.target.closest('tr');
            updateSubtotal(row);
        }
    });

    // Hitung subtotal awal
    document.querySelectorAll('#detailTable tbody tr').forEach(row => {
        updateSatuan(row);
        updateSubtotal(row);
    });
});
</script>
@endpush
