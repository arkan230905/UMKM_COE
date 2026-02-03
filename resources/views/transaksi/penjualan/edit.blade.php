@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="mb-3">Edit Penjualan</h3>

    @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('transaksi.penjualan.update',$penjualan->id) }}" method="POST" id="form-penjualan">
        @csrf
        @method('PATCH')

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ $penjualan->tanggal }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Metode Pembayaran</label>
                <select name="payment_method" id="payment_method_jual" class="form-select" required>
                    <option value="cash" {{ $penjualan->payment_method == 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="transfer" {{ $penjualan->payment_method == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                    <option value="credit" {{ $penjualan->payment_method == 'credit' ? 'selected' : '' }}>Kredit</option>
                </select>
            </div>
            <div class="col-md-6" id="sumber_dana_wrapper_jual">
                <label class="form-label">Terima di</label>
                <select name="sumber_dana" id="sumber_dana_jual" class="form-select">
                    @foreach($kasbank as $kb)
                        <option value="{{ $kb->kode_akun }}" {{ $penjualan->sumber_dana == $kb->kode_akun ? 'selected' : '' }}>
                            {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Barcode Scanner Input -->
        <div class="card mb-3 border-primary">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-barcode fa-2x text-primary"></i>
                    </div>
                    <div class="col">
                        <label class="form-label mb-1 small text-muted">Scan Barcode Produk</label>
                        <div class="input-group">
                            <input type="text" id="barcode-scanner" class="form-control form-control-lg" 
                                   placeholder="Scan atau ketik barcode produk..." 
                                   autocomplete="off" autofocus>
                            <button type="button" class="btn btn-primary" onclick="searchBarcode()">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                        <small class="text-muted">Tekan Enter setelah scan barcode untuk menambah produk otomatis</small>
                    </div>
                    <div class="col-auto">
                        <span id="barcode-status" class="badge bg-secondary">Siap Scan</span>
                    </div>
                </div>
            </div>
        </div>

        <h5>Detail Penjualan</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="detailTableJual">
                <thead class="table-dark">
                    <tr>
                        <th>Produk</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Harga/Satuan</th>
                        <th class="text-end">Diskon (%)</th>
                        <th class="text-end">Biaya Angkut</th>
                        <th style="width:6%"><button class="btn btn-success btn-sm" type="button" id="addRowJual">+</button></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Load existing details
                        $existingDetails = [];
                        foreach ($penjualan->details as $detail) {
                            $existingDetails[] = [
                                'produk_id' => $detail->produk_id,
                                'jumlah' => $detail->jumlah,
                                'harga_satuan' => $detail->harga_satuan,
                                'diskon_persen' => $detail->diskon_persen,
                                'biaya_angkut' => $detail->biaya_angkut ?? 0
                            ];
                        }
                    @endphp
                    @foreach ($existingDetails as $index => $detail)
                    <tr>
                        <td>
                            <select name="produk_id[]" class="form-select produk-select" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($produks as $p)
                                    <option value="{{ $p->id }}" 
                                            data-price="{{ $p->harga_jual ?? 0 }}"
                                            data-stok="{{ $p->stok_tersedia ?? 0 }}"
                                            {{ $p->nama_produk ?? $p->nama }} (Stok: {{ number_format($p->stok_tersedia ?? 0, 0, ',', '.') }})
                                        @if ($p->id == $detail['produk_id']) selected @endif
                                    >{{ $p->nama_produk ?? $p->nama }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted stok-info"></small>
                        </td>
                        <td><input type="number" step="0.0001" min="0.0001" name="jumlah[]" class="form-control jumlah" value="{{ $detail['jumlah'] }}" required></td>
                        <td><input type="text" name="harga_satuan[]" class="form-control harga" value="{{ number_format($detail['harga_satuan'], 0, ',', '.') }}" readonly required></td>
                        <td><input type="number" step="0.01" min="0" max="100" name="diskon_persen[]" class="form-control diskon" value="{{ $detail['diskon_persen'] }}"></td>
                        <td><input type="text" name="biaya_angkut[]" class="form-control biaya_angkut" value="{{ number_format($detail['biaya_angkut'], 0, ',', '.') }}"></td>
                        <td><button type="button" class="btn btn-danger btn-sm removeRow">-</button></td>
                    </tr>
                    @endforeach
                    @if (count($existingDetails) === 0)
                    <tr>
                        <td>
                            <select name="produk_id[]" class="form-select produk-select" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($produks as $p)
                                    <option value="{{ $p->id }}" 
                                            data-price="{{ $p->harga_jual ?? 0 }}"
                                            data-stok="{{ $p->stok_tersedia ?? 0 }}">
                                    >{{ $p->nama_produk ?? $p->nama }} (Stok: {{ number_format($p->stok_tersedia ?? 0, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted stok-info"></small>
                        </td>
                        <td><input type="number" step="0.0001" min="0.0001" name="jumlah[]" class="form-control jumlah" value="1" required></td>
                        <td><input type="text" name="harga_satuan[]" class="form-control harga" value="0" readonly required></td>
                        <td><input type="number" step="0.01" min="0" max="100" name="diskon_persen[]" class="form-control diskon" value="0"></td>
                        <td><input type="text" name="biaya_angkut[]" class="form-control biaya_angkut" value="0"></td>
                        <td><button type="button" class="btn btn-danger btn-sm removeRow">-</button></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Rincian Biaya -->
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Perhitungan PPN dan Total</h6>
            </div>
            <div class="card-body">
                <!-- Baris 1: Total Penjualan -->
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Total Penjualan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="total_penjualan" class="form-control text-end fw-bold bg-light" value="{{ number_format($penjualan->total - ($penjualan->total_ppn ?? 0), 0, ',', '.') }}" readonly id="total_penjualan">
                        </div>
                    </div>
                </div>

                <!-- Baris 2: PPN -->
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">PPN (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" name="ppn_persen" class="form-control text-end" value="{{ $penjualan->ppn_persen ?? 11 }}" id="ppn_persen">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total PPN</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="total_ppn" class="form-control text-end" value="{{ number_format($penjualan->total_ppn ?? 0, 0, ',', '.') }}" readonly id="total_ppn">
                        </div>
                    </div>
                </div>

                <!-- Baris 3: Total Dibayarkan -->
                <div class="row g-3 mt-2">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Total Dibayarkan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="total" class="form-control text-end fw-bold bg-light" value="{{ number_format($penjualan->total, 0, ',', '.') }}" readonly id="total_final">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mt-4">
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">Batal</a>
            <button class="btn btn-success">Update</button>
        </div>
    </form>
</div>

@push('styles')
<style>
.stok-info {
    font-size: 0.75em;
    display: block;
    margin-top: 2px;
}
</style>
@endpush

@push('scripts')
<script>
    // Format currency function
    function formatCurrency(value) {
        // Remove non-numeric characters
        let number = value.toString().replace(/[^\d]/g, '');
        // Convert to number and format with thousand separator
        let formatted = parseFloat(number).toLocaleString('id-ID');
        return formatted;
    }
    
    function parseCurrency(formatted) {
        // Remove thousand separators and convert to float
        return parseFloat(formatted.toString().replace(/\./g, '')) || 0;
    }

    function recalcRow(tr) {
        const qty = parseFloat(tr.querySelector('.jumlah').value) || 0;
        const price = parseCurrency(tr.querySelector('.harga').value) || 0;
        const diskonPersen = parseFloat(tr.querySelector('.diskon').value) || 0;
        const biayaAngkut = parseCurrency(tr.querySelector('.biaya_angkut').value) || 0;
        
        const subtotal = qty * price;
        const diskonNominal = subtotal * (diskonPersen / 100);
        const subtotalAfterDiskon = subtotal - diskonNominal + biayaAngkut;
        
        // Update subtotal field if exists
        const subtotalField = tr.querySelector('.subtotal');
        if (subtotalField) {
            subtotalField.value = subtotalAfterDiskon.toLocaleString('id-ID');
        }
    }

    function recalcTotal() {
        let sum = 0;
        let totalDiskon = 0;
        let totalBiayaAngkut = 0;
        
        table.querySelectorAll('tbody tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.jumlah').value) || 0;
            const price = parseCurrency(tr.querySelector('.harga').value) || 0;
            const diskonPersen = parseFloat(tr.querySelector('.diskon').value) || 0;
            const biayaAngkut = parseCurrency(tr.querySelector('.biaya_angkut').value) || 0;
            
            const subtotal = qty * price;
            const diskonNominal = subtotal * (diskonPersen / 100);
            const subtotalAfterDiskon = subtotal - diskonNominal;
            
            sum += subtotalAfterDiskon;
            totalDiskon += diskonNominal;
            totalBiayaAngkut += biayaAngkut;
        });
        
        // Calculate total penjualan (subtotal after diskon + total biaya angkut)
        const totalPenjualan = sum + totalBiayaAngkut;
        
        // Update total penjualan
        const totalPenjualanInput = document.getElementById('total_penjualan');
        if (totalPenjualanInput) {
            totalPenjualanInput.value = totalPenjualan.toLocaleString('id-ID');
        }
        
        // Get PPN
        const ppnPersen = parseFloat(document.getElementById('ppn_persen').value) || 0;
        const totalPPN = totalPenjualan * (ppnPersen / 100);
        
        // Update PPN
        const totalPPNInput = document.getElementById('total_ppn');
        if (totalPPNInput) {
            totalPPNInput.value = totalPPN.toLocaleString('id-ID');
        }
        
        // Calculate total dibayarkan (total penjualan + PPN)
        const finalTotal = totalPenjualan + totalPPN;
        
        // Update total final
        const totalInput = document.getElementById('total_final');
        if (totalInput) {
            totalInput.value = finalTotal.toLocaleString('id-ID');
        }
    }

    function setPriceFromSelect(tr) {
        const sel = tr.querySelector('.produk-select');
        const opt = sel.options[sel.selectedIndex];
        const price = parseFloat(opt?.getAttribute('data-price') || '0') || 0;
        const stok = parseFloat(opt?.getAttribute('data-stok') || '0') || 0;
        
        tr.querySelector('.harga').value = formatCurrency(price);
        
        // Update stok info
        const stokInfo = tr.querySelector('.stok-info');
        if (stokInfo && opt.value) {
            stokInfo.textContent = `Stok tersedia: ${stok.toLocaleString()}`;
            stokInfo.style.color = stok > 0 ? '#28a745' : '#dc3545';
        }
        
        // Set max qty to available stock
        const qtyInput = tr.querySelector('.jumlah');
        qtyInput.setAttribute('data-max-stok', stok);
        
        recalcRow(tr); 
        recalcTotal();
    }
    
    function validateStock(tr) {
        const qtyInput = tr.querySelector('.jumlah');
        const qty = parseFloat(qtyInput.value) || 0;
        const maxStok = parseFloat(qtyInput.getAttribute('data-max-stok') || '0') || 0;
        
        if ($qty > $maxStok) {
            alert(`Stok tidak cukup! Stok tersedia: ${maxStok.toLocaleString()}, Anda input: ${qty.toLocaleString()}`);
            qtyInput.value = $maxStok;
            qtyInput.style.borderColor = '#dc3545';
            return false;
        } else {
            qtyInput.style.borderColor = '';
            return true;
        }
    }

    // Toggle sumber dana berdasarkan payment method
    function toggleSumberDana() {
        const paymentMethod = document.getElementById('payment_method_jual').value;
        const sumberDanaWrapper = document.getElementById('sumber_dana_wrapper_jual');
        const sumberDana = document.getElementById('sumber_dana_jual');
        
        if (paymentMethod === 'cash' || paymentMethod === 'transfer') {
            sumberDanaWrapper.style.display = 'block';
            sumberDana.required = true;
            
            if (paymentMethod === 'cash') {
                sumberDana.innerHTML = `
                    <option value="1101">Kas Kecil (1101)</option>
                    <option value="101">Kas (101)</option>
                `;
            } else if (paymentMethod === 'transfer') {
                sumberDana.innerHTML = `
                    <option value="1102">Kas di Bank (1102)</option>
                    <option value="102">Bank (102)</option>
                `;
            }
        } else {
            sumberDanaWrapper.style.display = 'none';
            sumberDana.required = false;
        }
    }
    
    // Initial toggle
    toggleSumberDana();
    
    // Listen to payment method changes
    document.getElementById('payment_method_jual').addEventListener('change', toggleSumberDana);

    // Add row functionality
    const addBtn = document.getElementById('addRowJual');
    const table = document.getElementById('detailTableJual');
    
    addBtn.addEventListener('click', () => {
        const tbody = table.querySelector('tbody');
        const clone = tbody.rows[0].cloneNode(true);
        clone.querySelectorAll('input').forEach(inp => {
            if (inp.classList.contains('jumlah')) inp.value = 1;
            else if (inp.classList.contains('harga')) inp.value = formatCurrency(0);
            else if (inp.classList.contains('diskon')) inp.value = 0;
            else if (inp.classList.contains('biaya_angkut')) inp.value = formatCurrency(0);
            else if (inp.classList.contains('subtotal')) inp.value = 0;
        });
        clone.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
        table.querySelector('tbody').appendChild(clone);
    });

    table.addEventListener('change', (e) => {
        if (e.target && e.target.classList.contains('produk-select')) {
            const tr = e.target.closest('tr');
            setPriceFromSelect(tr);
        }
    });
    table.addEventListener('input', (e) => {
        if (e.target && (e.target.classList.contains('jumlah') || e.target.classList.contains('harga') || e.target.classList.contains('diskon') || e.target.classList.contains('biaya_angkut'))) {
            const tr = e.target.closest('tr');
            
            // Format harga and biaya angkut input
            if (e.target.classList.contains('harga') || e.target.classList.contains('biaya_angkut')) {
                // Save cursor position
                const start = e.target.selectionStart;
                const end = e.target.selectionEnd;
                const value = e.target.value;
                
                // Format the value
                e.target.value = formatCurrency(value);
                
                // Restore cursor position (adjust for formatting)
                const newValue = e.target.value;
                const diff = newValue.length - value.length;
                e.target.setSelectionRange(start + diff, end + diff);
            }
            
            // Validate stock if qty changed
            if (e.target.classList.contains('jumlah')) {
                validateStock(tr);
            }
            
            recalcRow(tr); 
            recalcTotal();
        }
    });
    
    // Listen to additional cost changes
    document.getElementById('biaya_ongkir').addEventListener('input', recalcTotal);
    document.getElementById('biaya_service').addEventListener('input', recalcTotal);
    document.getElementById('ppn_persen').addEventListener('input', recalcTotal);
    table.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('removeRow')) {
            const rows = table.querySelectorAll('tbody tr');
            if (rows.length > 1) e.target.closest('tr').remove();
            recalcTotal();
        }
    });

    // Init first row
    @if (count($existingDetails) > 0)
        // Initialize existing rows
        document.querySelectorAll('tbody tr').forEach(function(tr) {
            setPriceFromSelect(tr);
        });
    @else
        setPriceFromSelect(table.querySelector('tbody tr')); 
        recalcRow(table.querySelector('tbody tr')); 
        recalcTotal();
    @endif
    
    // Validate before submit
    document.getElementById('form-penjualan').addEventListener('submit', function(e) {
        let hasError = false;
        table.querySelectorAll('tbody tr').forEach(tr => {
            const sel = tr.querySelector('.produk-select');
            if (sel.value) {
                if (!validateStock(tr)) {
                    hasError = true;
                }
            }
        });
        if (hasError) {
            e.preventDefault();
        }
    });
</script>
@endsection
