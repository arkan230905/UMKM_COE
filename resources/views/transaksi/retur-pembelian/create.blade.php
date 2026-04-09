@extends('layouts.app')

@push('styles')
<style>
.summary-section {
    transition: all 0.3s ease-in-out;
}

.summary-table {
    background: #f8f9fa;
    border-radius: 0.375rem;
    padding: 1rem;
}

.summary-table table {
    margin-bottom: 0;
}

.summary-table .table-primary td {
    background-color: #cfe2ff !important;
    border-color: #9ec5fe !important;
}

.summary-table .table-info td {
    background-color: #cff4fc !important;
    border-color: #9eeaf9 !important;
}

#summary_placeholder {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 0.375rem;
    padding: 2rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Buat Retur Pembelian</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('transaksi.retur-pembelian.store') }}" method="POST" id="returForm">
        @csrf
        
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Informasi Retur</h5>
            </div>
            <div class="card-body">
                <input type="hidden" name="pembelian_id" value="{{ $pembelian->id }}">
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">No. Transaksi Pembelian</label>
                            <input type="text" class="form-control" value="{{ $pembelian->nomor_pembelian }}" readonly>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Tanggal Pembelian</label>
                            <input type="text" class="form-control" value="{{ date('d/m/Y', strtotime($pembelian->tanggal_pembelian)) }}" readonly>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Vendor</label>
                            <input type="text" class="form-control" value="{{ $pembelian->vendor->nama_vendor ?? '-' }}" readonly>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Tanggal Retur</label>
                            <input type="text" class="form-control" value="{{ date('d/m/Y') }}" readonly>
                            <input type="hidden" name="tanggal" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="alasan" class="form-label">Alasan Retur <span class="text-danger">*</span></label>
                            <textarea name="alasan" id="alasan" class="form-control" rows="2" required>{{ old('alasan') }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="jenis_retur" class="form-label">Jenis Retur <span class="text-danger">*</span></label>
                            <select name="jenis_retur" id="jenis_retur" class="form-control" required>
                                <option value="">-- Pilih Jenis Retur --</option>
                                <option value="tukar_barang" {{ old('jenis_retur') == 'tukar_barang' ? 'selected' : '' }}>Tukar Barang</option>
                                <option value="refund" {{ old('jenis_retur') == 'refund' ? 'selected' : '' }}>Refund (Pengembalian Uang)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="memo" class="form-label">Catatan</label>
                            <textarea name="memo" id="memo" class="form-control" rows="2">{{ old('memo') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Item Retur</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No.</th>
                                <th width="30%">Item</th>
                                <th width="15%">Qty Dibeli</th>
                                <th width="15%">Qty Retur</th>
                                <th width="10%">Satuan</th>
                                <th width="15%">Harga Satuan</th>
                                <th width="15%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @foreach($pembelian->details as $index => $detail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if($detail->bahan_baku_id && $detail->bahanBaku)
                                            <div class="d-flex align-items-center">
                                                <span class="text-primary fw-semibold me-2">BB</span>
                                                <span>{{ $detail->bahanBaku->nama_bahan }}</span>
                                            </div>
                                            <input type="hidden" name="items[{{ $index }}][pembelian_detail_id]" value="{{ $detail->id }}">
                                            <input type="hidden" name="items[{{ $index }}][bahan_baku_id]" value="{{ $detail->bahan_baku_id }}">
                                        @elseif($detail->bahan_pendukung_id && $detail->bahanPendukung)
                                            <div class="d-flex align-items-center">
                                                <span class="text-info fw-semibold me-2">BP</span>
                                                <span>{{ $detail->bahanPendukung->nama_bahan }}</span>
                                            </div>
                                            <input type="hidden" name="items[{{ $index }}][pembelian_detail_id]" value="{{ $detail->id }}">
                                            <input type="hidden" name="items[{{ $index }}][bahan_pendukung_id]" value="{{ $detail->bahan_pendukung_id }}">
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Item tidak diketahui
                                            </span>
                                            <input type="hidden" name="items[{{ $index }}][pembelian_detail_id]" value="{{ $detail->id }}">
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" value="{{ $detail->jumlah }} {{ $detail->satuan_nama }}" readonly>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[{{ $index }}][qty]" 
                                               class="form-control qty-input" 
                                               data-price="{{ $detail->harga_satuan }}"
                                               value="{{ old('items.'.$index.'.qty', 0) }}" 
                                               min="0" max="{{ $detail->jumlah }}">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][satuan]" class="form-control" 
                                               value="{{ $detail->satuan_nama }}" readonly>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" 
                                               value="Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}" readonly>
                                        <input type="hidden" name="items[{{ $index }}][harga_satuan]" value="{{ $detail->harga_satuan }}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control subtotal-display" readonly value="Rp 0">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Dynamic Summary Section -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calculator me-2"></i>Summary Retur
                </h5>
            </div>
            <div class="card-body">
                <!-- Summary untuk Refund (Pengembalian Uang) -->
                <div id="summary_refund" class="summary-section" style="display: none;">
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <div class="summary-table">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="text-end"><strong>Total Harga:</strong></td>
                                        <td class="text-end" id="refund_subtotal">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td class="text-end"><strong>PPN (11%):</strong></td>
                                        <td class="text-end" id="refund_ppn">Rp 0</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td class="text-end"><strong>Total Retur:</strong></td>
                                        <td class="text-end"><strong id="refund_total">Rp 0</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary untuk Tukar Barang -->
                <div id="summary_tukar" class="summary-section" style="display: none;">
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <div class="summary-table">
                                <table class="table table-sm">
                                    <tr>
                                        <td class="text-end"><strong>Total Harga:</strong></td>
                                        <td class="text-end" id="tukar_subtotal">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td class="text-end"><strong>PPN (11%):</strong></td>
                                        <td class="text-end" id="tukar_ppn">Rp 0</td>
                                    </tr>
                                    <tr class="table-info">
                                        <td class="text-end"><strong>Total Retur:</strong></td>
                                        <td class="text-end"><strong id="tukar_total_qty">0 Unit</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Default message when no jenis retur selected -->
                <div id="summary_placeholder">
                    <div class="text-center text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        Pilih jenis retur untuk melihat summary
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('transaksi.retur-pembelian.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Retur</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const jenisReturSelect = document.getElementById('jenis_retur');
    const summaryRefund = document.getElementById('summary_refund');
    const summaryTukar = document.getElementById('summary_tukar');
    const summaryPlaceholder = document.getElementById('summary_placeholder');

    // Toggle summary display based on jenis retur selection
    function toggleSummaryDisplay() {
        const jenisRetur = jenisReturSelect.value;
        
        // Hide all summaries first
        summaryRefund.style.display = 'none';
        summaryTukar.style.display = 'none';
        summaryPlaceholder.style.display = 'none';
        
        if (jenisRetur === 'refund') {
            summaryRefund.style.display = 'block';
        } else if (jenisRetur === 'tukar_barang') {
            summaryTukar.style.display = 'block';
        } else {
            summaryPlaceholder.style.display = 'block';
        }
        
        // Recalculate totals when display changes
        calculateTotals();
    }

    // Calculate totals and update appropriate summary
    function calculateTotals() {
        let totalAmount = 0;
        let totalQty = 0;
        let totalUnit = '';
        const jenisRetur = jenisReturSelect.value;
        
        document.querySelectorAll('.qty-input').forEach(function(input) {
            const qty = parseFloat(input.value) || 0;
            const price = parseFloat(input.dataset.price) || 0;
            const subtotal = qty * price;
            
            // Update individual row subtotal
            const row = input.closest('tr');
            const subtotalDisplay = row.querySelector('.subtotal-display');
            subtotalDisplay.value = 'Rp ' + subtotal.toLocaleString('id-ID');
            
            // Add to totals
            totalAmount += subtotal;
            
            // For quantity calculation, get the unit from the row
            if (qty > 0) {
                totalQty += qty;
                const unitInput = row.querySelector('input[name*="[satuan]"]');
                if (unitInput && unitInput.value) {
                    totalUnit = unitInput.value; // Use the last non-empty unit
                }
            }
        });
        
        // Calculate PPN (11%)
        const ppnAmount = totalAmount * 0.11;
        const totalWithPpn = totalAmount + ppnAmount;
        
        // Update summary based on jenis retur
        if (jenisRetur === 'refund') {
            // Refund summary - show all in currency
            document.getElementById('refund_subtotal').textContent = 'Rp ' + totalAmount.toLocaleString('id-ID');
            document.getElementById('refund_ppn').textContent = 'Rp ' + ppnAmount.toLocaleString('id-ID');
            document.getElementById('refund_total').textContent = 'Rp ' + totalWithPpn.toLocaleString('id-ID');
        } else if (jenisRetur === 'tukar_barang') {
            // Tukar barang summary - show total in quantity + unit
            document.getElementById('tukar_subtotal').textContent = 'Rp ' + totalAmount.toLocaleString('id-ID');
            document.getElementById('tukar_ppn').textContent = 'Rp ' + ppnAmount.toLocaleString('id-ID');
            
            // Format quantity with unit
            let qtyDisplay = totalQty.toLocaleString('id-ID');
            if (totalUnit) {
                qtyDisplay += ' ' + totalUnit;
            } else {
                qtyDisplay += ' Unit';
            }
            document.getElementById('tukar_total_qty').textContent = qtyDisplay;
        }
    }
    
    // Event listeners
    jenisReturSelect.addEventListener('change', toggleSummaryDisplay);
    
    document.querySelectorAll('.qty-input').forEach(function(input) {
        input.addEventListener('input', calculateTotals);
    });
    
    // Initial setup
    toggleSummaryDisplay();
    calculateTotals();
});
</script>
@endpush
@endsection
