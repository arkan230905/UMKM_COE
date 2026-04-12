// Barcode Scanner for Sales Transaction
console.log('Loading barcode scanner...');

// Global variables
let isProcessing = false;
let searchTimeout = null;
const SEARCH_DELAY = 150;

// Utility functions
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

function recalcRow(tr) {
    const q = Math.round(parseFloat(tr.querySelector('.jumlah').value) || 0);
    tr.querySelector('.jumlah').value = q;
    const p = parseCurrency(tr.querySelector('.harga').value) || 0;
    const dPct = Math.min(Math.max(parseFloat(tr.querySelector('.diskon').value) || 0, 0), 100);
    const sub = q * p;
    const dNom = sub * (dPct/100.0);
    const line = Math.max(sub - dNom, 0);
    tr.querySelector('.subtotal').value = formatCurrency(line);
}

function recalcTotal() {
    const table = document.getElementById('detailTableJual');
    if (!table) return;
    
    let sum = 0;
    table.querySelectorAll('tbody tr').forEach(tr => {
        const val = (tr.querySelector('.subtotal').value || 'Rp 0').replace(/[^\d]/g,'');
        sum += parseFloat(val) || 0;
    });
    
    const subtotalProdukInput = document.querySelector('input[name="subtotal_produk"]');
    if (subtotalProdukInput) {
        subtotalProdukInput.value = formatCurrency(sum);
    }
    
    const biayaOngkir = parseFloat(document.getElementById('biaya_ongkir')?.value || 0);
    const biayaService = parseFloat(document.getElementById('biaya_service')?.value || 0);
    const ppnPersen = parseFloat(document.getElementById('ppn_persen')?.value || 0);
    
    const ppnBase = sum + biayaOngkir + biayaService;
    const totalPPN = ppnBase * (ppnPersen / 100);
    
    const totalPPNInput = document.getElementById('total_ppn');
    if (totalPPNInput) {
        totalPPNInput.value = formatCurrency(totalPPN);
    }
    
    const finalTotal = sum + biayaOngkir + biayaService + totalPPN;
    
    const totalInput = document.getElementById('total_final');
    if (totalInput) {
        totalInput.value = formatCurrency(finalTotal);
    }
}

function findExistingProductRow(productId) {
    const table = document.getElementById('detailTableJual');
    if (!table) return null;
    
    const rows = table.querySelectorAll('tbody tr');
    for (let row of rows) {
        const select = row.querySelector('.produk-select');
        if (select && select.value && select.value == productId) {
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

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Real-time search functionality
function performRealTimeSearch(query) {
    const searchResults = document.getElementById('search-results');
    const searchResultsBody = document.getElementById('search-results-body');
    const searchCount = document.getElementById('search-count');
    
    if (!searchResults || !searchResultsBody) return;
    
    if (!query || query.length < 1) {
        searchResults.style.display = 'none';
        return;
    }
    
    const results = searchableProducts.filter(product => {
        if (product.barcode && product.barcode.startsWith(query)) {
            return true;
        }
        if (product.searchText.includes(query.toLowerCase())) {
            return true;
        }
        return false;
    })
    .sort((a, b) => {
        const aStartsWithBarcode = a.barcode && a.barcode.startsWith(query);
        const bStartsWithBarcode = b.barcode && b.barcode.startsWith(query);
        
        if (aStartsWithBarcode && !bStartsWithBarcode) return -1;
        if (!aStartsWithBarcode && bStartsWithBarcode) return 1;
        
        return a.nama.localeCompare(b.nama);
    })
    .slice(0, 10);
    
    if (results.length > 0) {
        searchCount.textContent = results.length;
        
        let html = '';
        results.forEach(product => {
            const stockBadge = product.stok > 0 ? 
                `<span class="badge bg-success">${product.stok}</span>` : 
                `<span class="badge bg-danger">Habis</span>`;
            
            let barcodeDisplay = '';
            if (product.barcode) {
                if (product.barcode.startsWith(query)) {
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
                     onclick="selectProductFromSearch(${product.id}, '${product.nama.replace(/'/g, "\\'")}', ${product.harga}, ${product.stok})">
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark">${product.nama}</div>
                        <small class="text-muted">${barcodeDisplay} * Rp ${product.harga.toLocaleString('id-ID')}</small>
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

function selectProductFromSearch(productId, productName, price, stock) {
    const product = { id: productId, nama: productName, harga: price, stok: stock };
    
    try {
        addProductByBarcode(product);
        showNotification('Produk ditambahkan: ' + productName, 'success');
        
        const barcodeInput = document.getElementById('barcode-scanner');
        if (barcodeInput) barcodeInput.value = '';
        
        const searchResults = document.getElementById('search-results');
        if (searchResults) searchResults.style.display = 'none';
        
        const scanIndicator = document.getElementById('scan-indicator');
        if (scanIndicator) {
            scanIndicator.textContent = 'Siap Scan';
            scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
        }
        
        setTimeout(() => {
            const barcodeInput = document.getElementById('barcode-scanner');
            if (barcodeInput) barcodeInput.focus();
        }, 100);
        
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

function processAutomaticBarcode(barcode) {
    if (isProcessing) return;
    
    isProcessing = true;
    console.log('Processing barcode:', barcode);
    
    const barcodeInput = document.getElementById('barcode-scanner');
    const scanIndicator = document.getElementById('scan-indicator');
    const searchResults = document.getElementById('search-results');
    
    if (searchResults) searchResults.style.display = 'none';
    if (barcodeInput) barcodeInput.value = '';
    
    try {
        const product = productData[barcode];
        
        if (product) {
            if (product.stok <= 0) {
                throw new Error('Produk ' + product.nama + ' stok habis!');
            }
            
            addProductByBarcode(product);
            
            if (scanIndicator) {
                scanIndicator.textContent = 'Produk ditambahkan';
                scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
            }
            
            showNotification('Produk ditambahkan: ' + product.nama, 'success');
            
        } else {
            if (scanIndicator) {
                scanIndicator.textContent = 'Produk tidak ditemukan';
                scanIndicator.parentElement.className = 'input-group-text bg-danger text-white';
            }
            
            showNotification('Produk dengan barcode ' + barcode + ' tidak ditemukan', 'error');
        }
    } catch (error) {
        if (scanIndicator) {
            scanIndicator.textContent = 'Error: ' + error.message;
            scanIndicator.parentElement.className = 'input-group-text bg-danger text-white';
        }
        showNotification(error.message, 'error');
    }
    
    setTimeout(() => {
        if (scanIndicator) {
            scanIndicator.textContent = 'Siap Scan';
            scanIndicator.parentElement.className = 'input-group-text bg-success text-white';
        }
        isProcessing = false;
        const barcodeInput = document.getElementById('barcode-scanner');
        if (barcodeInput) barcodeInput.focus();
    }, 2000);
}

function addProductByBarcode(product) {
    console.log('Adding product to cart:', product);
    
    const table = document.getElementById('detailTableJual');
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    
    const existingRow = findExistingProductRow(product.id);
    
    if (existingRow) {
        const qtyInput = existingRow.querySelector('.jumlah');
        const currentQty = parseFloat(qtyInput.value) || 0;
        const newQty = currentQty + 1;
        
        if (newQty > product.stok) {
            throw new Error('Stok tidak cukup! Stok tersedia: ' + product.stok);
        }
        
        qtyInput.value = newQty;
        recalcRow(existingRow);
        recalcTotal();
        
        highlightRow(existingRow);
    } else {
        let targetRow = null;
        const rows = tbody.querySelectorAll('tr');
        
        for (let row of rows) {
            const select = row.querySelector('.produk-select');
            if (!select || !select.value) {
                targetRow = row;
                break;
            }
        }
        
        if (!targetRow) {
            targetRow = createNewRow();
            tbody.appendChild(targetRow);
        }
        
        const select = targetRow.querySelector('.produk-select');
        const qtyInput = targetRow.querySelector('.jumlah');
        const hargaInput = targetRow.querySelector('.harga');
        const diskonInput = targetRow.querySelector('.diskon');
        
        if (select) select.value = product.id;
        if (qtyInput) {
            qtyInput.value = 1;
            qtyInput.setAttribute('data-max-stok', product.stok);
        }
        if (hargaInput) hargaInput.value = formatCurrency(product.harga);
        if (diskonInput) diskonInput.value = 0;
        
        const stokInfo = targetRow.querySelector('.stok-info');
        if (stokInfo) {
            stokInfo.textContent = `Stok tersedia: ${product.stok}`;
            stokInfo.style.color = product.stok > 0 ? '#28a745' : '#dc3545';
        }
        
        recalcRow(targetRow);
        recalcTotal();
        
        highlightRow(targetRow);
    }
}

function createNewRow() {
    const table = document.getElementById('detailTableJual');
    if (!table) return null;
    
    const tbody = table.querySelector('tbody');
    if (!tbody) return null;
    
    const firstRow = table.querySelector('tbody tr');
    
    if (firstRow) {
        const clone = firstRow.cloneNode(true);
        
        clone.querySelectorAll('input').forEach(inp => {
            if (inp.classList.contains('jumlah')) inp.value = 1;
            else if (inp.classList.contains('harga')) inp.value = formatCurrency(0);
            else if (inp.classList.contains('diskon')) inp.value = 0;
            else if (inp.classList.contains('subtotal')) inp.value = formatCurrency(0);
        });
        
        clone.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
        
        const stockInfo = clone.querySelector('.stok-info');
        if (stockInfo) {
            stockInfo.textContent = '';
        }
        
        return clone;
    }
    
    return null;
}

// Initialize barcode scanner
function initializeBarcodeScanner() {
    console.log('Initializing barcode scanner...');
    
    const barcodeInput = document.getElementById('barcode-scanner');
    if (!barcodeInput) {
        console.error('Barcode input not found');
        return;
    }
    
    // Set initial focus
    barcodeInput.focus();
    
    // Handle barcode input
    barcodeInput.addEventListener('input', function(e) {
        const value = e.target.value.trim();
        
        if (!value) {
            const searchResults = document.getElementById('search-results');
            if (searchResults) searchResults.style.display = 'none';
            return;
        }
        
        const currentTime = Date.now();
        const timeDiff = barcodeInput.lastInputTime ? (currentTime - barcodeInput.lastInputTime) : 1000;
        
        if (timeDiff < 50) {
            // Rapid input - likely scanner
            const searchResults = document.getElementById('search-results');
            if (searchResults) searchResults.style.display = 'none';
            
            setTimeout(() => {
                if (value.length >= 8) {
                    processAutomaticBarcode(value);
                }
            }, 100);
        } else {
            // Manual typing - show search results
            if (/^\d+$/.test(value) && value.length < 8 && !isProcessing) {
                if (searchTimeout) clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (!isProcessing) {
                        performRealTimeSearch(value);
                    }
                }, SEARCH_DELAY);
            }
        }
        
        barcodeInput.lastInputTime = currentTime;
    });
    
    // Handle Enter key
    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const currentValue = barcodeInput.value.trim();
            if (currentValue && currentValue.length >= 1) {
                const searchResults = document.getElementById('search-results');
                if (searchResults && searchResults.style.display !== 'none') {
                    const firstResult = searchResults.querySelector('.search-result-item');
                    if (firstResult) {
                        firstResult.click();
                        return;
                    }
                }
                processAutomaticBarcode(currentValue);
            }
        }
    });
    
    // Handle add row button
    const addRowBtn = document.getElementById('addRowJual');
    if (addRowBtn) {
        addRowBtn.addEventListener('click', function() {
            const tbody = document.querySelector('#detailTableJual tbody');
            if (tbody) {
                const newRow = createNewRow();
                if (newRow) {
                    tbody.appendChild(newRow);
                }
            }
        });
    }
    
    // Initialize first row
    const firstRow = document.querySelector('#detailTableJual tbody tr:first-child');
    if (firstRow) {
        const select = firstRow.querySelector('.produk-select');
        const qtyInput = firstRow.querySelector('.jumlah');
        const diskonInput = firstRow.querySelector('.diskon');
        
        if (select) {
            select.addEventListener('change', function() {
                const opt = select.options[select.selectedIndex];
                const price = parseFloat(opt?.getAttribute('data-price') || '0') || 0;
                const stok = parseFloat(opt?.getAttribute('data-stok') || '0') || 0;
                
                const hargaInput = firstRow.querySelector('.harga');
                if (hargaInput) hargaInput.value = formatCurrency(price);
                
                const stokInfo = firstRow.querySelector('.stok-info');
                if (stokInfo && opt.value) {
                    stokInfo.textContent = `Stok tersedia: ${stok.toLocaleString()}`;
                    stokInfo.style.color = stok > 0 ? '#28a745' : '#dc3545';
                }
                
                if (qtyInput) qtyInput.setAttribute('data-max-stok', stok);
                
                recalcRow(firstRow);
                recalcTotal();
            });
        }
        
        if (qtyInput) {
            qtyInput.addEventListener('input', function() {
                recalcRow(firstRow);
                recalcTotal();
            });
        }
        
        if (diskonInput) {
            diskonInput.addEventListener('input', function() {
                recalcRow(firstRow);
                recalcTotal();
            });
        }
    }
    
    // Initialize cost fields
    const biayaOngkir = document.getElementById('biaya_ongkir');
    const biayaService = document.getElementById('biaya_service');
    const ppnPersen = document.getElementById('ppn_persen');
    
    if (biayaOngkir) biayaOngkir.addEventListener('input', recalcTotal);
    if (biayaService) biayaService.addEventListener('input', recalcTotal);
    if (ppnPersen) ppnPersen.addEventListener('input', recalcTotal);
    
    console.log('Barcode scanner initialized successfully');
}

// Wait for DOM to be ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeBarcodeScanner);
} else {
    initializeBarcodeScanner();
}
