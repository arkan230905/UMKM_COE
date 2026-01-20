// Biaya Bahan Edit JavaScript - Enhanced Version
console.log('=== Biaya Bahan CRUD System - Script loaded ===');

// Global functions untuk diakses dari HTML
window.addBahanBakuRow = addBahanBakuRow;
window.addBahanPendukungRow = addBahanPendukungRow;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing CRUD system');
    
    // Initialize event listeners untuk tombol tambah
    initializeAddButtons();
    
    // Initialize existing rows
    initializeExistingRows();
    
    // Calculate initial totals
    calculateTotals();
    
    console.log('=== CRUD System Initialization Complete ===');
});

// Initialize tombol tambah bahan
function initializeAddButtons() {
    const addBahanBakuBtn = document.getElementById('addBahanBaku');
    if (addBahanBakuBtn) {
        addBahanBakuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            addBahanBakuRow();
        });
        console.log('✓ Add Bahan Baku button initialized');
    }

    const addBahanPendukungBtn = document.getElementById('addBahanPendukung');
    if (addBahanPendukungBtn) {
        addBahanPendukungBtn.addEventListener('click', function(e) {
            e.preventDefault();
            addBahanPendukungRow();
        });
        console.log('✓ Add Bahan Pendukung button initialized');
    }
}

// Add new Bahan Baku row
function addBahanBakuRow() {
    console.log('Adding new Bahan Baku row...');
    
    const newRow = document.getElementById('newBahanBakuRow');
    if (!newRow) {
        console.error('Template row newBahanBakuRow not found');
        return;
    }
    
    const tbody = newRow.parentElement;
    const clone = newRow.cloneNode(true);
    
    // Generate unique ID dan name attributes
    const timestamp = Date.now();
    clone.classList.remove('d-none');
    clone.id = 'bahanBaku_' + timestamp;
    
    // Update name attributes untuk form submission
    clone.querySelectorAll('[name^="bahan_baku[new]"]').forEach(input => {
        const fieldName = input.name.match(/\[new\]\[(\w+)\]/)[1];
        input.name = `bahan_baku[${timestamp}][${fieldName}]`;
        input.value = '';
        input.removeAttribute('selected');
    });
    
    // Reset displays
    const hargaDisplay = clone.querySelector('.harga-display');
    const subtotalDisplay = clone.querySelector('.subtotal-display');
    if (hargaDisplay) hargaDisplay.innerHTML = '-';
    if (subtotalDisplay) subtotalDisplay.innerHTML = '-';
    
    // Insert before template row
    tbody.insertBefore(clone, newRow);
    
    // Attach event listeners ke row baru
    attachEventListeners(clone);
    
    console.log('✓ New Bahan Baku row added with ID:', clone.id);
}

// Add new Bahan Pendukung row
function addBahanPendukungRow() {
    console.log('Adding new Bahan Pendukung row...');
    
    const newRow = document.getElementById('newBahanPendukungRow');
    if (!newRow) {
        console.error('Template row newBahanPendukungRow not found');
        return;
    }
    
    const tbody = newRow.parentElement;
    const clone = newRow.cloneNode(true);
    
    // Generate unique ID dan name attributes
    const timestamp = Date.now();
    clone.classList.remove('d-none');
    clone.id = 'bahanPendukung_' + timestamp;
    
    // Update name attributes untuk form submission
    clone.querySelectorAll('[name^="bahan_pendukung[new]"]').forEach(input => {
        const fieldName = input.name.match(/\[new\]\[(\w+)\]/)[1];
        input.name = `bahan_pendukung[${timestamp}][${fieldName}]`;
        input.value = '';
        input.removeAttribute('selected');
    });
    
    // Reset displays
    const hargaDisplay = clone.querySelector('.harga-display');
    const subtotalDisplay = clone.querySelector('.subtotal-display');
    if (hargaDisplay) hargaDisplay.innerHTML = '-';
    if (subtotalDisplay) subtotalDisplay.innerHTML = '-';
    
    // Insert before template row
    tbody.insertBefore(clone, newRow);
    
    // Attach event listeners ke row baru
    attachEventListeners(clone);
    
    console.log('✓ New Bahan Pendukung row added with ID:', clone.id);
}

// Initialize existing rows dengan event listeners
function initializeExistingRows() {
    console.log('Initializing existing rows...');
    
    // Initialize semua tabel
    const bahanBakuTable = document.getElementById('bahanBakuTable');
    const bahanPendukungTable = document.getElementById('bahanPendukungTable');
    
    if (bahanBakuTable) {
        attachEventListeners(bahanBakuTable);
        console.log('✓ Bahan Baku table initialized');
    }
    
    if (bahanPendukungTable) {
        attachEventListeners(bahanPendukungTable);
        console.log('✓ Bahan Pendukung table initialized');
    }
    
    // Initialize existing select values
    document.querySelectorAll('.bahan-baku-select, .bahan-pendukung-select').forEach(select => {
        if (select.value) {
            updateHargaDisplay(select);
            console.log('✓ Initialized select:', select.value);
        }
    });
}

// Attach event listeners to container (table or row)
function attachEventListeners(container) {
    if (!container) return;
    
    // Remove button - Enhanced dengan konfirmasi
    container.querySelectorAll('.remove-item').forEach(button => {
        // Remove existing listeners by cloning
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const row = this.closest('tr');
            if (!row) return;
            
            // Jangan hapus template rows
            if (row.id.includes('newBahan')) {
                console.log('Cannot remove template row');
                return;
            }
            
            // Konfirmasi hapus untuk existing data
            const bahanName = row.querySelector('select option:checked')?.textContent || 'item ini';
            if (confirm(`Yakin ingin menghapus ${bahanName}?`)) {
                console.log('Removing row:', row.id);
                row.remove();
                calculateTotals();
                
                // Show success message
                showNotification('Item berhasil dihapus', 'success');
            }
        });
    });

    // Bahan select change - Enhanced dengan validation
    container.querySelectorAll('.bahan-baku-select, .bahan-pendukung-select').forEach(select => {
        select.addEventListener('change', function() {
            console.log('Bahan changed:', this.value, this.options[this.selectedIndex]?.text);
            
            if (this.value) {
                updateHargaDisplay(this);
                
                // Auto-focus ke qty input
                const row = this.closest('tr');
                const qtyInput = row?.querySelector('.qty-input');
                if (qtyInput) {
                    setTimeout(() => qtyInput.focus(), 100);
                }
            }
        });
    });

    // Qty input - Enhanced dengan validation
    container.querySelectorAll('.qty-input').forEach(input => {
        // Real-time calculation
        input.addEventListener('input', function() {
            // Validate positive number
            if (this.value < 0) {
                this.value = 0;
            }
            calculateTotals();
        });
        
        input.addEventListener('change', function() {
            // Format number
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(2);
            }
            calculateTotals();
        });
        
        // Auto-focus ke satuan saat qty diisi
        input.addEventListener('blur', function() {
            if (this.value > 0) {
                const row = this.closest('tr');
                const satuanSelect = row?.querySelector('.satuan-select');
                if (satuanSelect && !satuanSelect.value) {
                    satuanSelect.focus();
                }
            }
        });
    });

    // Satuan select - Enhanced
    container.querySelectorAll('.satuan-select').forEach(select => {
        select.addEventListener('change', function() {
            console.log('Satuan changed:', this.value);
            calculateTotals();
            
            // Validate complete row
            validateRow(this.closest('tr'));
        });
    });
}

// Validate row completeness
function validateRow(row) {
    if (!row) return false;
    
    const bahanSelect = row.querySelector('.bahan-baku-select, .bahan-pendukung-select');
    const qtyInput = row.querySelector('.qty-input');
    const satuanSelect = row.querySelector('.satuan-select');
    
    const isComplete = bahanSelect?.value && 
                      qtyInput?.value && 
                      parseFloat(qtyInput.value) > 0 && 
                      satuanSelect?.value;
    
    // Visual feedback
    if (isComplete) {
        row.classList.remove('table-warning');
        row.classList.add('table-success');
    } else if (bahanSelect?.value || qtyInput?.value || satuanSelect?.value) {
        row.classList.add('table-warning');
        row.classList.remove('table-success');
    } else {
        row.classList.remove('table-warning', 'table-success');
    }
    
    return isComplete;
}

// Update harga display when bahan is selected - Enhanced
function updateHargaDisplay(selectElement) {
    if (!selectElement || !selectElement.value) return;
    
    const row = selectElement.closest('tr');
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const hargaSatuan = parseFloat(selectedOption.dataset.harga) || 0;
    const satuanBahan = selectedOption.dataset.satuan || 'unit';
    const hargaDisplay = row.querySelector('.harga-display');
    const satuanSelect = row.querySelector('.satuan-select');
    
    console.log('Update harga display:', {
        bahan: selectedOption.text,
        harga: hargaSatuan,
        satuan: satuanBahan
    });
    
    // Update harga display dengan format yang lebih baik
    if (hargaDisplay) {
        hargaDisplay.innerHTML = `
            <div class="fw-bold text-primary">Rp ${hargaSatuan.toLocaleString('id-ID')}</div>
            <small class="text-muted">per ${satuanBahan}</small>
        `;
    }
    
    // Auto-select matching satuan hanya jika user belum memilih (khusus baris baru)
    if (satuanSelect && satuanBahan && !satuanSelect.value) {
        // Cari exact match dulu
        let found = false;
        for (let i = 0; i < satuanSelect.options.length; i++) {
            if (satuanSelect.options[i].value.toLowerCase() === satuanBahan.toLowerCase()) {
                satuanSelect.value = satuanSelect.options[i].value;
                found = true;
                console.log('✓ Auto-selected exact satuan:', satuanSelect.options[i].value);
                break;
            }
        }
        
        // Jika tidak ada exact match, cari partial match
        if (!found) {
            for (let i = 0; i < satuanSelect.options.length; i++) {
                const optionValue = satuanSelect.options[i].value.toLowerCase();
                const satuanLower = satuanBahan.toLowerCase();
                
                if (optionValue.includes(satuanLower) || satuanLower.includes(optionValue)) {
                    satuanSelect.value = satuanSelect.options[i].value;
                    console.log('✓ Auto-selected partial satuan:', satuanSelect.options[i].value);
                    break;
                }
            }
        }
    }
    
    // Trigger calculation
    calculateTotals();
}

// Calculate totals - Enhanced dengan error handling dan debugging
function calculateTotals() {
    console.log('=== CALCULATING TOTALS ===');
    
    let totalBahanBaku = 0;
    let totalBahanPendukung = 0;
    let validRowsBB = 0;
    let validRowsBP = 0;

    // Calculate Bahan Baku dengan error handling
    document.querySelectorAll('#bahanBakuTable tbody tr:not(#newBahanBakuRow)').forEach((row, index) => {
        if (row.classList.contains('d-none')) return;
        
        try {
            const select = row.querySelector('.bahan-baku-select');
            const qtyInput = row.querySelector('.qty-input');
            const satuanSelect = row.querySelector('.satuan-select');
            const subtotalDisplay = row.querySelector('.subtotal-display');
            
            console.log(`BB Row ${index}:`, {
                selectedValue: select?.value,
                selectedText: select?.options[select?.selectedIndex]?.text,
                qtyValue: qtyInput?.value,
                satuanValue: satuanSelect?.value,
                dataHarga: select?.options[select?.selectedIndex]?.dataset?.harga,
                dataSatuan: select?.options[select?.selectedIndex]?.dataset?.satuan
            });
            
            if (select?.value && qtyInput?.value && satuanSelect?.value) {
                const option = select.options[select.selectedIndex];
                const harga = parseFloat(option.dataset.harga) || 0;
                const satuanBahan = option.dataset.satuan || 'unit';
                const qty = parseFloat(qtyInput.value) || 0;
                const satuan = satuanSelect.value;
                
                console.log(`BB Row ${index} calculation:`, {
                    bahan: option.text,
                    qty: qty,
                    satuanDipilih: satuan,
                    satuanBahan: satuanBahan,
                    harga: harga
                });
                
                if (qty > 0 && harga > 0) {
                    const subtotal = calculateSubtotal(qty, satuan, satuanBahan, harga);
                    
                    if (subtotalDisplay) {
                        subtotalDisplay.innerHTML = `<strong class="text-success">Rp ${Math.round(subtotal).toLocaleString('id-ID')}</strong>`;
                    }
                    
                    totalBahanBaku += subtotal;
                    validRowsBB++;
                    
                    console.log(`✓ BB Row ${index} calculated:`, {
                        bahan: option.text,
                        qty: qty,
                        satuan: satuan,
                        harga: harga,
                        subtotal: subtotal
                    });
                } else {
                    console.warn(`BB Row ${index}: Invalid qty or harga`, { qty, harga });
                }
            } else {
                console.warn(`BB Row ${index}: Missing required fields`, {
                    hasSelect: !!select?.value,
                    hasQty: !!qtyInput?.value,
                    hasSatuan: !!satuanSelect?.value
                });
            }
        } catch (error) {
            console.error(`Error calculating Bahan Baku row ${index}:`, error);
        }
    });

    // Calculate Bahan Pendukung dengan error handling
    document.querySelectorAll('#bahanPendukungTable tbody tr:not(#newBahanPendukungRow)').forEach((row, index) => {
        if (row.classList.contains('d-none')) return;
        
        try {
            const select = row.querySelector('.bahan-pendukung-select');
            const qtyInput = row.querySelector('.qty-input');
            const satuanSelect = row.querySelector('.satuan-select');
            const subtotalDisplay = row.querySelector('.subtotal-display');
            
            console.log(`BP Row ${index}:`, {
                selectedValue: select?.value,
                selectedText: select?.options[select?.selectedIndex]?.text,
                qtyValue: qtyInput?.value,
                satuanValue: satuanSelect?.value,
                dataHarga: select?.options[select?.selectedIndex]?.dataset?.harga,
                dataSatuan: select?.options[select?.selectedIndex]?.dataset?.satuan
            });
            
            if (select?.value && qtyInput?.value && satuanSelect?.value) {
                const option = select.options[select.selectedIndex];
                const harga = parseFloat(option.dataset.harga) || 0;
                const satuanBahan = option.dataset.satuan || 'unit';
                const qty = parseFloat(qtyInput.value) || 0;
                const satuan = satuanSelect.value;
                
                console.log(`BP Row ${index} calculation:`, {
                    bahan: option.text,
                    qty: qty,
                    satuanDipilih: satuan,
                    satuanBahan: satuanBahan,
                    harga: harga
                });
                
                if (qty > 0 && harga > 0) {
                    const subtotal = calculateSubtotal(qty, satuan, satuanBahan, harga);
                    
                    if (subtotalDisplay) {
                        subtotalDisplay.innerHTML = `<strong class="text-success">Rp ${Math.round(subtotal).toLocaleString('id-ID')}</strong>`;
                    }
                    
                    totalBahanPendukung += subtotal;
                    validRowsBP++;
                    
                    console.log(`✓ BP Row ${index} calculated:`, {
                        bahan: option.text,
                        qty: qty,
                        satuan: satuan,
                        harga: harga,
                        subtotal: subtotal
                    });
                } else {
                    console.warn(`BP Row ${index}: Invalid qty or harga`, { qty, harga });
                }
            } else {
                console.warn(`BP Row ${index}: Missing required fields`, {
                    hasSelect: !!select?.value,
                    hasQty: !!qtyInput?.value,
                    hasSatuan: !!satuanSelect?.value
                });
            }
        } catch (error) {
            console.error(`Error calculating Bahan Pendukung row ${index}:`, error);
        }
    });

    // Update displays dengan format yang konsisten
    const totalBiaya = totalBahanBaku + totalBahanPendukung;

    console.log('=== TOTALS ===', {
        totalBahanBaku: totalBahanBaku,
        totalBahanPendukung: totalBahanPendukung,
        totalBiaya: totalBiaya,
        validRowsBB: validRowsBB,
        validRowsBP: validRowsBP
    });

    // Update total displays
    updateElementText('totalBahanBaku', `Rp ${Math.round(totalBahanBaku).toLocaleString('id-ID')}`);
    updateElementText('totalBahanPendukung', `Rp ${Math.round(totalBahanPendukung).toLocaleString('id-ID')}`);
    updateElementText('summaryBahanBaku', `Rp ${Math.round(totalBahanBaku).toLocaleString('id-ID')}`);
    updateElementText('summaryBahanPendukung', `Rp ${Math.round(totalBahanPendukung).toLocaleString('id-ID')}`);
    updateElementText('summaryTotalBiaya', `Rp ${Math.round(totalBiaya).toLocaleString('id-ID')}`);
    
    console.log('=== CALCULATION COMPLETED ===');
}

// Helper function untuk update element text dengan error handling
function updateElementText(elementId, text) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = text;
    } else {
        console.warn(`Element with ID '${elementId}' not found`);
    }
}

// Calculate subtotal with enhanced conversion - Fixed Version with Better Fallback
function calculateSubtotal(qty, satuanDipilih, satuanBahan, hargaSatuan) {
    if (!qty || !hargaSatuan || qty <= 0 || hargaSatuan <= 0) {
        return 0;
    }

    const from = (satuanDipilih || '').toString().toLowerCase().trim();
    const to = (satuanBahan || '').toString().toLowerCase().trim();

    if (!from || !to || from === to) {
        return qty * hargaSatuan;
    }

    const aliases = {
        // mass
        'gram': 'g', 'gr': 'g', 'g': 'g',
        'kilogram': 'kg', 'kg': 'kg',
        'miligram': 'mg', 'milligram': 'mg', 'mg': 'mg',
        'ons': 'ons',
        // volume
        'liter': 'l', 'ltr': 'l', 'l': 'l',
        'mililiter': 'ml', 'milliliter': 'ml', 'ml': 'ml', 'cc': 'ml',
        'sdt': 'sdt', 'sendok_teh': 'sdt', 'sendok teh': 'sdt',
        'sdm': 'sdm', 'sendok_makan': 'sdm', 'sendok makan': 'sdm',
        'cup': 'cup',
        // count
        'pcs': 'pcs', 'buah': 'pcs', 'butir': 'pcs', 'biji': 'pcs'
    };

    const f = aliases[from] || from;
    const t = aliases[to] || to;

    const mass = { kg: 1000.0, g: 1.0, mg: 0.001, ons: 100.0 };
    const volume = { l: 1000.0, ml: 1.0, sdt: 5.0, sdm: 15.0, cup: 240.0 };
    const count = { pcs: 1.0 };

    const inMass = (u) => Object.prototype.hasOwnProperty.call(mass, u);
    const inVolume = (u) => Object.prototype.hasOwnProperty.call(volume, u);
    const inCount = (u) => Object.prototype.hasOwnProperty.call(count, u);

    let qtyBase = qty;

    if (inMass(f) && inMass(t)) {
        qtyBase = qty * (mass[f] / mass[t]);
    } else if (inVolume(f) && inVolume(t)) {
        qtyBase = qty * (volume[f] / volume[t]);
    } else if (inCount(f) && inCount(t)) {
        qtyBase = qty * (count[f] / count[t]);
    } else {
        if (!window.unitConversionWarningShown) {
            showNotification(`⚠️ Konversi tidak valid: ${satuanDipilih} ke ${satuanBahan}. Subtotal dihitung tanpa konversi.`, 'warning');
            window.unitConversionWarningShown = true;
        }
        qtyBase = qty;
    }

    return qtyBase * hargaSatuan;
}

// Show notification helper
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Form validation before submit
function validateForm() {
    const bahanBakuRows = document.querySelectorAll('#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none)');
    const bahanPendukungRows = document.querySelectorAll('#bahanPendukungTable tbody tr:not(#newBahanPendukungRow):not(.d-none)');
    
    let validBB = 0;
    let validBP = 0;
    
    bahanBakuRows.forEach(row => {
        if (validateRow(row)) validBB++;
    });
    
    bahanPendukungRows.forEach(row => {
        if (validateRow(row)) validBP++;
    });
    
    if (validBB === 0 && validBP === 0) {
        showNotification('Minimal harus ada 1 bahan baku atau bahan pendukung yang valid!', 'warning');
        return false;
    }
    
    console.log(`Form validation: ${validBB} valid BB rows, ${validBP} valid BP rows`);
    return true;
}

// Form validation is handled in the Blade template to avoid conflicts
// validateForm() function can still be called manually if needed
