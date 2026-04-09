@extends('layouts.app')

@section('title', 'Detail Retur Penjualan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-undo me-2"></i>Detail Retur Penjualan
        </h2>
        <div>
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Informasi Penjualan</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nomor Penjualan:</strong> {{ $penjualan->nomor_penjualan }}</p>
                    <p><strong>Tanggal Penjualan:</strong> {{ $penjualan->tanggal->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Total Penjualan:</strong> Rp {{ number_format($penjualan->total ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('transaksi.retur-penjualan.store') }}" method="POST" id="returForm">
        @csrf
        <input type="hidden" name="penjualan_id" value="{{ $penjualan->id }}">

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Data Retur</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tanggal" class="form-label">Tanggal Retur <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                            @error('tanggal')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
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
                </div>

                <div class="row">
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
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" rows="3">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-box me-2"></i>Detail Produk Retur</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th class="text-end">Qty Penjualan</th>
                                <th class="text-end">Qty Retur</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="detailTable">
                            @forelse($penjualan->penjualanDetails as $index => $detail)
                                <tr data-index="{{ $index }}">
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $detail->produk->nama_produk ?? '-' }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($detail->jumlah, 2, ',', '.'), '0'), ',') }}</td>
                                    <td>
                                        <input type="hidden" name="details[{{ $index }}][penjualan_detail_id]" value="{{ $detail->id }}">
                                        <input type="number" name="details[{{ $index }}][qty_retur]" class="form-control qty-retur"
                                            step="1" min="1" max="{{ (int)$detail->jumlah }}" value="{{ old("details.$index.qty_retur", 1) }}" required>
                                    </td>
                                    <td class="text-end">
                                        <input type="hidden" name="details[{{ $index }}][harga_barang]" class="harga-value" value="{{ $detail->harga_satuan }}">
                                        <input type="text" class="form-control harga-barang text-end" 
                                            value="Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}" readonly>
                                    </td>
                                    <td class="text-end">
                                        <input type="text" class="form-control subtotal text-end" value="Rp {{ number_format(old("details.$index.qty_retur", 1) * $detail->harga_satuan, 0, ',', '.') }}" readonly>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger remove-detail">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Tidak ada detail penjualan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
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
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Simpan Retur
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize totals on page load
    updateTotals();

    // Handle qty changes
    $(document).on('input', '.qty-retur', function() {
        const row = $(this).closest('tr');
        calculateRowSubtotal(row);
        updateTotals();
    });

    // Remove detail row
    $(document).on('click', '.remove-detail', function() {
        $(this).closest('tr').remove();
        updateTotals();
    });

    function calculateRowSubtotal(row) {
        const qty = parseFloat(row.find('.qty-retur').val()) || 0;
        const harga = parseFloat(row.find('.harga-value').val()) || 0;
        const subtotal = qty * harga;
        
        // Format sebagai nominal uang Indonesia
        const formatted = subtotal.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        row.find('.subtotal').val('Rp ' + formatted);
    }

    function updateTotals() {
        let totalHarga = 0;
        let jenisRetur = $('#jenis_retur').val();

        // Hitung total dari semua subtotal
        $('.subtotal').each(function() {
            const val = $(this).val();
            // Parse nilai dari format "Rp 150.000" ke number
            const cleanVal = val.replace(/Rp/g, '').replace(/\./g, '').replace(/,/g, '').trim();
            const numVal = parseFloat(cleanVal) || 0;
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

        // Format dan tampilkan hasil
        const totalHargaFormatted = totalHarga.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        const ppnFormatted = ppn.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        const totalReturFormatted = totalRetur.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

        $('#totalHarga').text(totalHargaFormatted);
        $('#totalPPN').text(ppnFormatted);
        $('#totalRetur').text(totalReturFormatted);
    }

    // Form validation
    $('#returForm').submit(function(e) {
        const detailRows = $('#detailTable tr').length;
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
