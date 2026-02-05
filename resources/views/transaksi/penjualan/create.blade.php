@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="mb-3">Tambah Penjualan</h3>

    @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('transaksi.penjualan.store') }}" method="POST" id="form-penjualan">
        @csrf

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Metode Pembayaran</label>
                <select name="payment_method" id="payment_method_jual" class="form-select" required>
                    <option value="cash" selected>Tunai</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="credit">Kredit</option>
                </select>
            </div>
            <div class="col-md-3" id="sumber_dana_wrapper_jual">
                <label class="form-label">Terima di</label>
                <select name="sumber_dana" id="sumber_dana_jual" class="form-select">
                    @foreach($kasbank as $kb)
                        <option value="{{ $kb->kode_akun }}" {{ $kb->kode_akun == '1101' ? 'selected' : '' }}>
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
                        <th class="text-end">Subtotal</th>
                        <th style="width:6%"><button class="btn btn-success btn-sm" type="button" id="addRowJual">+</button></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="produk_id[]" class="form-select produk-select" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($produks as $p)
                                    <option value="{{ $p->id }}" 
                                            data-price="{{ $p->harga_jual ?? 0 }}"
                                            data-stok="{{ $p->stok_tersedia ?? 0 }}">
                                        {{ $p->nama_produk ?? $p->nama }} (Stok: {{ number_format($p->stok_tersedia ?? 0, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted stok-info"></small>
                        </td>
                        <td><input type="number" step="0.0001" min="0.0001" name="jumlah[]" class="form-control jumlah" value="1" required></td>
                        <td><input type="number" step="0.01" min="0" name="harga_satuan[]" class="form-control harga" value="0" readonly required></td>
                        <td><input type="number" step="0.01" min="0" max="100" name="diskon_persen[]" class="form-control diskon" value="0"></td>
                        <td><input type="text" class="form-control subtotal" value="0" readonly></td>
                        <td><button type="button" class="btn btn-danger btn-sm removeRow">-</button></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-3 ms-auto">
                <label class="form-label">Subtotal Produk</label>
                <input type="text" name="subtotal_produk" class="form-control" value="0" readonly>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Biaya Ongkir</label>
                <input type="number" step="0.01" min="0" name="biaya_ongkir" class="form-control" value="0" id="biaya_ongkir">
            </div>
            <div class="col-md-3">
                <label class="form-label">Biaya Service</label>
                <input type="number" step="0.01" min="0" name="biaya_service" class="form-control" value="0" id="biaya_service">
            </div>
            <div class="col-md-3">
                <label class="form-label">PPN (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="ppn_persen" class="form-control" value="11" id="ppn_persen">
            </div>
            <div class="col-md-3">
                <label class="form-label">Total PPN</label>
                <input type="text" name="total_ppn" class="form-control" value="0" readonly id="total_ppn">
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4 ms-auto">
                <label class="form-label">Total Final</label>
                <input type="text" name="total" class="form-control" value="0" readonly id="total_final">
            </div>
        </div>

        <div class="text-end mt-4">
            <a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">Batal</a>
            <button class="btn btn-success">Simpan</button>
        </div>
    </form>
</div>

<script>
// Product data for barcode lookup
const productData = {
    @foreach($produks as $p)
    '{{ $p->barcode ?? '' }}': {
        id: {{ $p->id }},
        nama: '{{ addslashes($p->nama_produk ?? $p->nama) }}',
        harga: {{ $p->harga_jual ?? 0 }},
        stok: {{ $p->stok_tersedia ?? 0 }}
    },
    @endforeach
};

// Barcode scanner functionality
function searchBarcode() {
    const barcodeInput = document.getElementById('barcode-scanner');
    const barcode = barcodeInput.value.trim();
    
    if (!barcode) {
        updateBarcodeStatus('Masukkan barcode', 'warning');
        return;
    }
    
    processBarcode(barcode);
}

function processBarcode(barcode) {
    const barcodeInput = document.getElementById('barcode-scanner');
    const product = productData[barcode];
    
    if (product) {
        // Product found - add to table
        addProductByBarcode(product);
        updateBarcodeStatus('✓ ' + product.nama, 'success');
        barcodeInput.value = '';
        
        // Play success sound (optional)
        playBeep(true);
    } else {
        // Product not found
        updateBarcodeStatus('Produk tidak ditemukan', 'danger');
        playBeep(false);
    }
    
    // Refocus to barcode input for continuous scanning
    setTimeout(() => barcodeInput.focus(), 100);
}

function addProductByBarcode(product) {
    const table = document.getElementById('detailTableJual');
    const tbody = table.querySelector('tbody');
    
    // Check if product already exists in table
    const existingRow = findExistingProductRow(product.id);
    
    if (existingRow) {
        // Increment quantity
        const qtyInput = existingRow.querySelector('.jumlah');
        const currentQty = parseFloat(qtyInput.value) || 0;
        const newQty = currentQty + 1;
        
        // Check stock
        if (newQty > product.stok) {
            updateBarcodeStatus('Stok tidak cukup!', 'danger');
            playBeep(false);
            return;
        }
        
        qtyInput.value = newQty;
        recalcRow(existingRow);
        recalcTotal();
        
        // Highlight row
        highlightRow(existingRow);
    } else {
        // Add new row
        const firstRow = tbody.rows[0];
        const firstSelect = firstRow.querySelector('.produk-select');
        
        // If first row is empty, use it
        if (!firstSelect.value) {
            firstSelect.value = product.id;
            setPriceFromSelect(firstRow);
            highlightRow(firstRow);
        } else {
            // Clone and add new row
            const clone = firstRow.cloneNode(true);
            clone.querySelectorAll('input').forEach(inp => {
                if (inp.classList.contains('jumlah')) inp.value = 1;
                else if (inp.classList.contains('harga')) inp.value = product.harga.toFixed(2);
                else if (inp.classList.contains('diskon')) inp.value = 0;
                else if (inp.classList.contains('subtotal')) inp.value = product.harga.toLocaleString();
            });
            
            const select = clone.querySelector('.produk-select');
            select.value = product.id;
            
            tbody.appendChild(clone);
            recalcTotal();
            highlightRow(clone);
        }
    }
}

function findExistingProductRow(productId) {
    const table = document.getElementById('detailTableJual');
    const rows = table.querySelectorAll('tbody tr');
    
    for (let row of rows) {
        const select = row.querySelector('.produk-select');
        if (select && select.value == productId) {
            return row;
        }
    }
    return null;
}

function highlightRow(row) {
    row.style.backgroundColor = '#d4edda';
    setTimeout(() => {
        row.style.backgroundColor = '';
        row.style.transition = 'background-color 0.5s ease';
    }, 500);
}

function updateBarcodeStatus(message, type) {
    const status = document.getElementById('barcode-status');
    status.textContent = message;
    status.className = 'badge bg-' + type;
    
    // Reset to default after 3 seconds
    setTimeout(() => {
        status.textContent = 'Siap Scan';
        status.className = 'badge bg-secondary';
    }, 3000);
}

function playBeep(success) {
    // Simple beep using Web Audio API
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = success ? 800 : 300;
        oscillator.type = 'sine';
        gainNode.gain.value = 0.1;
        
        oscillator.start();
        setTimeout(() => oscillator.stop(), success ? 100 : 200);
    } catch (e) {
        // Audio not supported, ignore
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('detailTableJual');
    const addBtn = document.getElementById('addRowJual');
    const totalInput = document.querySelector('input[name="total"]');
    
    // Barcode scanner Enter key handler
    const barcodeInput = document.getElementById('barcode-scanner');
    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchBarcode();
        }
    });
    
    // Auto-focus barcode input when pressing F2
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F2') {
            e.preventDefault();
            barcodeInput.focus();
            barcodeInput.select();
        }
    });

    // Show/hide sumber dana based on payment method
    function toggleSumberDana() {
        const paymentMethod = document.getElementById('payment_method_jual').value;
        const sumberDanaWrapper = document.getElementById('sumber_dana_wrapper_jual');
        const sumberDana = document.getElementById('sumber_dana_jual');
        
        if (paymentMethod === 'cash' || paymentMethod === 'transfer') {
            sumberDanaWrapper.style.display = 'block';
            sumberDana.required = true;
            
            // Update options based on payment method
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

    function recalcRow(tr) {
        const q = parseFloat(tr.querySelector('.jumlah').value) || 0;
        const p = parseFloat(tr.querySelector('.harga').value) || 0;
        const dPct = Math.min(Math.max(parseFloat(tr.querySelector('.diskon').value) || 0, 0), 100);
        const sub = q * p;
        const dNom = sub * (dPct/100.0);
        const line = Math.max(sub - dNom, 0);
        tr.querySelector('.subtotal').value = line.toLocaleString();
    }

    function recalcTotal() {
        let sum = 0;
        table.querySelectorAll('tbody tr').forEach(tr => {
            const val = (tr.querySelector('.subtotal').value || '0').replace(/,/g,'');
            sum += parseFloat(val) || 0;
        });
        
        // Update subtotal produk
        const subtotalProdukInput = document.querySelector('input[name="subtotal_produk"]');
        if (subtotalProdukInput) {
            subtotalProdukInput.value = sum.toLocaleString();
        }
        
        // Get additional costs
        const biayaOngkir = parseFloat(document.getElementById('biaya_ongkir').value) || 0;
        const biayaService = parseFloat(document.getElementById('biaya_service').value) || 0;
        const ppnPersen = parseFloat(document.getElementById('ppn_persen').value) || 0;
        
        // Calculate PPN base (subtotal + ongkir + service)
        const ppnBase = sum + biayaOngkir + biayaService;
        const totalPPN = ppnBase * (ppnPersen / 100);
        
        // Update PPN
        const totalPPNInput = document.getElementById('total_ppn');
        if (totalPPNInput) {
            totalPPNInput.value = totalPPN.toLocaleString();
        }
        
        // Calculate final total
        const finalTotal = sum + biayaOngkir + biayaService + totalPPN;
        
        // Update total
        const totalInput = document.getElementById('total_final');
        if (totalInput) {
            totalInput.value = finalTotal.toLocaleString();
        }
    }

    function setPriceFromSelect(tr) {
        const sel = tr.querySelector('.produk-select');
        const opt = sel.options[sel.selectedIndex];
        const price = parseFloat(opt?.getAttribute('data-price') || '0') || 0;
        const stok = parseFloat(opt?.getAttribute('data-stok') || '0') || 0;
        
        tr.querySelector('.harga').value = price.toFixed(2);
        
        // Update stok info
        const stokInfo = tr.querySelector('.stok-info');
        if (stokInfo && opt.value) {
            stokInfo.textContent = `Stok tersedia: ${stok.toLocaleString()}`;
            stokInfo.style.color = stok > 0 ? '#28a745' : '#dc3545';
        }
        
        // Set max qty to available stock
        const qtyInput = tr.querySelector('.jumlah');
        qtyInput.setAttribute('data-max-stok', stok);
        
        recalcRow(tr); recalcTotal();
    }
    
    function validateStock(tr) {
        const qtyInput = tr.querySelector('.jumlah');
        const qty = parseFloat(qtyInput.value) || 0;
        const maxStok = parseFloat(qtyInput.getAttribute('data-max-stok') || '0') || 0;
        
        if (qty > maxStok) {
            alert(`Stok tidak cukup! Stok tersedia: ${maxStok.toLocaleString()}, Anda input: ${qty.toLocaleString()}`);
            qtyInput.value = maxStok;
            qtyInput.style.borderColor = '#dc3545';
            return false;
        } else {
            qtyInput.style.borderColor = '';
            return true;
        }
    }

    addBtn.addEventListener('click', () => {
        const tbody = table.querySelector('tbody');
        const clone = tbody.rows[0].cloneNode(true);
        clone.querySelectorAll('input').forEach(inp => {
            if (inp.classList.contains('jumlah')) inp.value = 1;
            else if (inp.classList.contains('harga')) inp.value = 0;
            else if (inp.classList.contains('diskon')) inp.value = 0;
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
        if (e.target && (e.target.classList.contains('jumlah') || e.target.classList.contains('harga') || e.target.classList.contains('diskon'))) {
            const tr = e.target.closest('tr');
            
            // Validate stock if qty changed
            if (e.target.classList.contains('jumlah')) {
                validateStock(tr);
            }
            
            recalcRow(tr); recalcTotal();
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
    setPriceFromSelect(table.querySelector('tbody tr'));
    recalcRow(table.querySelector('tbody tr')); recalcTotal();
    
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
            alert('Mohon perbaiki jumlah produk yang melebihi stok tersedia!');
            return false;
        }
    });
});
</script>
@endsection
