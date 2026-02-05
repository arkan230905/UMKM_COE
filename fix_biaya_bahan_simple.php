<?php

// Script to replace the entire JavaScript section in create.blade.php with a simpler version

$filePath = 'resources/views/master-data/biaya-bahan/create.blade.php';
$content = file_get_contents($filePath);

// Find the start and end of the scripts section
$startPattern = '@push(\'scripts\')';
$endPattern = '@endpush';

$startPos = strpos($content, $startPattern);
$endPos = strpos($content, $endPattern, $startPos);

if ($startPos === false || $endPos === false) {
    echo "ERROR: Could not find scripts section\n";
    exit(1);
}

// Calculate the length to replace (include @endpush)
$endPos += strlen($endPattern);
$lengthToReplace = $endPos - $startPos;

// New simple JavaScript content
$newScriptContent = '@push(\'scripts\')
<script>
console.log(\'=== Biaya Bahan Create - Simple Version ===\');
console.log(\'=== TIMESTAMP: \' + new Date().toISOString() + \' ===\');

// Simple add row functions
function addBahanBakuRow() {
    console.log(\'=== addBahanBakuRow called ===\');
    
    const newRow = document.getElementById(\'newBahanBakuRow\');
    if (!newRow) {
        console.error(\'ERROR: newBahanBakuRow not found!\');
        alert(\'Error: Template row tidak ditemukan!\');
        return false;
    }
    
    const tbody = newRow.parentElement;
    const clone = newRow.cloneNode(true);
    clone.classList.remove(\'d-none\');
    clone.id = \'bahanBaku_\' + Date.now();
    
    // Update name attributes
    const timestamp = Date.now();
    clone.querySelectorAll(\'[name^="bahan_baku[new]"]\').forEach(input => {
        const fieldName = input.name.match(/\\[new\\]\\[(\\w+)\\]/)[1];
        input.name = `bahan_baku[${timestamp}][${fieldName}]`;
        input.value = \'\';
    });
    
    tbody.insertBefore(clone, newRow);
    console.log(\'✓ Row added successfully! ID:\', clone.id);
    
    // Add event listeners to new row
    addRowEventListeners(clone);
    
    return false;
}

function addBahanPendukungRow() {
    console.log(\'=== addBahanPendukungRow called ===\');
    
    const newRow = document.getElementById(\'newBahanPendukungRow\');
    if (!newRow) {
        console.error(\'ERROR: newBahanPendukungRow not found!\');
        alert(\'Error: Template row tidak ditemukan!\');
        return false;
    }
    
    const tbody = newRow.parentElement;
    const clone = newRow.cloneNode(true);
    clone.classList.remove(\'d-none\');
    clone.id = \'bahanPendukung_\' + Date.now();
    
    // Update name attributes
    const timestamp = Date.now();
    clone.querySelectorAll(\'[name^="bahan_pendukung[new]"]\').forEach(input => {
        const fieldName = input.name.match(/\\[new\\]\\[(\\w+)\\]/)[1];
        input.name = `bahan_pendukung[${timestamp}][${fieldName}]`;
        input.value = \'\';
    });
    
    tbody.insertBefore(clone, newRow);
    console.log(\'✓ Row added successfully! ID:\', clone.id);
    
    // Add event listeners to new row
    addRowEventListeners(clone);
    
    return false;
}

function addRowEventListeners(row) {
    const bahanSelect = row.querySelector(\'.bahan-baku-select, .bahan-pendukung-select\');
    const qtyInput = row.querySelector(\'.qty-input\');
    const satuanSelect = row.querySelector(\'.satuan-select\');
    const removeBtn = row.querySelector(\'.remove-item\');
    
    if (bahanSelect) {
        bahanSelect.addEventListener(\'change\', function() {
            console.log(\'Bahan selected:\', this.value);
            const option = this.options[this.selectedIndex];
            if (option && option.dataset.harga) {
                // Auto-fill satuan
                if (option.dataset.satuan && satuanSelect) {
                    satuanSelect.value = option.dataset.satuan;
                }
                
                // Update harga display
                const hargaDisplay = row.querySelector(\'.harga-utama\');
                if (hargaDisplay) {
                    const harga = parseInt(option.dataset.harga);
                    hargaDisplay.innerHTML = \'<strong>Rp \' + harga.toLocaleString(\'id-ID\') + \'</strong>\';
                }
                
                // Show conversion info
                updateConversionDisplay(row, option);
            }
            calculateRowSubtotal(row);
        });
    }
    
    if (qtyInput) {
        qtyInput.addEventListener(\'input\', function() {
            calculateRowSubtotal(row);
        });
    }
    
    if (satuanSelect) {
        satuanSelect.addEventListener(\'change\', function() {
            console.log(\'Satuan changed:\', this.value);
            const bahanSelect = row.querySelector(\'.bahan-baku-select, .bahan-pendukung-select\');
            if (bahanSelect && bahanSelect.value) {
                const option = bahanSelect.options[bahanSelect.selectedIndex];
                updateConversionDisplay(row, option);
            }
            calculateRowSubtotal(row);
        });
    }
    
    if (removeBtn) {
        removeBtn.addEventListener(\'click\', function() {
            if (confirm(\'Yakin ingin menghapus baris ini?\')) {
                row.remove();
                calculateTotals();
            }
        });
    }
}

function updateConversionDisplay(row, option) {
    const hargaKonversiDiv = row.querySelector(\'.harga-konversi\');
    const satuanSelect = row.querySelector(\'.satuan-select\');
    
    if (!hargaKonversiDiv || !option || !satuanSelect) return;
    
    const hargaUtama = parseFloat(option.dataset.harga) || 0;
    const satuanUtama = option.dataset.satuan || \'unit\';
    const satuanDipilih = satuanSelect.value;
    
    if (!satuanDipilih) {
        hargaKonversiDiv.innerHTML = \'<small class="text-muted">Pilih satuan untuk melihat konversi</small>\';
        return;
    }
    
    if (satuanDipilih === satuanUtama) {
        hargaKonversiDiv.innerHTML = \'<small class="text-success">Satuan sama, tidak perlu konversi</small>\';
        return;
    }
    
    // Simple conversion examples
    let konversiText = \'\';
    if (satuanUtama.toLowerCase() === \'ekor\' && satuanDipilih.toLowerCase() === \'potong\') {
        const hargaKonversi = hargaUtama / 6; // 1 ekor = 6 potong
        konversiText = `
            <div class="text-info"><strong>Rp ${Math.round(hargaKonversi).toLocaleString(\'id-ID\')}/${satuanDipilih}</strong></div>
            <small class="text-muted">Rumus: 1 Ekor = 6 Potong<br>Rp ${hargaUtama.toLocaleString(\'id-ID\')} ÷ 6 = Rp ${Math.round(hargaKonversi).toLocaleString(\'id-ID\')}</small>
        `;
    } else if (satuanUtama.toLowerCase() === \'kilogram\' && satuanDipilih.toLowerCase() === \'gram\') {
        const hargaKonversi = hargaUtama / 1000; // 1 kg = 1000 gram
        konversiText = `
            <div class="text-info"><strong>Rp ${Math.round(hargaKonversi).toLocaleString(\'id-ID\')}/${satuanDipilih}</strong></div>
            <small class="text-muted">Rumus: 1 Kilogram = 1000 Gram<br>Rp ${hargaUtama.toLocaleString(\'id-ID\')} ÷ 1000 = Rp ${Math.round(hargaKonversi).toLocaleString(\'id-ID\')}</small>
        `;
    } else {
        konversiText = `<small class="text-warning">Konversi dari ${satuanUtama} ke ${satuanDipilih} tidak tersedia</small>`;
    }
    
    hargaKonversiDiv.innerHTML = konversiText;
}

function calculateRowSubtotal(row) {
    const bahanSelect = row.querySelector(\'.bahan-baku-select, .bahan-pendukung-select\');
    const qtyInput = row.querySelector(\'.qty-input\');
    const satuanSelect = row.querySelector(\'.satuan-select\');
    const subtotalDisplay = row.querySelector(\'.subtotal-display\');
    
    if (!bahanSelect || !qtyInput || !satuanSelect || !subtotalDisplay) return;
    
    const option = bahanSelect.options[bahanSelect.selectedIndex];
    if (!option || !option.value) {
        subtotalDisplay.innerHTML = \'-\';
        return;
    }
    
    const harga = parseFloat(option.dataset.harga) || 0;
    const qty = parseFloat(qtyInput.value) || 0;
    const satuanUtama = option.dataset.satuan || \'unit\';
    const satuanDipilih = satuanSelect.value;
    
    if (qty <= 0 || !satuanDipilih) {
        subtotalDisplay.innerHTML = \'-\';
        return;
    }
    
    let subtotal = harga * qty;
    
    // Simple conversion for calculation
    if (satuanUtama.toLowerCase() === \'ekor\' && satuanDipilih.toLowerCase() === \'potong\') {
        subtotal = (harga / 6) * qty; // 1 ekor = 6 potong
    } else if (satuanUtama.toLowerCase() === \'kilogram\' && satuanDipilih.toLowerCase() === \'gram\') {
        subtotal = (harga / 1000) * qty; // 1 kg = 1000 gram
    }
    
    subtotalDisplay.innerHTML = \'<strong class="text-success">Rp \' + Math.round(subtotal).toLocaleString(\'id-ID\') + \'</strong>\';
    
    // Update totals
    setTimeout(calculateTotals, 100);
}

function calculateTotals() {
    let totalBahanBaku = 0;
    let totalBahanPendukung = 0;
    
    // Calculate Bahan Baku
    document.querySelectorAll(\'#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none)\').forEach(row => {
        const subtotalText = row.querySelector(\'.subtotal-display\')?.textContent || \'\';
        const subtotal = parseFloat(subtotalText.replace(/[^\\d]/g, \'\')) || 0;
        totalBahanBaku += subtotal;
    });
    
    // Calculate Bahan Pendukung
    document.querySelectorAll(\'#bahanPendukungTable tbody tr:not(#newBahanPendukungRow):not(.d-none)\').forEach(row => {
        const subtotalText = row.querySelector(\'.subtotal-display\')?.textContent || \'\';
        const subtotal = parseFloat(subtotalText.replace(/[^\\d]/g, \'\')) || 0;
        totalBahanPendukung += subtotal;
    });
    
    const totalBiaya = totalBahanBaku + totalBahanPendukung;
    
    // Update displays
    const totalBBElement = document.getElementById(\'totalBahanBaku\');
    const totalBPElement = document.getElementById(\'totalBahanPendukung\');
    const summaryBBElement = document.getElementById(\'summaryBahanBaku\');
    const summaryBPElement = document.getElementById(\'summaryBahanPendukung\');
    const summaryTotalElement = document.getElementById(\'summaryTotalBiaya\');
    
    if (totalBBElement) totalBBElement.textContent = \'Rp \' + totalBahanBaku.toLocaleString(\'id-ID\');
    if (totalBPElement) totalBPElement.textContent = \'Rp \' + totalBahanPendukung.toLocaleString(\'id-ID\');
    if (summaryBBElement) summaryBBElement.textContent = \'Rp \' + totalBahanBaku.toLocaleString(\'id-ID\');
    if (summaryBPElement) summaryBPElement.textContent = \'Rp \' + totalBahanPendukung.toLocaleString(\'id-ID\');
    if (summaryTotalElement) summaryTotalElement.textContent = \'Rp \' + totalBiaya.toLocaleString(\'id-ID\');
}

// Make functions global
window.addBahanBakuRow = addBahanBakuRow;
window.addBahanPendukungRow = addBahanPendukungRow;

// Initialize when DOM is ready
document.addEventListener(\'DOMContentLoaded\', function() {
    console.log(\'=== DOM loaded, initializing simple version ===\');
    
    // Add event listeners to existing buttons
    const addBBBtn = document.getElementById(\'addBahanBaku\');
    const addBPBtn = document.getElementById(\'addBahanPendukung\');
    
    if (addBBBtn) {
        addBBBtn.addEventListener(\'click\', function(e) {
            e.preventDefault();
            addBahanBakuRow();
        });
        console.log(\'✓ Add Bahan Baku button listener attached\');
    }
    
    if (addBPBtn) {
        addBPBtn.addEventListener(\'click\', function(e) {
            e.preventDefault();
            addBahanPendukungRow();
        });
        console.log(\'✓ Add Bahan Pendukung button listener attached\');
    }
    
    // Auto-add first row
    setTimeout(() => {
        const existingRows = document.querySelectorAll(\'#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none), #bahanPendukungTable tbody tr:not(#newBahanPendukungRow):not(.d-none)\');
        if (existingRows.length === 0) {
            console.log(\'No existing rows, adding first Bahan Baku row...\');
            addBahanBakuRow();
        }
    }, 1000);
    
    console.log(\'=== Initialization complete ===\');
});
</script>
@endpush';

// Replace the content
$newContent = substr_replace($content, $newScriptContent, $startPos, $lengthToReplace);

// Write back to file
if (file_put_contents($filePath, $newContent)) {
    echo "SUCCESS: JavaScript section replaced with simple version\n";
    echo "Changes made:\n";
    echo "✓ Removed complex JavaScript functions\n";
    echo "✓ Added simple add row functions\n";
    echo "✓ Added basic conversion display\n";
    echo "✓ Added auto-add first row on page load\n";
    echo "✓ Added comprehensive debugging\n\n";
    echo "Please test the page now:\n";
    echo "1. Clear browser cache (Ctrl+Shift+R)\n";
    echo "2. Open console (F12)\n";
    echo "3. Visit /master-data/biaya-bahan/create/2\n";
    echo "4. Should see input row automatically\n";
    echo "5. Test add buttons and conversion formulas\n";
} else {
    echo "ERROR: Could not write to file\n";
}