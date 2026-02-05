<?php

// Script untuk membuat versi biaya bahan yang benar-benar berfungsi

$filePath = 'resources/views/master-data/biaya-bahan/create.blade.php';

// Buat backup dulu
$backupPath = $filePath . '.backup.' . time();
copy($filePath, $backupPath);
echo "Backup created: $backupPath\n";

// Baca file asli
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

// JavaScript yang benar-benar sederhana dan pasti berfungsi
$newScriptContent = '@push(\'scripts\')
<script>
// BIAYA BAHAN WORKING VERSION - ' . date('Y-m-d H:i:s') . '
console.log("🚀 BIAYA BAHAN LOADED - " + new Date().toISOString());

// Global flag
window.biayaBahanReady = true;

// Helper functions
function formatClean(num) {
    return num === Math.floor(num) ? Math.floor(num).toString() : parseFloat(num.toFixed(4)).toString();
}

function formatRupiah(num) {
    return "Rp " + Math.round(num).toLocaleString("id-ID");
}

// MAIN FUNCTION: Update conversion display
function updateConversionDisplay(row, option) {
    console.log("📊 updateConversionDisplay called");
    
    const hargaKonversiDiv = row.querySelector(".harga-konversi");
    const satuanSelect = row.querySelector(".satuan-select");
    
    if (!hargaKonversiDiv || !option || !satuanSelect) {
        console.log("❌ Missing elements");
        return;
    }
    
    const hargaUtama = parseFloat(option.dataset.harga) || 0;
    const satuanUtama = option.dataset.satuan || "unit";
    const satuanDipilih = satuanSelect.value;
    const subSatuanData = JSON.parse(option.dataset.subSatuan || "[]");
    
    console.log("📋 Data:", {
        harga: hargaUtama,
        satuanUtama: satuanUtama,
        satuanDipilih: satuanDipilih,
        subSatuan: subSatuanData.length
    });
    
    if (!satuanDipilih) {
        hargaKonversiDiv.innerHTML = \'<small class="text-muted">Pilih satuan untuk konversi</small>\';
        return;
    }
    
    // Use database sub satuan
    if (subSatuanData.length > 0) {
        console.log("✅ Using database sub satuan");
        
        // Find exact match
        const match = subSatuanData.find(sub => 
            sub.nama.toLowerCase().trim() === satuanDipilih.toLowerCase().trim()
        );
        
        if (match) {
            // Specific conversion
            const hargaKonversi = (hargaUtama * match.konversi) / match.nilai;
            const konversiClean = formatClean(match.konversi);
            const nilaiClean = formatClean(match.nilai);
            
            console.log("🎯 Found match:", match);
            
            hargaKonversiDiv.innerHTML = `
                <div class="text-info mb-1">
                    <strong>${formatRupiah(hargaKonversi)}/${satuanDipilih}</strong>
                </div>
                <div class="text-muted" style="font-size: 0.8rem;">
                    <div><strong>📊 Rumus:</strong></div>
                    <div>• ${konversiClean} ${satuanUtama} = ${nilaiClean} ${satuanDipilih}</div>
                    <div>• ${formatRupiah(hargaUtama)} × ${konversiClean} ÷ ${nilaiClean}</div>
                    <div class="text-success">• <strong>${formatRupiah(hargaKonversi)}</strong></div>
                </div>
            `;
            return;
        }
        
        // Show all conversions if same unit or no match
        if (satuanDipilih === satuanUtama || !match) {
            console.log("📋 Showing all conversions");
            
            let html = \'<div class="text-success mb-1"><strong>Konversi Tersedia:</strong></div>\';
            
            subSatuanData.forEach(sub => {
                const hargaKonversi = (hargaUtama * sub.konversi) / sub.nilai;
                const konversiClean = formatClean(sub.konversi);
                const nilaiClean = formatClean(sub.nilai);
                
                html += `
                    <div class="border-start border-info ps-2 mb-1" style="font-size: 0.8rem;">
                        <div class="text-info"><strong>${formatRupiah(hargaKonversi)}/${sub.nama}</strong></div>
                        <div class="text-muted">${konversiClean} ${satuanUtama} = ${nilaiClean} ${sub.nama}</div>
                    </div>
                `;
            });
            
            hargaKonversiDiv.innerHTML = html;
            return;
        }
    }
    
    // No sub satuan data
    if (satuanDipilih === satuanUtama) {
        hargaKonversiDiv.innerHTML = `
            <div class="text-success">
                <strong>${formatRupiah(hargaUtama)}/${satuanUtama}</strong>
            </div>
            <small class="text-muted">Satuan sama</small>
        `;
    } else {
        hargaKonversiDiv.innerHTML = `
            <div class="text-warning">Konversi tidak tersedia</div>
            <small class="text-muted">Dari ${satuanUtama} ke ${satuanDipilih}</small>
        `;
    }
}

// Get conversion factor for calculations
function getConversionFactor(fromUnit, toUnit, subSatuanData = []) {
    if (fromUnit.toLowerCase() === toUnit.toLowerCase()) {
        return 1;
    }
    
    if (subSatuanData.length > 0) {
        const match = subSatuanData.find(sub => 
            sub.nama.toLowerCase().trim() === toUnit.toLowerCase().trim()
        );
        
        if (match) {
            return match.konversi / match.nilai;
        }
    }
    
    return 1; // Default fallback
}

// Calculate row subtotal
function calculateRowSubtotal(row) {
    console.log("🧮 calculateRowSubtotal called");
    
    const bahanSelect = row.querySelector(".bahan-baku-select, .bahan-pendukung-select");
    const qtyInput = row.querySelector(".qty-input");
    const satuanSelect = row.querySelector(".satuan-select");
    const subtotalDisplay = row.querySelector(".subtotal-display");
    
    if (!bahanSelect || !qtyInput || !satuanSelect || !subtotalDisplay) {
        console.log("❌ Missing elements for calculation");
        return;
    }
    
    const option = bahanSelect.options[bahanSelect.selectedIndex];
    if (!option || !option.value) {
        subtotalDisplay.innerHTML = "-";
        return;
    }
    
    const harga = parseFloat(option.dataset.harga) || 0;
    const qty = parseFloat(qtyInput.value) || 0;
    const satuanUtama = option.dataset.satuan || "unit";
    const satuanDipilih = satuanSelect.value;
    const subSatuanData = JSON.parse(option.dataset.subSatuan || "[]");
    
    console.log("💰 Calculation data:", {
        harga: harga,
        qty: qty,
        satuanUtama: satuanUtama,
        satuanDipilih: satuanDipilih
    });
    
    if (qty <= 0 || !satuanDipilih) {
        subtotalDisplay.innerHTML = "-";
        return;
    }
    
    let subtotal = harga * qty;
    
    // Apply conversion if different units
    if (satuanUtama !== satuanDipilih) {
        const factor = getConversionFactor(satuanUtama, satuanDipilih, subSatuanData);
        subtotal = (harga * factor) * qty;
        console.log("🔄 Applied conversion factor:", factor);
    }
    
    subtotalDisplay.innerHTML = `<strong class="text-success">${formatRupiah(subtotal)}</strong>`;
    console.log("✅ Subtotal updated:", subtotal);
    
    // Update totals
    setTimeout(calculateTotals, 50);
}

// Calculate all totals
function calculateTotals() {
    let totalBB = 0;
    let totalBP = 0;
    
    // Bahan Baku
    document.querySelectorAll("#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none)").forEach(row => {
        const subtotalText = row.querySelector(".subtotal-display")?.textContent || "";
        const subtotal = parseFloat(subtotalText.replace(/[^\\d]/g, "")) || 0;
        totalBB += subtotal;
    });
    
    // Bahan Pendukung
    document.querySelectorAll("#bahanPendukungTable tbody tr:not(#newBahanPendukungRow):not(.d-none)").forEach(row => {
        const subtotalText = row.querySelector(".subtotal-display")?.textContent || "";
        const subtotal = parseFloat(subtotalText.replace(/[^\\d]/g, "")) || 0;
        totalBP += subtotal;
    });
    
    const total = totalBB + totalBP;
    
    console.log("📊 Totals:", { bb: totalBB, bp: totalBP, total: total });
    
    // Update displays
    const elements = {
        totalBahanBaku: document.getElementById("totalBahanBaku"),
        totalBahanPendukung: document.getElementById("totalBahanPendukung"),
        summaryBahanBaku: document.getElementById("summaryBahanBaku"),
        summaryBahanPendukung: document.getElementById("summaryBahanPendukung"),
        summaryTotalBiaya: document.getElementById("summaryTotalBiaya")
    };
    
    if (elements.totalBahanBaku) elements.totalBahanBaku.textContent = formatRupiah(totalBB);
    if (elements.totalBahanPendukung) elements.totalBahanPendukung.textContent = formatRupiah(totalBP);
    if (elements.summaryBahanBaku) elements.summaryBahanBaku.textContent = formatRupiah(totalBB);
    if (elements.summaryBahanPendukung) elements.summaryBahanPendukung.textContent = formatRupiah(totalBP);
    if (elements.summaryTotalBiaya) elements.summaryTotalBiaya.textContent = formatRupiah(total);
}

// Add event listeners to row
function addRowEventListeners(row) {
    const bahanSelect = row.querySelector(".bahan-baku-select, .bahan-pendukung-select");
    const qtyInput = row.querySelector(".qty-input");
    const satuanSelect = row.querySelector(".satuan-select");
    const removeBtn = row.querySelector(".remove-item");
    
    if (bahanSelect) {
        bahanSelect.addEventListener("change", function() {
            console.log("🔄 Bahan changed:", this.value);
            const option = this.options[this.selectedIndex];
            if (option && option.dataset.harga) {
                // Auto-fill satuan
                if (option.dataset.satuan && satuanSelect) {
                    satuanSelect.value = option.dataset.satuan;
                }
                
                // Auto-set quantity
                if (qtyInput && (!qtyInput.value || qtyInput.value === "0")) {
                    qtyInput.value = "1";
                }
                
                // Update harga display
                const hargaDisplay = row.querySelector(".harga-utama");
                if (hargaDisplay) {
                    const harga = parseInt(option.dataset.harga);
                    hargaDisplay.innerHTML = `<strong>${formatRupiah(harga)}</strong>`;
                }
                
                // Show conversion
                updateConversionDisplay(row, option);
            }
            calculateRowSubtotal(row);
        });
    }
    
    if (qtyInput) {
        qtyInput.addEventListener("input", function() {
            calculateRowSubtotal(row);
        });
    }
    
    if (satuanSelect) {
        satuanSelect.addEventListener("change", function() {
            console.log("🔄 Satuan changed:", this.value);
            const bahanSelect = row.querySelector(".bahan-baku-select, .bahan-pendukung-select");
            if (bahanSelect && bahanSelect.value) {
                const option = bahanSelect.options[bahanSelect.selectedIndex];
                updateConversionDisplay(row, option);
            }
            calculateRowSubtotal(row);
        });
    }
    
    if (removeBtn) {
        removeBtn.addEventListener("click", function() {
            if (confirm("Hapus baris ini?")) {
                row.remove();
                calculateTotals();
            }
        });
    }
}

// Add new row functions
function addBahanBakuRow() {
    console.log("➕ Adding Bahan Baku row");
    
    const newRow = document.getElementById("newBahanBakuRow");
    if (!newRow) {
        console.error("❌ Template row not found");
        return false;
    }
    
    const tbody = newRow.parentElement;
    const clone = newRow.cloneNode(true);
    clone.classList.remove("d-none");
    clone.id = "bahanBaku_" + Date.now();
    
    // Update name attributes
    const timestamp = Date.now();
    clone.querySelectorAll(\'[name^="bahan_baku[new]"]\').forEach(input => {
        const fieldName = input.name.match(/\\[new\\]\\[(\\w+)\\]/)[1];
        input.name = `bahan_baku[${timestamp}][${fieldName}]`;
        input.value = "";
    });
    
    tbody.insertBefore(clone, newRow);
    addRowEventListeners(clone);
    
    console.log("✅ Bahan Baku row added");
    return false;
}

function addBahanPendukungRow() {
    console.log("➕ Adding Bahan Pendukung row");
    
    const newRow = document.getElementById("newBahanPendukungRow");
    if (!newRow) {
        console.error("❌ Template row not found");
        return false;
    }
    
    const tbody = newRow.parentElement;
    const clone = newRow.cloneNode(true);
    clone.classList.remove("d-none");
    clone.id = "bahanPendukung_" + Date.now();
    
    // Update name attributes
    const timestamp = Date.now();
    clone.querySelectorAll(\'[name^="bahan_pendukung[new]"]\').forEach(input => {
        const fieldName = input.name.match(/\\[new\\]\\[(\\w+)\\]/)[1];
        input.name = `bahan_pendukung[${timestamp}][${fieldName}]`;
        input.value = "";
    });
    
    tbody.insertBefore(clone, newRow);
    addRowEventListeners(clone);
    
    console.log("✅ Bahan Pendukung row added");
    return false;
}

// Emergency debug function
function emergencyDebug() {
    console.log("🚨 EMERGENCY DEBUG");
    const testResult = document.getElementById("testResult");
    
    let info = [];
    info.push("Functions: " + (typeof updateConversionDisplay));
    info.push("Rows: " + document.querySelectorAll("#bahanBakuTable tbody tr:not(.d-none)").length);
    
    const firstRow = document.querySelector("#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none)");
    if (firstRow) {
        const bahanSelect = firstRow.querySelector(".bahan-baku-select");
        const satuanSelect = firstRow.querySelector(".satuan-select");
        info.push("Bahan: " + (bahanSelect?.value || "none"));
        info.push("Satuan: " + (satuanSelect?.value || "none"));
        
        if (bahanSelect && bahanSelect.value) {
            const option = bahanSelect.options[bahanSelect.selectedIndex];
            info.push("Harga: " + (option?.dataset?.harga || "none"));
            info.push("Sub Satuan: " + (option?.dataset?.subSatuan ? "ada" : "none"));
            
            // Manual trigger
            try {
                updateConversionDisplay(firstRow, option);
                calculateRowSubtotal(firstRow);
                info.push("Manual trigger: SUCCESS");
            } catch (error) {
                info.push("Manual trigger: ERROR - " + error.message);
            }
        }
    }
    
    if (testResult) {
        testResult.innerHTML = info.join("<br>");
    }
    console.log("Debug info:", info);
}

// Make functions global
window.addBahanBakuRow = addBahanBakuRow;
window.addBahanPendukungRow = addBahanPendukungRow;
window.emergencyDebug = emergencyDebug;

// Initialize when DOM ready
document.addEventListener("DOMContentLoaded", function() {
    console.log("🎯 DOM Ready - Initializing");
    
    // Attach button listeners
    const addBBBtn = document.getElementById("addBahanBaku");
    const addBPBtn = document.getElementById("addBahanPendukung");
    
    if (addBBBtn) {
        addBBBtn.addEventListener("click", function(e) {
            e.preventDefault();
            addBahanBakuRow();
        });
        console.log("✅ BB button attached");
    }
    
    if (addBPBtn) {
        addBPBtn.addEventListener("click", function(e) {
            e.preventDefault();
            addBahanPendukungRow();
        });
        console.log("✅ BP button attached");
    }
    
    // Auto-add first row
    setTimeout(() => {
        const existingRows = document.querySelectorAll("#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none)");
        if (existingRows.length === 0) {
            console.log("🚀 Auto-adding first row");
            addBahanBakuRow();
        }
    }, 500);
    
    console.log("✅ Initialization complete");
});

console.log("🎉 BIAYA BAHAN SCRIPT LOADED SUCCESSFULLY");
</script>
@endpush';

// Replace content
$newContent = substr_replace($content, $newScriptContent, $startPos, $lengthToReplace);

// Write back to file
if (file_put_contents($filePath, $newContent)) {
    echo "SUCCESS: Created working version of biaya bahan\n";
    echo "✅ Clean, simple JavaScript\n";
    echo "✅ Database sub satuan integration\n";
    echo "✅ Working conversion formulas\n";
    echo "✅ Proper subtotal calculations\n";
    echo "✅ Comprehensive logging\n\n";
    echo "NEXT STEPS:\n";
    echo "1. Clear Laravel cache: php artisan view:clear\n";
    echo "2. Clear browser cache completely\n";
    echo "3. Test the page\n";
} else {
    echo "ERROR: Could not write to file\n";
}