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
                        <option value="{{ $kb->kode_akun }}">
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
                                   placeholder="Siap untuk scan barcode..." 
                                   autocomplete="off" readonly autofocus>
                            <div class="input-group-text bg-success text-white">
                                <i class="fas fa-wifi me-1"></i>
                                <span id="scan-indicator">Siap Scan</span>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetScannerState()" title="Reset Scanner">
                                <i class="fas fa-refresh"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Sistem otomatis mendeteksi barcode - tidak perlu klik atau tekan tombol
                        </small>
                    </div>
                    <div class="col-auto">
                        <button type="button" id="barcode-status" class="btn btn-outline-secondary btn-sm" onclick="toggleProductList()">
                            <i class="fas fa-list me-1"></i>Daftar Produk
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <h5>Detail Penjualan</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="detailTableJual">
                <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Harga/Satuan</th>
                        <th class="text-end">Diskon (%)</th>
                        <th class="text-end">Subtotal</th>
                        <th style="width:6%">NO<button class="btn btn-success btn-sm" type="button" id="addRowJual">+</button></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="produk_id[]" class="form-select produk-select" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($produks as $p)
                                    <option value="{{ $p->id }}" 
                                            data-price="{{ round($p->harga_jual ?? 0) }}"
                                            data-stok="{{ $p->stok ?? 0 }}">
                                        {{ $p->nama_produk ?? $p->nama }} (Stok: {{ number_format($p->stok ?? 0, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted stok-info"></small>
                        </td>
                        <td><input type="number" step="1" min="1" name="jumlah[]" class="form-control jumlah" value="1" required></td>
                        <td><input type="text" name="harga_satuan[]" class="form-control harga" value="0" readonly required></td>
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
                <input type="number" step="0.01" min="0" name="biaya_ongkir" class="form-control" value="0" id="biaya_ongkir" 
                     onclick="this.focus()" 
                     onfocus="this.select()">
            </div>
            <div class="col-md-3">
                <label class="form-label">Biaya Service</label>
                <input type="number" step="0.01" min="0" name="biaya_service" class="form-control" value="0" id="biaya_service" 
                         onclick="this.focus()" 
                         onfocus="this.select()">
            </div>
            <div class="col-md-3">
                <label class="form-label">PPN (%)</label>
                <input type="number" step="0.01" min="0" name="ppn_persen" class="form-control" value="0" id="ppn_persen" 
                         onclick="this.focus()" 
                         onfocus="this.select()">
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

<!-- Product List Modal -->
<div class="modal fade" id="productListModal" tabindex="-1" aria-labelledby="productListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productListModalLabel">
                    <i class="fas fa-barcode me-2"></i>Daftar Produk & Barcode
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="modal-search" class="form-control" placeholder="Cari produk atau barcode...">
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-sm">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Produk</th>
                                <th>Barcode</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="product-list-body">
                            @foreach($produks as $p)
                            <tr class="product-row" data-barcode="{{ $p->barcode ?? '' }}" data-name="{{ strtolower($p->nama_produk ?? $p->nama) }}">
                                <td>{{ $p->nama_produk ?? $p->nama }}</td>
                                <td>
                                    @if($p->barcode)
                                        <code>{{ $p->barcode }}</code>
                                    @else
                                        <small class="text-muted">Tidak ada</small>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($p->harga_jual ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ ($p->stok ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ number_format($p->stok ?? 0, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="addProductFromModal({{ $p->id }}, '{{ addslashes($p->nama_produk ?? $p->nama) }}', {{ $p->harga_jual ?? 0 }}, {{ $p->stok ?? 0 }})">>
                                        <i class="fas fa-plus"></i> Tambah
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
// Product data for barcode lookup
const productData = {
    @foreach($produks as $p)
    '{{ $p->barcode ?? '' }}': {
        id: {{ $p->id }},
        nama: '{{ addslashes($p->nama_produk ?? $p->nama) }}',
        harga: {{ round($p->harga_jual ?? 0) }},
        stok: {{ $p->stok ?? 0 }}
    },
    @endforeach
};

// Debug: Log productData to console
console.log('Product Data:', productData);

// Global utility functions (must be outside DOMContentLoaded)
function formatCurrency(value) {
    if (value === null || value === undefined || isNaN(value)) {
        return 'Rp 0';
    }
    const roundedValue = Math.round(parseFloat(value) * 1000) / 1000;
    return 'Rp ' + roundedValue.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 3 });
}

function parseCurrency(formattedValue) {
    if (!formattedValue) return 0;
    return parseFloat(formattedValue.toString().replace(/[^\d]/g, '')) || 0;
}

// Global functions for barcode system
function recalcRow(tr) {
    const q = Math.round(parseFloat(tr.querySelector('.jumlah').value) || 0);
    tr.querySelector('.jumlah').value = q; // Ensure integer display
    const p = parseCurrency(tr.querySelector('.harga').value) || 0;
    const dPct = Math.min(Math.max(parseFloat(tr.querySelector('.diskon').value) || 0, 0), 100);
    const sub = q * p;
    const dNom = sub * (dPct/100.0);
    const line = Math.max(sub - dNom, 0);
    tr.querySelector('.subtotal').value = formatCurrency(line);
}

function recalcTotal() {
    const table = document.getElementById('detailTableJual');
    let sum = 0;
    table.querySelectorAll('tbody tr').forEach(tr => {
        const val = (tr.querySelector('.subtotal').value || 'Rp 0').replace(/[^\d]/g,'');
        sum += parseFloat(val) || 0;
    });
    
    // Update subtotal produk
    const subtotalProdukInput = document.querySelector('input[name="subtotal_produk"]');
    if (subtotalProdukInput) {
        subtotalProdukInput.value = formatCurrency(sum);
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
        totalPPNInput.value = formatCurrency(totalPPN);
    }
    
    // Calculate final total
    const finalTotal = sum + biayaOngkir + biayaService + totalPPN;
    
    // Update total
    const totalInput = document.getElementById('total_final');
    if (totalInput) {
        totalInput.value = formatCurrency(finalTotal);
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
    let qty = parseFloat(qtyInput.value) || 0;
    qty = Math.round(qty); // Round to nearest integer
    qtyInput.value = qty; // Update display
    
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

function highlightRow(row) {
    row.style.backgroundColor = '#d4edda';
    setTimeout(() => {
        row.style.backgroundColor = '';
        row.style.transition = 'background-color 0.5s ease';
    }, 500);
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

// Automatic Barcode Scanner System
let barcodeBuffer = '';
let barcodeTimeout = null;
let isProcessing = false;
const BARCODE_TIMEOUT = 100; // 100ms timeout for barcode completion
const MIN_BARCODE_LENGTH = 3; // Minimum barcode length

// Safety mechanism to reset processing state if stuck (reduced frequency)
function resetProcessingState() {
    const scanIndicator = document.getElementById('scan-indicator');
    if (isProcessing && scanIndicator && scanIndicator.textContent === 'Memproses...') {
        console.log('⚠️ Resetting stuck processing state');
        isProcessing = false;
        scanIndicator.textContent = 'Siap Scan';
        scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
    }
}

// Check for stuck processing every 10 seconds (reduced from 5)
setInterval(resetProcessingState, 10000);

// Auto-focus system
function maintainFocus() {
    const barcodeInput = document.getElementById('barcode-scanner');
    const activeElement = document.activeElement;
    
    // Don't interfere if user is typing in other fields
    if (activeElement && (
        activeElement.classList.contains('form-control') && 
        activeElement.id !== 'barcode-scanner'
    )) {
        return; // User is typing in other form fields
    }
    
    // Allow barcode scanner to get focus when needed
    if (activeElement === document.body || 
        activeElement === document.documentElement ||
        activeElement === barcodeInput) {
        barcodeInput.focus();
    }
}

// Automatic barcode detection
function handleBarcodeInput(char) {
    const barcodeInput = document.getElementById('barcode-scanner');
    
    // Always process barcode input - scanner should always be ready
    // Add character to buffer
    barcodeBuffer += char;
    
    // Clear existing timeout
    if (barcodeTimeout) {
        clearTimeout(barcodeTimeout);
    }
    
    // Set new timeout - if no new characters come within BARCODE_TIMEOUT, process the barcode
    barcodeTimeout = setTimeout(() => {
        if (barcodeBuffer.length >= MIN_BARCODE_LENGTH && !isProcessing) {
            processAutomaticBarcode(barcodeBuffer.trim());
        }
        barcodeBuffer = ''; // Clear buffer
    }, BARCODE_TIMEOUT);
}

// Process barcode automatically
function processAutomaticBarcode(barcode) {
    if (isProcessing) return;
    
    isProcessing = true;
    console.log('🔍 Auto-processing barcode:', barcode);
    
    const barcodeInput = document.getElementById('barcode-scanner');
    const scanIndicator = document.getElementById('scan-indicator');
    
    // Update UI to show processing
    scanIndicator.textContent = 'Memproses...';
    scanIndicator.parentElement.className = 'input-group-text bg-warning text-dark';
    
    try {
        const product = productData[barcode];
        
        if (product) {
            console.log('✅ Product found:', product);
            
            // Validate stock before adding
            if (product.stok <= 0) {
                throw new Error('Produk ' + product.nama + ' stok habis!');
            }
            
            // Product found - add to table
            addProductByBarcode(product);
            
            // Success feedback
            scanIndicator.textContent = '✓ ' + product.nama;
            scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
            
            // Play success sound
            playBeep(true);
            
            // Show success notification
            showNotification('Produk ditambahkan: ' + product.nama, 'success');
            
        } else {
            console.log('❌ Product not found for barcode:', barcode);
            
            // Product not found
            scanIndicator.textContent = 'Tidak ditemukan';
            scanIndicator.parentElement.className = 'input-group-text bg-danger text-white';
            
            // Play error sound
            playBeep(false);
            
            // Show error notification
            showNotification('Produk tidak ditemukan: ' + barcode, 'error');
        }
    } catch (error) {
        console.error('Error processing barcode:', error);
        
        // Error feedback
        scanIndicator.textContent = 'Error';
        scanIndicator.parentElement.className = 'input-group-text bg-danger text-white';
        
        // Show specific error message
        showNotification(error.message || 'Terjadi kesalahan saat memproses barcode', 'error');
        
        // Play error sound
        playBeep(false);
    }
    
    // Clear input
    barcodeInput.value = '';
    
    // Reset status after 3 seconds (increased from 2)
    setTimeout(() => {
        scanIndicator.textContent = 'Siap Scan';
        scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
        isProcessing = false;
        
        // Ensure focus is maintained
        maintainFocus();
    }, 3000);
}

// Show notification
function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Manual reset function
function resetScannerState() {
    console.log('🔄 Manually resetting scanner state');
    isProcessing = false;
    barcodeBuffer = '';
    
    if (barcodeTimeout) {
        clearTimeout(barcodeTimeout);
        barcodeTimeout = null;
    }
    
    const scanIndicator = document.getElementById('scan-indicator');
    const barcodeInput = document.getElementById('barcode-scanner');
    
    if (scanIndicator) {
        scanIndicator.textContent = 'Siap Scan';
        scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
    }
    
    if (barcodeInput) {
        barcodeInput.value = '';
        barcodeInput.focus();
    }
    
    showNotification('Scanner direset - siap untuk scan', 'success');
}

// Legacy functions (kept for compatibility)
function searchBarcode() {
    const barcodeInput = document.getElementById('barcode-scanner');
    const barcode = barcodeInput.value.trim();
    
    if (!barcode) {
        showNotification('Masukkan barcode', 'error');
        return;
    }
    
    processAutomaticBarcode(barcode);
}

function processBarcode(barcode) {
    processAutomaticBarcode(barcode);
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
            throw new Error('Stok tidak cukup! Stok tersedia: ' + product.stok);
        }
        
        qtyInput.value = Math.round(newQty);
        recalcRow(existingRow);
        recalcTotal();
        
        // Highlight row
        highlightRow(existingRow);
    } else {
        // Find first empty row or create new one
        let targetRow = null;
        const rows = tbody.querySelectorAll('tr');
        
        // Look for empty row (no product selected)
        for (let row of rows) {
            const select = row.querySelector('.produk-select');
            if (!select.value) {
                targetRow = row;
                break;
            }
        }
        
        // If no empty row found, create new one
        if (!targetRow) {
            targetRow = createNewRow();
            tbody.appendChild(targetRow);
        }
        
        // Fill the row with product data
        const select = targetRow.querySelector('.produk-select');
        const qtyInput = targetRow.querySelector('.jumlah');
        const hargaInput = targetRow.querySelector('.harga');
        const diskonInput = targetRow.querySelector('.diskon');
        
        select.value = product.id;
        qtyInput.value = 1;
        hargaInput.value = formatCurrency(product.harga);
        diskonInput.value = 0;
        
        // Update stock info
        setPriceFromSelect(targetRow);
        
        // Recalculate
        recalcRow(targetRow);
        recalcTotal();
        
        // Highlight row
        highlightRow(targetRow);
        
        // Add new empty row for next scan
        const newEmptyRow = createNewRow();
        tbody.appendChild(newEmptyRow);
    }
}

// Create new empty row
function createNewRow() {
    const table = document.getElementById('detailTableJual');
    const firstRow = table.querySelector('tbody tr');
    const clone = firstRow.cloneNode(true);
    
    // Reset all inputs
    clone.querySelectorAll('input').forEach(inp => {
        if (inp.classList.contains('jumlah')) inp.value = 1;
        else if (inp.classList.contains('harga')) inp.value = formatCurrency(0);
        else if (inp.classList.contains('diskon')) inp.value = 0;
        else if (inp.classList.contains('subtotal')) inp.value = formatCurrency(0);
    });
    
    // Reset select
    clone.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
    
    // Reset stock info
    const stockInfo = clone.querySelector('.stok-info');
    if (stockInfo) {
        stockInfo.textContent = '';
    }
    
    return clone;
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
    // Legacy function - now handled by automatic system
    console.log('Status:', message, type);
}

// Toggle product list modal
function toggleProductList() {
    const modal = new bootstrap.Modal(document.getElementById('productListModal'));
    modal.show();
}

// Add product from modal
function addProductFromModal(productId, productName, price, stock) {
    const product = {
        id: productId,
        nama: productName,
        harga: price,
        stok: stock
    };
    
    addProductByBarcode(product);
    showNotification('✓ ' + productName + ' ditambahkan', 'success');
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('productListModal'));
    if (modal) {
        modal.hide();
    }
    
    // Focus back to barcode input
    setTimeout(() => {
        document.getElementById('barcode-scanner').focus();
    }, 100);
}

// Search products in modal
function filterProductList() {
    const searchTerm = document.getElementById('modal-search').value.toLowerCase();
    const rows = document.querySelectorAll('.product-row');
    
    rows.forEach(row => {
        const productName = row.getAttribute('data-name');
        const barcode = row.getAttribute('data-barcode');
        
        if (productName.includes(searchTerm) || barcode.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
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
    const barcodeInput = document.getElementById('barcode-scanner');
    
    // 🎯 AUTOMATIC BARCODE SCANNING SYSTEM
    
    // Maintain focus on barcode input (less aggressive)
    function ensureBarcodeInputFocus() {
        if (document.activeElement !== barcodeInput && !document.querySelector('.modal.show')) {
            barcodeInput.focus();
        }
    }
    
    // Set initial focus
    barcodeInput.focus();
    
    // Maintain focus every 2 seconds (reduced from 500ms)
    setInterval(ensureBarcodeInputFocus, 2000);
    
    // Global keydown listener for automatic barcode detection
    document.addEventListener('keydown', function(e) {
        // Skip if user is typing in other inputs (except barcode input)
        if (e.target.tagName === 'INPUT' && e.target.id !== 'barcode-scanner') {
            return;
        }
        
        // Skip if modal is open
        if (document.querySelector('.modal.show')) {
            return;
        }
        
        // Skip special keys
        if (e.ctrlKey || e.altKey || e.metaKey) {
            return;
        }
        
        // Handle Enter key (barcode scanners usually send Enter at the end)
        if (e.key === 'Enter') {
            e.preventDefault();
            const currentValue = barcodeInput.value.trim();
            if (currentValue && currentValue.length >= MIN_BARCODE_LENGTH) {
                processAutomaticBarcode(currentValue);
            }
            return;
        }
        
        // Handle printable characters
        if (e.key.length === 1) {
            // Ensure barcode input is focused
            if (document.activeElement !== barcodeInput) {
                barcodeInput.focus();
            }
            
            // Let the character be typed naturally, then handle it
            setTimeout(() => {
                handleBarcodeInput(e.key);
            }, 1);
        }
    });
    
    // Handle direct input to barcode field (simplified)
    barcodeInput.addEventListener('input', function(e) {
        const value = e.target.value;
        
        // If input is cleared, reset buffer
        if (!value) {
            barcodeBuffer = '';
            if (barcodeTimeout) {
                clearTimeout(barcodeTimeout);
            }
            return;
        }
        
        // Simple timeout-based processing
        if (barcodeTimeout) {
            clearTimeout(barcodeTimeout);
        }
        
        barcodeTimeout = setTimeout(() => {
            if (value.length >= MIN_BARCODE_LENGTH && !isProcessing) {
                processAutomaticBarcode(value.trim());
            }
        }, 300); // Increased timeout for better stability
    });
    
    // Prevent form submission on Enter in barcode input
    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });
    
    // Re-focus when clicking on empty areas (less aggressive)
    document.addEventListener('click', function(e) {
        if (e.target === document.body || e.target.classList.contains('container')) {
            setTimeout(() => {
                if (!document.querySelector('.modal.show')) {
                    barcodeInput.focus();
                }
            }, 100);
        }
    });
    
    // Handle window focus/blur
    window.addEventListener('focus', function() {
        setTimeout(() => {
            barcodeInput.focus();
        }, 100);
    });
    
    // 🎯 END AUTOMATIC BARCODE SCANNING SYSTEM
    
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
            
            // Get recent pick from localStorage
            const recentPick = localStorage.getItem('recent_sumber_dana_' + paymentMethod);
            
            // Update options based on payment method - use accounts from kas bank report
            if (paymentMethod === 'cash') {
                sumberDana.innerHTML = `
                    <option value="112">Kas (112)</option>
                    <option value="113">Kas Kecil (113)</option>
                `;
            } else if (paymentMethod === 'transfer') {
                sumberDana.innerHTML = `
                    <option value="111">Kas Bank (111)</option>
                `;
            }
            
            // Set recent pick if exists and valid
            if (recentPick && sumberDana.querySelector(`option[value="${recentPick}"]`)) {
                sumberDana.value = recentPick;
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
    
    // Listen to sumber dana changes and save to localStorage
    document.getElementById('sumber_dana_jual').addEventListener('change', function() {
        const paymentMethod = document.getElementById('payment_method_jual').value;
        if (paymentMethod === 'cash' || paymentMethod === 'transfer') {
            localStorage.setItem('recent_sumber_dana_' + paymentMethod, this.value);
        }
    });

    addBtn.addEventListener('click', () => {
        const tbody = table.querySelector('tbody');
        const clone = tbody.rows[0].cloneNode(true);
        clone.querySelectorAll('input').forEach(inp => {
            if (inp.classList.contains('jumlah')) inp.value = 1;
            else if (inp.classList.contains('harga')) inp.value = 'Rp 0';
            else if (inp.classList.contains('diskon')) inp.value = 0;
            else if (inp.classList.contains('subtotal')) inp.value = 'Rp 0';
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

// Add click event listeners to form fields
document.addEventListener("DOMContentLoaded", function() {
    const formFields = document.querySelectorAll("input, select, textarea");
    const barcodeInput = document.getElementById("barcode-scanner");
    
    nonBarcodeFields.forEach(function(field) {
        field.addEventListener("click", function() {
            // When user clicks on a form field, remove focus from barcode scanner
            barcodeInput.blur();
            console.log("Field clicked:", field.name || field.id);
        });
        
        field.addEventListener("focus", function() {
            // When user focuses on a form field, remove focus from barcode scanner
            barcodeInput.blur();
            console.log("Field focused:", field.name || field.id);
        });
    });
    
    // Add click listener to barcode scanner to ensure it can get focus when needed
    barcodeInput.addEventListener("click", function() {
        barcodeInput.focus();
        console.log("Barcode scanner clicked");
    });
});

// Special handling for biaya_ongkir field
document.addEventListener("DOMContentLoaded", function() {
    const biayaOngkirInput = document.getElementById("biaya_ongkir");
    
    if (biayaOngkirInput) {
        // Store original value when field gets focus
        let originalValue = biayaOngkirInput.value;
        
        biayaOngkirInput.addEventListener("focus", function() {
            originalValue = this.value;
            this.select();
            console.log("Biaya ongkir focused, value:", originalValue);
        });
        
        biayaOngkirInput.addEventListener("blur", function() {
            // Only update if value has actually changed
            if (this.value !== originalValue) {
                console.log("Biaya ongkir changed from", originalValue, "to", this.value);
                originalValue = this.value;
            }
        });
        
        // Prevent value reset on click
        biayaOngkirInput.addEventListener("click", function(e) {
            e.preventDefault();
            this.focus();
            this.select();
            console.log("Biaya ongkir clicked, current value:", this.value);
        });
        
        // Handle input changes properly
        biayaOngkirInput.addEventListener("input", function() {
            console.log("Biaya ongkir input changed to:", this.value);
            recalcTotal();
        });
        
        // Handle keydown to prevent unwanted behavior
        biayaOngkirInput.addEventListener("keydown", function(e) {
            // Allow: numbers, decimal point, backspace, delete, tab, enter, arrows
            const allowedKeys = ["0","1","2","3","4","5","6","7","8","9",".","Backspace","Delete","Tab","Enter","ArrowLeft","ArrowRight","ArrowUp","ArrowDown","Home","End"];
            
            if (!allowedKeys.includes(e.key) && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
            }
        });
    }
});

// Enhanced focus management system
document.addEventListener("DOMContentLoaded", function() {
    const formFields = document.querySelectorAll("input[type=number], input[type=text], select, textarea");
    const barcodeInput = document.getElementById("barcode-scanner");
    
    // Exclude barcode scanner from form fields list
    const nonBarcodeFields = Array.from(formFields).filter(field => field.id !== "barcode-scanner");
    
    // Store current focus state
    let currentFocusedField = null;
    let isUserTyping = false;
    let typingTimer = null;
    
    // Enhanced field focus handling
    nonBarcodeFields.forEach(function(field) {
        // Store original value when field gets focus
        field.addEventListener("focus", function(e) {
            currentFocusedField = this;
            isUserTyping = true;
            
            // Clear any existing typing timer
            if (typingTimer) {
                clearTimeout(typingTimer);
            }
            
            // Select all text for easy editing
            if (this.type === "number" || this.type === "text") {
                this.select();
            }
            
            console.log("Field focused:", this.name || this.id, "value:", this.value);
            
            // Remove focus from barcode scanner
            if (barcodeInput && barcodeInput !== this) {
                barcodeInput.blur();
            }
        });
        
        // Detect when user stops typing
        field.addEventListener("input", function(e) {
            isUserTyping = true;
            
            // Clear existing timer
            if (typingTimer) {
                clearTimeout(typingTimer);
            }
            
            // Set timer to detect when user stops typing
            typingTimer = setTimeout(function() {
                isUserTyping = false;
            }, 2000); // 2 seconds after last input
        });
        
        // Handle field blur
        field.addEventListener("blur", function(e) {
            if (currentFocusedField === this) {
                currentFocusedField = null;
                isUserTyping = false;
                
                // Clear typing timer
                if (typingTimer) {
                    clearTimeout(typingTimer);
                    typingTimer = null;
                }
                
                console.log("Field blurred:", this.name || this.id, "final value:", this.value);
            }
        });
        
        // Enhanced click handling
        field.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Set focus to this field
            this.focus();
            this.select();
            
            // Remove focus from barcode scanner
            if (barcodeInput) {
                barcodeInput.blur();
            }
            
            console.log("Field clicked:", this.name || this.id, "value:", this.value);
        });
        
        // Prevent accidental tab navigation
        field.addEventListener("keydown", function(e) {
            // Allow tab key but prevent focus loss
            if (e.key === "Tab") {
                e.preventDefault();
                
                // Move to next field manually
                const fields = Array.from(formFields);
                const currentIndex = fields.indexOf(this);
                const nextIndex = (currentIndex + 1) % fields.length;
                fields[nextIndex].focus();
                fields[nextIndex].select();
            }
        });
    });
    
    // Enhanced barcode scanner integration
    if (barcodeInput) {
        barcodeInput.addEventListener("focus", function() {
            // Only allow barcode scanner focus if user is not typing elsewhere
            if (!isUserTyping && !currentFocusedField) {
                console.log("Barcode scanner focused");
            } else {
                // If user is typing elsewhere, don't allow barcode scanner to steal focus
                this.blur();
                if (currentFocusedField) {
                    currentFocusedField.focus();
                }
            }
        });
    }
    
    // Prevent accidental form submission
    document.getElementById("form-penjualan").addEventListener("submit", function(e) {
        // Check if any field is still being typed in
        if (isUserTyping) {
            e.preventDefault();
            alert("Harap selesaikan input terlebih dahulu sebelum menyimpan.");
            return false;
        }
    });
});

// Enhanced input validation
function validateAndFormatField(field) {
    let value = field.value;
    
    // Remove any non-numeric characters except decimal point
    if (field.type === "number") {
        value = value.replace(/[^0-9.]/g, "");
        
        // Ensure only one decimal point
        const decimalPoints = value.match(/\./g);
        if (decimalPoints && decimalPoints.length > 1) {
            value = value.replace(/\.(?=.*\.)/g, "");
        }
        
        // Limit to 2 decimal places
        const parts = value.split(".");
        if (parts[1] && parts[1].length > 2) {
            value = parts[0] + "." + parts[1].substring(0, 2);
        }
        
        // Update field value
        field.value = value;
    }
}

// Apply validation to all numeric fields
document.addEventListener("DOMContentLoaded", function() {
    const numericFields = document.querySelectorAll("input[type=number]");
    numericFields.forEach(function(field) {
        field.addEventListener("input", function() {
            validateAndFormatField(this);
        });
        
        field.addEventListener("blur", function() {
            validateAndFormatField(this);
        });
    });
});

// Auto-save functionality to prevent data loss
let autoSaveTimer = null;
let lastSaveTime = 0;

function autoSaveForm() {
    const currentTime = Date.now();
    
    // Only auto-save if 5 seconds have passed since last save
    if (currentTime - lastSaveTime < 5000) {
        return;
    }
    
    const formData = new FormData(document.getElementById("form-penjualan"));
    const data = {};
    
    // Convert FormData to simple object
    for (let [key, value] of formData.entries()) {
        if (Array.isArray(data[key])) {
            data[key] = data[key] || [];
            data[key].push(value);
        } else {
            data[key] = value;
        }
    }
    
    // Save to localStorage
    localStorage.setItem("penjualan_draft", JSON.stringify(data));
    lastSaveTime = currentTime;
    
    console.log("Form auto-saved");
}

// Auto-save on input changes
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("form-penjualan");
    const formFields = form.querySelectorAll("input, select, textarea");
    
    nonBarcodeFields.forEach(function(field) {
        field.addEventListener("input", function() {
            // Clear existing timer
            if (autoSaveTimer) {
                clearTimeout(autoSaveTimer);
            }
            
            // Set new timer to auto-save after 20 seconds of inactivity
            autoSaveTimer = setTimeout(autoSaveForm, 20000);
        });
    });
    
    // Load draft on page load
    const draftData = localStorage.getItem("penjualan_draft");
    if (draftData) {
        try {
            const data = JSON.parse(draftData);
            console.log("Loaded draft data:", data);
            
            // Restore form fields (implementation depends on your form structure)
            // This is a placeholder - you would need to implement field restoration
        } catch (e) {
            console.error("Error loading draft data:", e);
        }
    }
});

// Ensure barcode scanner is always ready
document.addEventListener("DOMContentLoaded", function() {
    const barcodeInput = document.getElementById("barcode-scanner");
    
    // Make sure barcode scanner gets focus on page load
    setTimeout(function() {
        barcodeInput.focus();
        console.log("Barcode scanner focused on page load");
    }, 500);
    
    // Add click handler to barcode scanner
    barcodeInput.addEventListener("click", function() {
        this.focus();
        console.log("Barcode scanner clicked and focused");
    });
    
    // Add focus handler to barcode scanner
    barcodeInput.addEventListener("focus", function() {
        console.log("Barcode scanner focused");
    });
    
    // Add blur handler to barcode scanner
    barcodeInput.addEventListener("blur", function() {
        console.log("Barcode scanner blurred");
        // Refocus after a short delay unless user is typing elsewhere
        setTimeout(function() {
            const activeElement = document.activeElement;
            if (activeElement === document.body || 
                activeElement === document.documentElement ||
                !activeElement.classList.contains('form-control') ||
                activeElement.id === 'barcode-scanner') {
                barcodeInput.focus();
            }
        }, 100);
    });
});
</script>
@endsection
