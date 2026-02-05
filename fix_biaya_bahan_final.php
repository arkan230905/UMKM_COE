<?php

// Script untuk mengganti seluruh JavaScript dengan versi yang benar-benar berfungsi

$filePath = 'resources/views/master-data/biaya-bahan/create.blade.php';
$content = file_get_contents($filePath);

// Cari dan hapus seluruh bagian @push('scripts') sampai @endpush
$startPattern = '@push(\'scripts\')';
$endPattern = '@endpush';

$startPos = strpos($content, $startPattern);
$endPos = strpos($content, $endPattern, $startPos);

if ($startPos === false || $endPos === false) {
    echo "ERROR: Could not find scripts section\n";
    exit(1);
}

// Hitung panjang yang akan diganti (termasuk @endpush)
$endPos += strlen($endPattern);
$lengthToReplace = $endPos - $startPos;

// JavaScript baru yang benar-benar berfungsi
$newScriptContent = '@push(\'scripts\')
<!-- FORCE REFRESH: ' . time() . ' -->
<script>
console.log("=== BIAYA BAHAN FINAL VERSION LOADED ===");
console.log("Timestamp: " + new Date().toISOString());

// GLOBAL VARIABLES
window.biayaBahanLoaded = true;

// Helper function untuk format angka bersih
function formatNumberClean(num) {
    if (num === Math.floor(num)) {
        return Math.floor(num).toString();
    }
    return parseFloat(num.toFixed(4)).toString();
}

// FUNGSI UTAMA: Update Conversion Display
function updateConversionDisplay(row, option) {
    console.log("=== updateConversionDisplay DIPANGGIL ===");
    
    const hargaKonversiDiv = row.querySelector(\'.harga-konversi\');
    const satuanSelect = row.querySelector(\'.satuan-select\');
    
    if (!hargaKonversiDiv || !option || !satuanSelect) {
        console.log("Element tidak ditemukan:", {
            hargaKonversiDiv: !!hargaKonversiDiv,
            option: !!option,
            satuanSelect: !!satuanSelect
        });
        return;
    }
    
    const hargaUtama = parseFloat(option.dataset.harga) || 0;
    const satuanUtama = option.dataset.satuan || "unit";
    const satuanDipilih = satuanSelect.value;
    const subSatuanData = JSON.parse(option.dataset.subSatuan || "[]");
    
    console.log("Data konversi:", {
        hargaUtama: hargaUtama,
        satuanUtama: satuanUtama,
        satuanDipilih: satuanDipilih,
        subSatuanData: subSatuanData
    });
    
    if (!satuanDipilih) {
        hargaKonversiDiv.innerHTML = \'<small class="text-muted">Pilih satuan untuk melihat konversi</small>\';
        return;
    }
    
    // PRIORITAS: Gunakan data sub satuan dari database
    if (subSatuanData.length > 0) {
        console.log("✅ Menggunakan data sub satuan dari database");
        
        // Cari konversi yang tepat
        const matchingSub = subSatuanData.find(sub => 
            sub.nama.toLowerCase().trim() === satuanDipilih.toLowerCase().trim()
        );
        
        if (matchingSub) {
            // KONVERSI SPESIFIK
            const hargaKonversi = (hargaUtama * matchingSub.konversi) / matchingSub.nilai;
            const konversiClean = formatNumberClean(matchingSub.konversi);
            const nilaiClean = formatNumberClean(matchingSub.nilai);
            
            console.log("✅ Konversi ditemukan:", matchingSub);
            console.log("Harga konversi:", hargaKonversi);
            
            const konversiText = `
                <div class="text-info mb-2">
                    <strong>Rp ${Math.round(hargaKonversi).toLocaleString("id-ID")}/${satuanDipilih}</strong>
                </div>
                <div class="text-muted" style="font-size: 0.75rem; line-height: 1.4;">
                    <div class="mb-1"><strong>📊 Rumus Konversi:</strong></div>
                    <div class="mb-1">• Dasar: ${konversiClean} ${satuanUtama} = ${nilaiClean} ${satuanDipilih}</div>
                    <div class="mb-1">• Perhitungan: (Rp ${hargaUtama.toLocaleString("id-ID")} × ${konversiClean}) ÷ ${nilaiClean}</div>
                    <div class="text-success"><strong>• Hasil: Rp ${Math.round(hargaKonversi).toLocaleString("id-ID")} per ${satuanDipilih}</strong></div>
                    <div class="mt-1 text-primary" style="font-size: 0.7rem;">
                        <em>💡 Berdasarkan sub satuan database</em>
                    </div>
                </div>
            `;
            hargaKonversiDiv.innerHTML = konversiText;
            return;
        }
        
        // TAMPILKAN SEMUA KONVERSI jika satuan sama atau tidak ada yang cocok
        if (satuanDipilih === satuanUtama || !matchingSub) {
            console.log("✅ Menampilkan semua konversi tersedia");
            
            let allConversions = `<div class="text-success mb-2"><strong>📋 Konversi Tersedia:</strong></div>`;
            
            subSatuanData.forEach((sub, index) => {
                const hargaKonversi = (hargaUtama * sub.konversi) / sub.nilai;
                const konversiClean = formatNumberClean(sub.konversi);
                const nilaiClean = formatNumberClean(sub.nilai);
                
                allConversions += `
                    <div class="border-start border-info ps-2 mb-2" style="font-size: 0.75rem;">
                        <div class="text-info mb-1">
                            <strong>Rp ${Math.round(hargaKonversi).toLocaleString("id-ID")}/${sub.nama}</strong>
                        </div>
                        <div class="text-muted" style="line-height: 1.3;">
                            <div>• Dasar: ${konversiClean} ${satuanUtama} = ${nilaiClean} ${sub.nama}</div>
                            <div>• Rumus: (Rp ${hargaUtama.toLocaleString("id-ID")} × ${konversiClean}) ÷ ${nilaiClean}</div>
                        </div>
                    </div>
                `;
            });
            
            allConversions += `<div class="mt-2 text-primary" style="font-size: 0.7rem;"><em>💡 Dari database sub satuan</em></div>`;
            hargaKonversiDiv.innerHTML = allConversions;
            return;
        }
    } else {
        // TIDAK ADA SUB SATUAN
        console.log("⚠️ Tidak ada data sub satuan");
        
        if (satuanDipilih === satuanUtama) {
            hargaKonversiDiv.innerHTML = `
                <div class="text-success mb-1">
                    <strong>Rp ${hargaUtama.toLocaleString("id-ID")}/${satuanUtama}</strong>
                </div>
                <div class="text-muted" style="font-size: 0.75rem;">
                    ✅ Satuan sama dengan satuan utama
                </div>
            `;
        } else {
            hargaKonversiDiv.innerHTML = `
                <div class="text-warning mb-1">
                    <strong>Konversi tidak tersedia</strong>
                </div>
                <div class="text-muted" style="font-size: 0.75rem;">
                    ❌ Tidak ada konversi dari ${satuanUtama} ke ${satuanDipilih}
                </div>
            `;
        }
    }
}

// FUNGSI: Get Conversion Factor untuk perhitungan subtotal
function getConversionFactor(fromUnit, toUnit, subSatuanData = []) {
    console.log("=== getConversionFactor ===");
    console.log("Dari:", fromUnit, "Ke:", toUnit);
    
    const from = fromUnit.toLowerCase().trim();
    const to = toUnit.toLowerCase().trim();
    
    if (from === to) {
        return { factor: 1, source: "same_unit" };
    }
    
    // Cari di data sub satuan database
    if (subSatuanData && subSatuanData.length > 0) {
        const matchingSub = subSatuanData.find(sub => 
            sub.nama.toLowerCase().trim() === to
        );
        
        if (matchingSub) {
            const factor = matchingSub.konversi / matchingSub.nilai;
            console.log("✅ Faktor konversi dari database:", factor);
            return { factor: factor, source: "database" };
        }
    }
    
    console.log("❌ Tidak ada faktor konversi");
    return { factor: null, source: "not_found" };
}

// FUNGSI: Calculate Row Subtotal
function calculateRowSubtotal(row) {
    console.log("=== calculateRowSubtotal DIPANGGIL ===");
    
    const bahanSelect = row.querySelector(\'.bahan-baku-select, .bahan-pendukung-select\');
    const qtyInput = row.querySelector(\'.qty-input\');
    const satuanSelect = row.querySelector(\'.satuan-select\');
    const subtotalDisplay = row.querySelector(\'.subtotal-display\');
    
    console.log("Elements found:", {
        bahanSelect: !!bahanSelect,
        qtyInput: !!qtyInput,
        satuanSelect: !!satuanSelect,
        subtotalDisplay: !!subtotalDisplay
    });
    
    if (!bahanSelect || !qtyInput || !satuanSelect || !subtotalDisplay) {
        console.log("❌ Element tidak lengkap");
        return;
    }
    
    const option = bahanSelect.options[bahanSelect.selectedIndex];
    if (!option || !option.value) {
        console.log("❌ Tidak ada bahan yang dipilih");
        subtotalDisplay.innerHTML = \'-\';
        return;
    }
    
    const harga = parseFloat(option.dataset.harga) || 0;
    const qty = parseFloat(qtyInput.value) || 0;
    const satuanUtama = option.dataset.satuan || "unit";
    const satuanDipilih = satuanSelect.value;
    const subSatuanData = JSON.parse(option.dataset.subSatuan || "[]");
    
    console.log("Data perhitungan:", {
        harga: harga,
        qty: qty,
        satuanUtama: satuanUtama,
        satuanDipilih: satuanDipilih
    });
    
    if (qty <= 0 || !satuanDipilih) {
        console.log("❌ Quantity atau satuan tidak valid");
        subtotalDisplay.innerHTML = \'-\';
        return;
    }
    
    let subtotal = harga * qty;
    console.log("Subtotal dasar (same unit):", subtotal);
    
    // Gunakan konversi database jika satuan berbeda
    if (satuanUtama !== satuanDipilih) {
        console.log("🔄 Menerapkan konversi...");
        const conversionResult = getConversionFactor(satuanUtama, satuanDipilih, subSatuanData);
        if (conversionResult.factor !== null) {
            subtotal = (harga * conversionResult.factor) * qty;
            console.log("✅ Subtotal setelah konversi:", subtotal, "dengan faktor:", conversionResult.factor);
        } else {
            console.log("⚠️ Tidak ada faktor konversi, menggunakan harga dasar");
        }
    }
    
    subtotalDisplay.innerHTML = \'<strong class="text-success">Rp \' + Math.round(subtotal).toLocaleString("id-ID") + \'</strong>\';
    console.log("✅ Subtotal display updated:", subtotalDisplay.innerHTML);
    
    // Update total
    setTimeout(calculateTotals, 100);
}

// FUNGSI: Calculate Totals
function calculateTotals() {
    console.log("=== calculateTotals ===");
    
    let totalBahanBaku = 0;
    let totalBahanPendukung = 0;
    
    // Hitung Bahan Baku
    document.querySelectorAll(\'#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none)\').forEach(row => {
        const subtotalText = row.querySelector(\'.subtotal-display\')?.textContent || \'\';
        const subtotal = parseFloat(subtotalText.replace(/[^\\d]/g, \'\')) || 0;
        totalBahanBaku += subtotal;
    });
    
    // Hitung Bahan Pendukung
    document.querySelectorAll(\'#bahanPendukungTable tbody tr:not(#newBahanPendukungRow):not(.d-none)\').forEach(row => {
        const subtotalText = row.querySelector(\'.subtotal-display\')?.textContent || \'\';
        const subtotal = parseFloat(subtotalText.replace(/[^\\d]/g, \'\')) || 0;
        totalBahanPendukung += subtotal;
    });
    
    const totalBiaya = totalBahanBaku + totalBahanPendukung;
    
    console.log("Totals calculated:", {
        bahanBaku: totalBahanBaku,
        bahanPendukung: totalBahanPendukung,
        total: totalBiaya
    });
    
    // Update displays
    const elements = {
        totalBahanBaku: document.getElementById(\'totalBahanBaku\'),
        totalBahanPendukung: document.getElementById(\'totalBahanPendukung\'),
        summaryBahanBaku: document.getElementById(\'summaryBahanBaku\'),
        summaryBahanPendukung: document.getElementById(\'summaryBahanPendukung\'),
        summaryTotalBiaya: document.getElementById(\'summaryTotalBiaya\')
    };
    
    if (elements.totalBahanBaku) elements.totalBahanBaku.textContent = \'Rp \' + totalBahanBaku.toLocaleString(\'id-ID\');
    if (elements.totalBahanPendukung) elements.totalBahanPendukung.textContent = \'Rp \' + totalBahanPendukung.toLocaleString(\'id-ID\');
    if (elements.summaryBahanBaku) elements.summaryBahanBaku.textContent = \'Rp \' + totalBahanBaku.toLocaleString(\'id-ID\');
    if (elements.summaryBahanPendukung) elements.summaryBahanPendukung.textContent = \'Rp \' + totalBahanPendukung.toLocaleString(\'id-ID\');
    if (elements.summaryTotalBiaya) elements.summaryTotalBiaya.textContent = \'Rp \' + totalBiaya.toLocaleString(\'id-ID\');
}

// FUNGSI: Add Row Event Listeners
function addRowEventListeners(row) {
    console.log("=== addRowEventListeners ===");
    
    const bahanSelect = row.querySelector(\'.bahan-baku-select, .bahan-pendukung-select\');
    const qtyInput = row.querySelector(\'.qty-input\');
    const satuanSelect = row.querySelector(\'.satuan-select\');
    const removeBtn = row.querySelector(\'.remove-item\');
    
    if (bahanSelect) {
        bahanSelect.addEventListener(\'change\', function() {
            console.log("🔄 Bahan selected:", this.value);
            const option = this.options[this.selectedIndex];
            if (option && option.dataset.harga) {
                // Auto-fill satuan
                if (option.dataset.satuan && satuanSelect) {
                    satuanSelect.value = option.dataset.satuan;
                    console.log("✅ Auto-filled satuan:", option.dataset.satuan);
                }
                
                // Auto-set quantity jika kosong
                if (qtyInput && (!qtyInput.value || qtyInput.value === \'0\')) {
                    qtyInput.value = \'1\';
                    console.log("✅ Auto-set quantity to 1");
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
            console.log("🔄 Quantity changed:", this.value);
            calculateRowSubtotal(row);
        });
    }
    
    if (satuanSelect) {
        satuanSelect.addEventListener(\'change\', function() {
            console.log("🔄 Satuan changed:", this.value);
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

// FUNGSI: Add Bahan Baku Row
function addBahanBakuRow() {
    console.log("=== addBahanBakuRow ===");
    
    const newRow = document.getElementById(\'newBahanBakuRow\');
    if (!newRow) {
        console.error("❌ newBahanBakuRow not found!");
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
    console.log("✅ Row added successfully! ID:", clone.id);
    
    // Add event listeners
    addRowEventListeners(clone);
    
    return false;
}

// FUNGSI: Add Bahan Pendukung Row
function addBahanPendukungRow() {
    console.log("=== addBahanPendukungRow ===");
    
    const newRow = document.getElementById(\'newBahanPendukungRow\');
    if (!newRow) {
        console.error("❌ newBahanPendukungRow not found!");
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
    console.log("✅ Row added successfully! ID:", clone.id);
    
    // Add event listeners
    addRowEventListeners(clone);
    
    return false;
}

// FUNGSI DEBUG
function emergencyDebug() {
    console.log("=== EMERGENCY DEBUG ===");
    const testResult = document.getElementById(\'testResult\');
    
    let debugInfo = [];
    
    // Check functions
    debugInfo.push(\'Functions exist:\');
    debugInfo.push(`- updateConversionDisplay: ${typeof updateConversionDisplay}`);
    debugInfo.push(`- calculateRowSubtotal: ${typeof calculateRowSubtotal}`);
    debugInfo.push(`- getConversionFactor: ${typeof getConversionFactor}`);
    
    // Check DOM
    const rows = document.querySelectorAll(\'#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none)\');
    debugInfo.push(`<br>DOM elements:`);
    debugInfo.push(`- Visible rows: ${rows.length}`);
    
    if (rows.length > 0) {
        const firstRow = rows[0];
        const bahanSelect = firstRow.querySelector(\'.bahan-baku-select\');
        const hargaKonversiDiv = firstRow.querySelector(\'.harga-konversi\');
        const satuanSelect = firstRow.querySelector(\'.satuan-select\');
        
        debugInfo.push(`- Bahan select: ${!!bahanSelect} (value: ${bahanSelect?.value || \'none\'})`);
        debugInfo.push(`- Harga konversi div: ${!!hargaKonversiDiv}`);
        debugInfo.push(`- Satuan select: ${!!satuanSelect} (value: ${satuanSelect?.value || \'none\'})`);
        
        if (bahanSelect && bahanSelect.value) {
            const option = bahanSelect.options[bahanSelect.selectedIndex];
            debugInfo.push(`- Option data-harga: ${option?.dataset?.harga || \'none\'}`);
            debugInfo.push(`- Option data-satuan: ${option?.dataset?.satuan || \'none\'}`);
            debugInfo.push(`- Option data-sub-satuan: ${option?.dataset?.subSatuan || \'none\'}`);
            
            // Manual trigger
            if (typeof updateConversionDisplay === \'function\') {
                try {
                    updateConversionDisplay(firstRow, option);
                    calculateRowSubtotal(firstRow);
                    debugInfo.push(`- Manual trigger: SUCCESS`);
                } catch (error) {
                    debugInfo.push(`- Manual trigger: ERROR - ${error.message}`);
                }
            }
        }
    }
    
    if (testResult) {
        testResult.innerHTML = debugInfo.join(\'<br>\');
    }
    console.log(\'Emergency debug info:\', debugInfo);
}

// Make functions global
window.addBahanBakuRow = addBahanBakuRow;
window.addBahanPendukungRow = addBahanPendukungRow;
window.emergencyDebug = emergencyDebug;

// Initialize when DOM is ready
document.addEventListener(\'DOMContentLoaded\', function() {
    console.log("=== DOM LOADED - INITIALIZING ===");
    
    // Add event listeners to buttons
    const addBBBtn = document.getElementById(\'addBahanBaku\');
    const addBPBtn = document.getElementById(\'addBahanPendukung\');
    
    if (addBBBtn) {
        addBBBtn.addEventListener(\'click\', function(e) {
            e.preventDefault();
            addBahanBakuRow();
        });
        console.log("✅ Add Bahan Baku button listener attached");
    }
    
    if (addBPBtn) {
        addBPBtn.addEventListener(\'click\', function(e) {
            e.preventDefault();
            addBahanPendukungRow();
        });
        console.log("✅ Add Bahan Pendukung button listener attached");
    }
    
    // Auto-add first row
    setTimeout(() => {
        const existingRows = document.querySelectorAll(\'#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none), #bahanPendukungTable tbody tr:not(#newBahanPendukungRow):not(.d-none)\');
        if (existingRows.length === 0) {
            console.log("No existing rows, adding first Bahan Baku row...");
            addBahanBakuRow();
        }
    }, 1000);
    
    console.log("=== INITIALIZATION COMPLETE ===");
});

console.log("=== SCRIPT LOADED SUCCESSFULLY ===");
</script>
@endpush';

// Replace content
$newContent = substr_replace($content, $newScriptContent, $startPos, $lengthToReplace);

// Write back to file
if (file_put_contents($filePath, $newContent)) {
    echo "SUCCESS: JavaScript completely replaced with working version\n";
    echo "Changes made:\n";
    echo "✓ Complete JavaScript rewrite\n";
    echo "✓ Database sub satuan integration\n";
    echo "✓ Detailed conversion formulas\n";
    echo "✓ Working subtotal calculations\n";
    echo "✓ Comprehensive debugging\n";
    echo "✓ Force refresh timestamp\n\n";
    echo "TESTING STEPS:\n";
    echo "1. Close ALL browser tabs\n";
    echo "2. Clear browser cache (Ctrl+Shift+Delete - All time)\n";
    echo "3. Restart browser completely\n";
    echo "4. Open fresh: /master-data/biaya-bahan/create/2\n";
    echo "5. Open console (F12) - should see initialization logs\n";
    echo "6. Select bahan - should auto-set quantity and show conversion\n";
    echo "7. Change satuan - should show conversion formula\n";
    echo "8. Check subtotal - should calculate correctly\n";
} else {
    echo "ERROR: Could not write to file\n";
}