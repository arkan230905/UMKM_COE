@extends('layouts.app')

@section('title', 'Tambah Retur Penjualan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-plus me-2"></i>Tambah Retur Penjualan
        </h2>
        <div>
            <a href="{{ route('transaksi.retur-penjualan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('transaksi.retur-penjualan.store') }}" method="POST" id="returForm">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="penjualan_id" class="form-label">Nomor Penjualan <span class="text-danger">*</span></label>
                            <select name="penjualan_id" id="penjualan_id" class="form-select" required>
                                <option value="">-- Pilih Penjualan --</option>
                                @foreach($penjualans as $penjualan)
                                    <option value="{{ $penjualan->id }}" {{ old('penjualan_id') == $penjualan->id ? 'selected' : '' }}>
                                        {{ $penjualan->nomor_penjualan ?? 'PJ-' . $penjualan->id }} - {{ $penjualan->tanggal->format('d/m/Y') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('penjualan_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tanggal" class="form-label">Tanggal Retur <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                            @error('tanggal')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="jenis_retur" class="form-label">Jenis Retur <span class="text-danger">*</span></label>
                            <select name="jenis_retur" id="jenis_retur" class="form-select" required>
                                <option value="">-- Pilih Jenis Retur --</option>
                                @foreach($jenisReturOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('jenis_retur') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_retur')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="pelanggan_id" class="form-label">Pelanggan <span class="text-danger" id="pelangganRequired" style="display:none;">*</span></label>
                            <select name="pelanggan_id" id="pelanggan_id" class="form-select">
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach($pelanggans as $pelanggan)
                                    <option value="{{ $pelanggan->id }}" {{ old('pelanggan_id') == $pelanggan->id ? 'selected' : '' }}>
                                        {{ $pelanggan->kode_pelanggan }} - {{ $pelanggan->nama_pelanggan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pelanggan_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" id="pelangganInfo">Wajib diisi untuk jenis retur Kredit</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" class="form-control" rows="3">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <hr>

                <h5 class="mb-3">Detail Retur</h5>
                <div id="detailContainer">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="detailTable">
                            <thead>
                                <tr>
                                    <th style="width: 40px">#</th>
                                    <th>Produk</th>
                                    <th style="width: 120px">Qty Penjualan</th>
                                    <th style="width: 120px">Qty Retur</th>
                                    <th style="width: 150px">Harga Barang</th>
                                    <th style="width: 150px">Subtotal</th>
                                    <th style="width: 40px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Detail rows will be added here dynamically -->
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" id="addDetail">
                        <i class="fas fa-plus"></i> Tambah Detail
                    </button>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Total Harga:</label>
                            <h5 class="text-primary">Rp <span id="totalHarga">0.00</span></h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">PPN (11%):</label>
                            <h5 class="text-info">Rp <span id="totalPPN">0.00</span></h5>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Total Retur:</label>
                            <h4 class="text-success">Rp <span id="totalRetur">0.00</span></h4>
                            <small class="text-muted" id="totalReturInfo">Untuk Tukar Barang, total retur akan menjadi Rp 0</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('transaksi.retur-penjualan.index') }}" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Retur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let detailIndex = 0;
    let penjualanDetails = [];

    // Load penjualan details when penjualan is selected
    $('#penjualan_id').change(function() {
        const penjualanId = $(this).val();
        if (penjualanId) {
            $.get(`/retur-penjualan/get-penjualan-details/${penjualanId}`, function(data) {
                penjualanDetails = data;
                // Clear existing details
                $('#detailTable tbody').empty();
                detailIndex = 0;
                updateTotals();
            });
        } else {
            penjualanDetails = [];
            $('#detailTable tbody').empty();
            detailIndex = 0;
            updateTotals();
        }
    });

    // Show/hide pelanggan field based on jenis_retur
    $('#jenis_retur').change(function() {
        const jenisRetur = $(this).val();
        if (jenisRetur === 'kredit') {
            $('#pelanggan_id').prop('required', true);
            $('#pelangganRequired').show();
            $('#pelangganInfo').show();
        } else {
            $('#pelanggan_id').prop('required', false);
            $('#pelangganRequired').hide();
            $('#pelangganInfo').hide();
        }
        updateTotals();
    });

    // Add detail row
    $('#addDetail').click(function() {
        if (!$('#penjualan_id').val()) {
            alert('Pilih nomor penjualan terlebih dahulu');
            return;
        }

        addDetailRow();
    });

    function addDetailRow() {
        const row = `
            <tr data-index="${detailIndex}">
                <td class="text-center">${detailIndex + 1}</td>
                <td>
                    <select name="details[${detailIndex}][penjualan_detail_id]" class="form-select penjualan-detail-select" required>
                        <option value="">-- Pilih Produk --</option>
                        ${penjualanDetails.map(detail => 
                            `<option value="${detail.id}" data-qty="${detail.jumlah}" data-harga="${detail.harga_satuan}">
                                ${detail.produk_nama} (Stok: ${detail.jumlah})
                            </option>`
                        ).join('')}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control qty-penjualan" readonly>
                </td>
                <td>
                    <input type="number" name="details[${detailIndex}][qty_retur]" class="form-control qty-retur" step="0.0001" min="0.0001" required>
                </td>
                <td>
                    <input type="number" name="details[${detailIndex}][harga_barang]" class="form-control harga-barang" step="0.01" min="0" required>
                </td>
                <td>
                    <input type="text" class="form-control subtotal" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-detail">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#detailTable tbody').append(row);
        detailIndex++;
    }

    // Handle events for dynamically added rows
    $(document).on('change', '.penjualan-detail-select', function() {
        const row = $(this).closest('tr');
        const selectedOption = $(this).find('option:selected');
        const qtyPenjualan = selectedOption.data('qty');
        const harga = selectedOption.data('harga');
        
        row.find('.qty-penjualan').val(qtyPenjualan);
        row.find('.harga-barang').val(harga);
        row.find('.qty-retur').attr('max', qtyPenjualan);
        
        calculateRowSubtotal(row);
        updateTotals();
    });

    $(document).on('input', '.qty-retur, .harga-barang', function() {
        const row = $(this).closest('tr');
        calculateRowSubtotal(row);
        updateTotals();
    });

    $(document).on('click', '.remove-detail', function() {
        $(this).closest('tr').remove();
        updateTotals();
    });

    function calculateRowSubtotal(row) {
        const qty = parseFloat(row.find('.qty-retur').val()) || 0;
        const harga = parseFloat(row.find('.harga-barang').val()) || 0;
        const subtotal = qty * harga;
        
        row.find('.subtotal').val(subtotal.toFixed(2));
    }

    function updateTotals() {
        let totalHarga = 0;
        let jenisRetur = $('#jenis_retur').val();
        
        $('.subtotal').each(function() {
            totalHarga += parseFloat($(this).val()) || 0;
        });

        let ppn = 0;
        let totalRetur = 0;

        if (jenisRetur === 'tukar_barang') {
            totalRetur = 0;
            $('#totalReturInfo').show();
        } else {
            ppn = totalHarga * 0.11;
            totalRetur = totalHarga + ppn;
            $('#totalReturInfo').hide();
        }

        $('#totalHarga').text(totalHarga.toFixed(2));
        $('#totalPPN').text(ppn.toFixed(2));
        $('#totalRetur').text(totalRetur.toFixed(2));
    }

    // Form validation
    $('#returForm').submit(function(e) {
        const detailRows = $('#detailTable tbody tr').length;
        if (detailRows === 0) {
            e.preventDefault();
            alert('Tambahkan minimal satu detail retur');
            return false;
        }

        let valid = true;
        $('.qty-retur').each(function() {
            const qty = parseFloat($(this).val());
            const maxQty = parseFloat($(this).attr('max'));
            
            if (qty > maxQty) {
                valid = false;
                alert('Qty retur tidak boleh melebihi qty penjualan');
                return false;
            }
        });

        if (!valid) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
