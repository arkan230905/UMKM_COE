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
                                    <option value="{{ $penjualan->id }}" {{ (old('penjualan_id', $selectedPenjualanId ?? '') == $penjualan->id) ? 'selected' : '' }}>
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
                                        {{ $pelanggan->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pelanggan_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Opsional</small>
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
                    <button type="button" class="btn btn-sm btn-primary" id="addDetail" data-bs-toggle="modal" data-bs-target="#detailReturModal">
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

<!-- Modal Tambah Detail Retur -->
<div class="modal fade" id="detailReturModal" tabindex="-1" aria-labelledby="detailReturModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailReturModalLabel">Tambah Detail Retur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="modalProduk" class="form-label">Produk <span class="text-danger">*</span></label>
                    <select id="modalProduk" class="form-select" required>
                        <option value="">-- Pilih Produk --</option>
                    </select>
                    <small class="text-muted d-block mt-2">Qty Tersedia: <span id="modalQtyTersedia">0</span></small>
                </div>
                <div class="mb-3">
                    <label for="modalQtyRetur" class="form-label">Quantity Retur <span class="text-danger">*</span></label>
                    <input type="number" id="modalQtyRetur" class="form-control" step="0.0001" min="0.0001" required>
                </div>
                <div class="mb-3">
                    <label for="modalHarga" class="form-label">Harga Barang <span class="text-danger">*</span></label>
                    <input type="number" id="modalHarga" class="form-control" step="0.01" min="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="modalSimpanDetail">Simpan Detail</button>
            </div>
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
            $.get(`{{ route('transaksi.retur-penjualan.get-penjualan-details', ':id') }}`.replace(':id', penjualanId), function(data) {
                penjualanDetails = data;
                $('#detailTable tbody').empty();
                detailIndex = 0;

                // Otomatis tambahkan semua produk dari penjualan ke tabel detail
                data.forEach(function(detail) {
                    addDetailRow(detail);
                });

                updateTotals();
            });
        } else {
            penjualanDetails = [];
            $('#detailTable tbody').empty();
            detailIndex = 0;
            updateTotals();
        }
    });

    // Auto-trigger jika penjualan_id sudah dipilih dari URL parameter
    if ($('#penjualan_id').val()) {
        $('#penjualan_id').trigger('change');
    }

    // Add detail row (manual, tanpa prefill)
    $('#addDetail').click(function() {
        if (!$('#penjualan_id').val()) {
            alert('Pilih nomor penjualan terlebih dahulu');
            return;
        }
        
        // Populate modal dropdown dengan produk dari penjualan
        $('#modalProduk').empty().append('<option value="">-- Pilih Produk --</option>');
        penjualanDetails.forEach(function(detail) {
            $('#modalProduk').append(
                `<option value="${detail.id}" data-qty="${detail.jumlah}" data-harga="${detail.harga_satuan}">
                    ${detail.produk_nama}
                </option>`
            );
        });
        
        // Reset form
        $('#modalProduk').val('');
        $('#modalQtyRetur').val('');
        $('#modalHarga').val('');
        $('#modalQtyTersedia').text('0');
    });

    // Handle produk selection di modal
    $('#modalProduk').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const qty = selectedOption.data('qty') || 0;
        const harga = selectedOption.data('harga') || 0;
        
        $('#modalQtyTersedia').text(qty);
        $('#modalQtyRetur').attr('max', qty).val(qty);
        $('#modalHarga').val(harga);
    });

    // Simpan detail dari modal
    $('#modalSimpanDetail').click(function() {
        const produkId = $('#modalProduk').val();
        const qtyRetur = parseFloat($('#modalQtyRetur').val());
        const harga = parseFloat($('#modalHarga').val());

        if (!produkId) {
            alert('Pilih produk terlebih dahulu');
            return;
        }
        if (!qtyRetur || qtyRetur <= 0) {
            alert('Quantity retur harus lebih dari 0');
            return;
        }
        if (!harga || harga < 0) {
            alert('Harga barang tidak valid');
            return;
        }

        // Cari detail produk dari penjualanDetails
        const produk = penjualanDetails.find(d => d.id == produkId);
        if (!produk) {
            alert('Produk tidak ditemukan');
            return;
        }

        // Tambahkan baris ke tabel
        const row = `
            <tr data-index="${detailIndex}">
                <td class="text-center">${detailIndex + 1}</td>
                <td>${produk.produk_nama}</td>
                <td>
                    <input type="hidden" name="details[${detailIndex}][penjualan_detail_id]" value="${produkId}">
                    <input type="text" class="form-control qty-penjualan" value="${produk.jumlah}" readonly>
                </td>
                <td>
                    <input type="number" name="details[${detailIndex}][qty_retur]" class="form-control qty-retur"
                        step="0.0001" min="0.0001" max="${produk.jumlah}" value="${qtyRetur}" required>
                </td>
                <td>
                    <input type="number" name="details[${detailIndex}][harga_barang]" class="form-control harga-barang"
                        step="0.01" min="0" value="${harga}" required>
                </td>
                <td>
                    <input type="text" class="form-control subtotal" value="Rp ${(qtyRetur * harga).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}" readonly>
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
        updateTotals();

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('detailReturModal'));
        modal.hide();
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
        
        row.find('.subtotal').val('Rp ' + subtotal.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }));
    }

    function updateTotals() {
        let totalHarga = 0;
        let jenisRetur = $('#jenis_retur').val();
        
        $('.subtotal').each(function() {
            const val = $(this).val();
            // Parse nilai dari format "Rp 150.000" ke number
            const numVal = parseFloat(val.replace(/Rp/g, '').replace(/\./g, '').replace(/,/g, '')) || 0;
            totalHarga += numVal;
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

        $('#totalHarga').text(totalHarga.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }));
        $('#totalPPN').text(ppn.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }));
        $('#totalRetur').text(totalRetur.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }));
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
