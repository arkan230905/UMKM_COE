@extends('layouts.app')
@section('content')

<style>
/* Enhanced search result styling */
.search-result-item {
    transition: all 0.2s ease;
    border-radius: 4px;
    padding: 8px !important;
    margin: 2px 0;
}

.search-result-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-result-item.selected {
    background-color: #e3f2fd !important;
    border-left: 3px solid #2196f3;
}

/* Barcode highlight styling */
mark.bg-warning {
    background-color: #fff3cd !important;
    color: #856404 !important;
    font-weight: bold;
    padding: 1px 2px;
    border-radius: 2px;
}

/* Search results container */
#search-results {
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Barcode scanner input focus */
#barcode-scanner:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>

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
                <label class="form-label">Waktu</label>
                <input type="time" name="waktu" class="form-control" value="{{ now()->format('H:i') }}" required>
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
                                   placeholder="Ketik atau scan barcode..." 
                                   autocomplete="off" autofocus>
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
                            Pencarian berdasarkan awalan barcode - ketik angka untuk mencari produk yang barcodenya diawali dengan angka tersebut
                        </small>
                        
                        <!-- Real-time search results -->
                        <div id="search-results" class="mt-2" style="display: none;">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white py-1">
                                    <small><i class="fas fa-search me-1"></i>Hasil Pencarian (<span id="search-count">0</span> produk)</small>
                                </div>
                                <div class="card-body p-2" id="search-results-body" style="max-height: 200px; overflow-y: auto;">
                                    <!-- Results will be populated here -->
                                </div>
                            </div>
                        </div>
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
        harga: {{ round($p->harga_jual ?? 0) }},
        stok: {{ $p->stok ?? 0 }},
        barcode: '{{ $p->barcode ?? '' }}'
    },
    @endforeach
};

// Create searchable product array for real-time search
const searchableProducts = [
    @foreach($produks as $p)
    {
        id: {{ $p->id }},
        nama: '{{ addslashes($p->nama_produk ?? $p->nama) }}',
        harga: {{ round($p->harga_jual ?? 0) }},
        stok: {{ $p->stok ?? 0 }},
        barcode: '{{ $p->barcode ?? '' }}',
        searchText: '{{ strtolower(addslashes($p->nama_produk ?? $p->nama)) }} {{ $p->barcode ?? '' }}'.toLowerCase()
    },
    @endforeach
];

// Debug: Log productData to console
console.log('Product Data:', productData);
console.log('Searchable Products:', searchableProducts);

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
    
    console.log('🔍 Looking for existing product ID:', productId);
    
    for (let row of rows) {
        const select = row.querySelector('.produk-select');
        if (select && select.value) {
            console.log('📋 Found row with product ID:', select.value);
            if (select.value == productId) {
                console.log('✅ Found existing row for product:', productId);
                return row;
            }
        }
    }
    console.log('❌ No existing row found for product:', productId);
    return null;
}

// Automatic Barcode Scanner System
let barcodeBuffer = '';
let barcodeTimeout = null;
let isProcessing = false;
let searchTimeout = null;
const BARCODE_TIMEOUT = 50; // Reduced to 50ms for faster response
const MIN_BARCODE_LENGTH = 1; // Reduced to 1 for immediate search
const SEARCH_DELAY = 150; // Delay for real-time search to avoid too many requests

// Real-time product search functionality
function performRealTimeSearch(query) {
    const searchResults = document.getElementById('search-results');
    const searchResultsBody = document.getElementById('search-results-body');
    const searchCount = document.getElementById('search-count');
    
    if (!query || query.length < 1) {
        searchResults.style.display = 'none';
        return;
    }
    
    console.log('🔍 Searching for products with barcode starting with:', query);
    
    // Search in products with priority for barcode prefix match
    const results = searchableProducts.filter(product => {
        // Priority 1: Barcode starts with query (exact prefix match)
        if (product.barcode && product.barcode.startsWith(query)) {
            console.log('✅ Found barcode prefix match:', product.barcode, 'for product:', product.nama);
            return true;
        }
        // Priority 2: Product name contains query (fallback for name search)
        if (product.searchText.includes(query.toLowerCase())) {
            console.log('📝 Found name match:', product.nama, 'for query:', query);
            return true;
        }
        return false;
    })
    .sort((a, b) => {
        // Sort by priority: barcode prefix matches first
        const aStartsWithBarcode = a.barcode && a.barcode.startsWith(query);
        const bStartsWithBarcode = b.barcode && b.barcode.startsWith(query);
        
        if (aStartsWithBarcode && !bStartsWithBarcode) return -1;
        if (!aStartsWithBarcode && bStartsWithBarcode) return 1;
        
        // If both or neither start with barcode, sort by name
        return a.nama.localeCompare(b.nama);
    })
    .slice(0, 10); // Limit to 10 results for performance
    
    console.log('📊 Search results count:', results.length);
    
    if (results.length > 0) {
        searchCount.textContent = results.length;
        
        let html = '';
        results.forEach(product => {
            const stockBadge = product.stok > 0 ? 
                `<span class="badge bg-success">${product.stok}</span>` : 
                `<span class="badge bg-danger">Habis</span>`;
            
            // Highlight matching part in barcode for prefix matches
            let barcodeDisplay = '';
            if (product.barcode) {
                if (product.barcode.startsWith(query)) {
                    // Highlight the matching prefix
                    const matchedPart = product.barcode.substring(0, query.length);
                    const remainingPart = product.barcode.substring(query.length);
                    barcodeDisplay = `<code class="text-primary"><mark class="bg-warning text-dark">${matchedPart}</mark>${remainingPart}</code>`;
                } else {
                    barcodeDisplay = `<code class="text-primary">${product.barcode}</code>`;
                }
            } else {
                barcodeDisplay = '<small class="text-muted">No barcode</small>';
            }
            
            html += `
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom search-result-item" 
                     style="cursor: pointer;" 
                     onclick="selectProductFromSearch(${product.id}, '${product.nama.replace(/'/g, "\\'")}', ${product.harga}, ${product.stok})"
                     onmouseover="this.style.backgroundColor='#f8f9fa'" 
                     onmouseout="this.style.backgroundColor=''">
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark">${product.nama}</div>
                        <small class="text-muted">${barcodeDisplay} • Rp ${product.harga.toLocaleString('id-ID')}</small>
                    </div>
                    <div class="text-end">
                        ${stockBadge}
                        <button type="button" class="btn btn-sm btn-primary ms-2" onclick="event.stopPropagation(); selectProductFromSearch(${product.id}, '${product.nama.replace(/'/g, "\\'")}', ${product.harga}, ${product.stok})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        
        searchResultsBody.innerHTML = html;
        searchResults.style.display = 'block';
    } else {
        searchCount.textContent = '0';
        searchResultsBody.innerHTML = `
            <div class="text-center text-muted py-2">
                <i class="fas fa-search me-1"></i>
                Tidak ada produk dengan barcode yang diawali "${query}"
            </div>
        `;
        searchResults.style.display = 'block';
    }
}

// Select product from search results
function selectProductFromSearch(productId, productName, price, stock) {
    const product = {
        id: productId,
        nama: productName,
        harga: price,
        stok: stock
    };
    
    try {
        addProductByBarcode(product);
        showNotification('✓ ' + productName + ' ditambahkan', 'success');
        
        // Clear search
        document.getElementById('barcode-scanner').value = '';
        document.getElementById('search-results').style.display = 'none';
        
        // Reset scan indicator to "Siap Scan" after product selection
        const scanIndicator = document.getElementById('scan-indicator');
        if (scanIndicator) {
            scanIndicator.textContent = 'Siap Scan';
            scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
        }
        
        // Focus back to barcode input
        setTimeout(() => {
            document.getElementById('barcode-scanner').focus();
        }, 100);
        
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Enhanced barcode input handler with real-time search
function handleBarcodeInputEnhanced(value) {
    // Clear existing search timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // If value is empty, hide search results
    if (!value) {
        document.getElementById('search-results').style.display = 'none';
        return;
    }
    
    // Don't show search results during barcode scanning (when processing is active)
    if (isProcessing) {
        document.getElementById('search-results').style.display = 'none';
        return;
    }
    
    // Only show search results for numeric input (barcode search) and manual typing
    if (/^\d+$/.test(value)) {
        // Set timeout for real-time search for numeric input
        searchTimeout = setTimeout(() => {
            // Double check we're not processing a barcode scan
            if (!isProcessing) {
                performRealTimeSearch(value);
            }
        }, SEARCH_DELAY);
    } else {
        // Hide search results for non-numeric input
        document.getElementById('search-results').style.display = 'none';
    }
}

// Safety mechanism to reset processing state if stuck
function resetProcessingState() {
    const scanIndicator = document.getElementById('scan-indicator');
    if (isProcessing && scanIndicator && scanIndicator.textContent === 'Memproses...') {
        console.log('⚠️ Resetting stuck processing state');
        isProcessing = false;
        scanIndicator.textContent = 'Siap Scan';
        scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
    }
}

// Check for stuck processing every 5 seconds
setInterval(resetProcessingState, 5000);

// Auto-focus system
function maintainFocus() {
    const barcodeInput = document.getElementById('barcode-scanner');
    if (document.activeElement !== barcodeInput) {
        barcodeInput.focus();
    }
}

// Automatic barcode detection
function handleBarcodeInput(char) {
    // Hide search results immediately when rapid input is detected (barcode scanning)
    document.getElementById('search-results').style.display = 'none';
    
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

// Process barcode automatically - Enhanced version
function processAutomaticBarcode(barcode) {
    if (isProcessing) return;
    
    isProcessing = true;
    console.log('🔍 Auto-processing barcode:', barcode);
    
    const barcodeInput = document.getElementById('barcode-scanner');
    const scanIndicator = document.getElementById('scan-indicator');
    const searchResults = document.getElementById('search-results');
    
    // Hide search results when processing exact barcode
    searchResults.style.display = 'none';
    
    // Clear input immediately for silent processing
    barcodeInput.value = '';
    
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
            
            // Product found - add to table silently
            addProductByBarcode(product);
            
            // Success feedback
            scanIndicator.textContent = '✓ ' + product.nama.substring(0, 15) + (product.nama.length > 15 ? '...' : '');
            scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
            
            // Play success sound
            playBeep(true);
            
            // Show success notification
            showNotification('Produk ditambahkan: ' + product.nama, 'success');
            
        } else {
            console.log('❌ Product not found for barcode:', barcode);
            
            // Product not found - show error without search results
            scanIndicator.textContent = 'Produk tidak ditemukan';
            scanIndicator.parentElement.className = 'input-group-text bg-danger text-white';
            
            // Show error notification
            showNotification('Produk dengan barcode ' + barcode + ' tidak ditemukan', 'error');
            
            // Play error sound
            playBeep(false);
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
    
    // Reset status after 2 seconds
    setTimeout(() => {
        scanIndicator.textContent = 'Siap Scan';
        scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
        isProcessing = false;
        
        // Ensure focus is maintained
        maintainFocus();
    }, 2000);
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

// Manual reset function - Enhanced
function resetScannerState() {
    console.log('🔄 Manually resetting scanner state');
    isProcessing = false;
    barcodeBuffer = '';
    
    if (barcodeTimeout) {
        clearTimeout(barcodeTimeout);
        barcodeTimeout = null;
    }
    
    if (searchTimeout) {
        clearTimeout(searchTimeout);
        searchTimeout = null;
    }
    
    const scanIndicator = document.getElementById('scan-indicator');
    const barcodeInput = document.getElementById('barcode-scanner');
    const searchResults = document.getElementById('search-results');
    
    if (scanIndicator) {
        scanIndicator.textContent = 'Siap Scan';
        scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
    }
    
    if (barcodeInput) {
        barcodeInput.value = '';
        barcodeInput.focus();
    }
    
    if (searchResults) {
        searchResults.style.display = 'none';
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
    console.log('🛒 Adding product to cart:', product);
    
    const table = document.getElementById('detailTableJual');
    const tbody = table.querySelector('tbody');
    
    // Check if product already exists in table
    const existingRow = findExistingProductRow(product.id);
    
    if (existingRow) {
        console.log('📈 Incrementing existing product quantity');
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
        console.log('➕ Adding new product to table');
        // Find first empty row or create new one
        let targetRow = null;
        const rows = tbody.querySelectorAll('tr');
        
        // Look for empty row (no product selected)
        for (let row of rows) {
            const select = row.querySelector('.produk-select');
            if (!select || !select.value) {
                console.log('📝 Found empty row to use');
                targetRow = row;
                break;
            }
        }
        
        // If no empty row found, create new one
        if (!targetRow) {
            console.log('🆕 Creating new row');
            targetRow = createNewRow();
            tbody.appendChild(targetRow);
        }
        
        // Fill the row with product data
        const select = targetRow.querySelector('.produk-select');
        const qtyInput = targetRow.querySelector('.jumlah');
        const hargaInput = targetRow.querySelector('.harga');
        const diskonInput = targetRow.querySelector('.diskon');
        
        console.log('📋 Setting product data in row:', {
            productId: product.id,
            name: product.nama,
            price: product.harga
        });
        
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
    
    // Maintain focus on barcode input at all times
    function ensureBarcodeInputFocus() {
        // Don't steal focus if user is actively using dropdown or other form elements
        const activeElement = document.activeElement;
        
        if (activeElement && (
            activeElement.tagName === 'SELECT' ||
            activeElement.tagName === 'INPUT' ||
            activeElement.tagName === 'TEXTAREA' ||
            activeElement.classList.contains('form-control') ||
            activeElement.classList.contains('form-select') ||
            activeElement.hasAttribute('data-dropdown-focused')
        )) {
            return; // Don't steal focus
        }
        
        // Don't steal focus if any dropdown is currently focused
        if (document.querySelector('select[data-dropdown-focused="true"]')) {
            return;
        }
        
        // Don't steal focus if modal is open
        if (document.querySelector('.modal.show')) {
            return;
        }
        
        // Only focus barcode input if no other form element is focused
        if (document.activeElement !== barcodeInput) {
            barcodeInput.focus();
        }
    }
    
    // Set initial focus
    barcodeInput.focus();
    
    // Maintain focus every 1000ms (reduced frequency to be less aggressive)
    setInterval(ensureBarcodeInputFocus, 1000);
    
    // Enhanced keyboard handling for better UX
    document.addEventListener('keydown', function(e) {
        // Skip if user is typing in other inputs (except barcode input)
        if (e.target.tagName === 'INPUT' && e.target.id !== 'barcode-scanner') {
            return;
        }
        
        // Skip if user is interacting with select dropdown
        if (e.target.tagName === 'SELECT') {
            return;
        }
        
        // Skip if modal is open
        if (document.querySelector('.modal.show')) {
            return;
        }
        
        // Skip if dropdown is open
        if (document.querySelector('select:focus')) {
            return;
        }
        
        // Skip special keys
        if (e.ctrlKey || e.altKey || e.metaKey) {
            return;
        }
        
        // Handle Escape key to clear search results
        if (e.key === 'Escape') {
            e.preventDefault();
            document.getElementById('search-results').style.display = 'none';
            barcodeInput.value = '';
            barcodeInput.focus();
            return;
        }
        
        // Handle Arrow keys for search result navigation
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            const searchResults = document.getElementById('search-results');
            if (searchResults.style.display !== 'none') {
                e.preventDefault();
                navigateSearchResults(e.key === 'ArrowDown' ? 1 : -1);
                return;
            }
        }
        
        // Skip navigation keys when dropdown is focused
        if (['ArrowLeft', 'ArrowRight', 'Tab'].includes(e.key)) {
            return;
        }
        
        // Handle Enter key (barcode scanners usually send Enter at the end)
        if (e.key === 'Enter') {
            e.preventDefault();
            const currentValue = barcodeInput.value.trim();
            if (currentValue && currentValue.length >= MIN_BARCODE_LENGTH) {
                // Check if search results are visible and select first result
                const searchResults = document.getElementById('search-results');
                if (searchResults.style.display !== 'none') {
                    const firstResult = searchResults.querySelector('.search-result-item');
                    if (firstResult) {
                        firstResult.click();
                        return;
                    }
                }
                processAutomaticBarcode(currentValue);
            }
            return;
        }
        
        // Handle printable characters
        if (e.key.length === 1) {
            // Don't interfere if user is typing in dropdown or other form elements
            if (e.target.tagName === 'SELECT' || e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return;
            }
            
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

// Navigate search results with arrow keys
function navigateSearchResults(direction) {
    const results = document.querySelectorAll('.search-result-item');
    if (results.length === 0) return;
    
    let currentIndex = -1;
    results.forEach((result, index) => {
        if (result.classList.contains('selected')) {
            currentIndex = index;
            result.classList.remove('selected');
            result.style.backgroundColor = '';
        }
    });
    
    currentIndex += direction;
    if (currentIndex < 0) currentIndex = results.length - 1;
    if (currentIndex >= results.length) currentIndex = 0;
    
    const selectedResult = results[currentIndex];
    selectedResult.classList.add('selected');
    selectedResult.style.backgroundColor = '#e3f2fd';
    selectedResult.scrollIntoView({ block: 'nearest' });
}
    
    // Handle direct input to barcode field - Enhanced with real-time search
    barcodeInput.addEventListener('input', function(e) {
        const value = e.target.value.trim();
        const scanIndicator = document.getElementById('scan-indicator');
        
        // If input is cleared, reset buffer and hide search
        if (!value) {
            barcodeBuffer = '';
            if (barcodeTimeout) {
                clearTimeout(barcodeTimeout);
            }
            document.getElementById('search-results').style.display = 'none';
            
            // Reset scan indicator to "Siap Scan" when input is cleared
            if (scanIndicator) {
                scanIndicator.textContent = 'Siap Scan';
                scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
            }
            return;
        }
        
        // Handle rapid input (typical of barcode scanners)
        const currentTime = Date.now();
        const timeDiff = barcodeInput.lastInputTime ? (currentTime - barcodeInput.lastInputTime) : 1000;
        
        // If input is very rapid (< 50ms between characters), it's likely a barcode scanner
        if (timeDiff < 50) {
            // Rapid input detected - likely from scanner, hide search results and process silently
            document.getElementById('search-results').style.display = 'none';
            handleBarcodeInput(value.slice(-1)); // Get last character for buffer
            barcodeInput.lastInputTime = currentTime;
            return;
        }
        
        // If input is slower, treat as manual typing and show search results
        barcodeInput.lastInputTime = currentTime;
        
        // Only show search results for numeric input (barcode search) and manual typing
        // Don't show search results for text input
        if (/^\d+$/.test(value)) {
            // Numeric input - show search results for barcode search only for manual typing
            if (value.length >= 1 && value.length < 8 && !isProcessing) {
                // Handle real-time search for numeric input (barcode search) - only for manual input
                handleBarcodeInputEnhanced(value);
            } else if (value.length >= 8) {
                // Long numeric input - try to process as complete barcode
                document.getElementById('search-results').style.display = 'none';
                setTimeout(() => {
                    if (barcodeInput.value === value) {
                        processAutomaticBarcode(value);
                    }
                }, 100);
            }
        } else {
            // Non-numeric input - hide search results completely
            document.getElementById('search-results').style.display = 'none';
        }
    });
    
    // Prevent form submission on Enter in barcode input
    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });
    
    // Re-focus when clicking anywhere on the page (except inputs/buttons)
    document.addEventListener('click', function(e) {
        // Hide search results when clicking outside
        const searchResults = document.getElementById('search-results');
        const barcodeScanner = document.getElementById('barcode-scanner');
        
        if (!e.target.closest('#search-results') && e.target !== barcodeScanner) {
            searchResults.style.display = 'none';
        }
        
        // Don't interfere with form elements
        if (e.target.matches('input, button, select, textarea, option, .btn, .form-control, .form-select, .modal *, .dropdown-menu *')) {
            return;
        }
        
        // Don't interfere if clicking on dropdown options
        if (e.target.closest('select') || e.target.closest('.dropdown-menu')) {
            return;
        }
        
        setTimeout(() => {
            // Only focus if no form element is currently focused
            const activeElement = document.activeElement;
            if (!activeElement || (!activeElement.matches('input, select, textarea') && !activeElement.classList.contains('form-control'))) {
                barcodeInput.focus();
            }
        }, 10);
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
            
            // Update options based on payment method
            if (paymentMethod === 'cash') {
                // Ambil dari kasbank yang sudah di-load dari server
                let cashOptions = '';
                @foreach($kasbank as $kb)
                    @if(stripos($kb->nama_akun, 'kas') !== false && stripos($kb->nama_akun, 'bank') === false)
                        cashOptions += `<option value="{{ $kb->kode_akun }}">{{ $kb->nama_akun }} ({{ $kb->kode_akun }})</option>`;
                    @endif
                @endforeach
                
                // Jika tidak ada kas spesifik, gunakan semua kasbank
                if (!cashOptions.trim()) {
                    @foreach($kasbank as $kb)
                        cashOptions += `<option value="{{ $kb->kode_akun }}">{{ $kb->nama_akun }} ({{ $kb->kode_akun }})</option>`;
                    @endforeach
                }
                
                sumberDana.innerHTML = cashOptions;
            } else if (paymentMethod === 'transfer') {
                // Ambil dari kasbank yang sudah di-load dari server
                let bankOptions = '';
                @foreach($kasbank as $kb)
                    @if(stripos($kb->nama_akun, 'bank') !== false)
                        bankOptions += `<option value="{{ $kb->kode_akun }}">{{ $kb->nama_akun }} ({{ $kb->kode_akun }})</option>`;
                    @endif
                @endforeach
                
                // Jika tidak ada bank spesifik, gunakan semua kasbank
                if (!bankOptions.trim()) {
                    @foreach($kasbank as $kb)
                        bankOptions += `<option value="{{ $kb->kode_akun }}">{{ $kb->nama_akun }} ({{ $kb->kode_akun }})</option>`;
                    @endforeach
                }
                
                sumberDana.innerHTML = bankOptions;
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
    
    // Add special handling for dropdown focus/blur to prevent barcode interference
    table.addEventListener('focus', (e) => {
        if (e.target && e.target.classList.contains('produk-select')) {
            // Stop barcode auto-focus when dropdown is focused
            e.target.setAttribute('data-dropdown-focused', 'true');
        }
    }, true);
    
    table.addEventListener('blur', (e) => {
        if (e.target && e.target.classList.contains('produk-select')) {
            // Remove dropdown focus flag
            e.target.removeAttribute('data-dropdown-focused');
            
            // Resume barcode focus after a short delay
            setTimeout(() => {
                if (!document.querySelector('select:focus')) {
                    barcodeInput.focus();
                }
            }, 100);
        }
    }, true);
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
