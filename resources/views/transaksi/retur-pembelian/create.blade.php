@extends('layouts.app')

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
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="alasan" class="form-label">Alasan Retur <span class="text-danger">*</span></label>
                            <textarea name="alasan" id="alasan" class="form-control" rows="2" required>{{ old('alasan') }}</textarea>
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
                                <th width="5%">#</th>
                                <th width="30%">Bahan Baku</th>
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
                                        {{ $detail->bahanBaku->nama_bahan ?? '-' }}
                                        <input type="hidden" name="items[{{ $index }}][pembelian_detail_id]" value="{{ $detail->id }}">
                                        <input type="hidden" name="items[{{ $index }}][bahan_baku_id]" value="{{ $detail->bahan_baku_id }}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" value="{{ $detail->jumlah }} {{ $detail->satuan }}" readonly>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[{{ $index }}][qty]" 
                                               class="form-control qty-input" 
                                               data-price="{{ $detail->harga_satuan }}"
                                               value="{{ old('items.'.$index.'.qty', 0) }}" min="0" max="{{ $detail->jumlah }}">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][satuan]" class="form-control" 
                                               value="{{ $detail->satuan }}" readonly>
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
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end">Total Retur:</th>
                                <th>
                                    <input type="text" id="totalDisplay" class="form-control fw-bold" readonly value="Rp 0">
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('transaksi.retur.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Retur</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hitung subtotal dan total
    function calculateTotals() {
        let total = 0;
        
        document.querySelectorAll('.qty-input').forEach(function(input) {
            const qty = parseFloat(input.value) || 0;
            const price = parseFloat(input.dataset.price) || 0;
            const subtotal = qty * price;
            
            const row = input.closest('tr');
            const subtotalDisplay = row.querySelector('.subtotal-display');
            subtotalDisplay.value = 'Rp ' + subtotal.toLocaleString('id-ID');
            
            total += subtotal;
        });
        
        document.getElementById('totalDisplay').value = 'Rp ' + total.toLocaleString('id-ID');
    }
    
    // Event listener untuk qty input
    document.querySelectorAll('.qty-input').forEach(function(input) {
        input.addEventListener('input', calculateTotals);
    });
    
    // Hitung awal
    calculateTotals();
});
</script>
@endpush
@endsection
