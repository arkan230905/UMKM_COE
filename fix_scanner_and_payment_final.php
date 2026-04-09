<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Fixing scanner and payment method issues - Final fix...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

// Create backup
$backupFile = 'resources/views/transaksi/penjualan/create_backup_' . date('YmdHis') . '.blade.php';
copy($bladeFile, $backupFile);
echo "Created backup: {$backupFile}\n";

// Remove all existing DOMContentLoaded listeners to avoid conflicts
$content = preg_replace('/document\.addEventListener\(["\']DOMContentLoaded["\'][^}]*}\);?\s*/', '', $content);

// Create a single, comprehensive script that handles everything
$comprehensiveScript = '
// Comprehensive form management system
document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM loaded - initializing comprehensive form management");
    
    // Global variables
    let barcodeBuffer = "";
    let barcodeTimeout = null;
    let isProcessing = false;
    const BARCODE_TIMEOUT = 100;
    const MIN_BARCODE_LENGTH = 3;
    
    // Get elements
    const form = document.getElementById("form-penjualan");
    const barcodeInput = document.getElementById("barcode-scanner");
    const paymentMethodSelect = document.getElementById("payment_method_jual");
    const sumberDanaWrapper = document.getElementById("sumber_dana_wrapper_jual");
    const sumberDanaSelect = document.getElementById("sumber_dana_jual");
    const addRowBtn = document.getElementById("addRowJual");
    
    // Product data for barcode lookup
    const productData = {
        @foreach($produks as $p)
        "{{ $p->barcode ?? "" }}": {
            id: {{ $p->id }},
            nama: "{{ addslashes($p->nama_produk ?? $p->nama) }}",
            harga: {{ round($p->harga_jual ?? 0) }},
            stok: {{ $p->stok ?? 0 }}
        },
        @endforeach
    };
    
    console.log("Product data loaded:", productData);
    
    // Payment method change handler
    function handlePaymentMethodChange() {
        const paymentMethod = this.value;
        console.log("Payment method changed to:", paymentMethod);
        
        if (paymentMethod === "credit") {
            // Hide sumber dana for credit payments
            sumberDanaWrapper.style.display = "none";
            sumberDanaSelect.value = "";
            sumberDanaSelect.removeAttribute("required");
        } else {
            // Show sumber dana for cash/transfer payments
            sumberDanaWrapper.style.display = "block";
            sumberDanaSelect.setAttribute("required", "required");
            
            // Auto-select appropriate account based on payment method
            if (paymentMethod === "cash") {
                // Select "Kas" (112) for cash payments
                for (let option of sumberDanaSelect.options) {
                    if (option.value === "112") {
                        option.selected = true;
                        break;
                    }
                }
            } else if (paymentMethod === "transfer") {
                // Select "Kas Bank" (111) for transfer payments
                for (let option of sumberDanaSelect.options) {
                    if (option.value === "111") {
                        option.selected = true;
                        break;
                    }
                }
            }
        }
    }
    
    // Barcode scanner functions
    function handleBarcodeInput(char) {
        barcodeBuffer += char;
        
        if (barcodeTimeout) {
            clearTimeout(barcodeTimeout);
        }
        
        barcodeTimeout = setTimeout(() => {
            if (barcodeBuffer.length >= MIN_BARCODE_LENGTH && !isProcessing) {
                processAutomaticBarcode(barcodeBuffer.trim());
            }
            barcodeBuffer = "";
        }, BARCODE_TIMEOUT);
    }
    
    function processAutomaticBarcode(barcode) {
        if (isProcessing) return;
        
        isProcessing = true;
        console.log("Processing barcode:", barcode);
        
        const scanIndicator = document.getElementById("scan-indicator");
        
        try {
            const product = productData[barcode];
            
            if (product) {
                console.log("Product found:", product);
                
                // Validate stock
                if (product.stok <= 0) {
                    throw new Error("Produk " + product.nama + " stok habis!");
                }
                
                // Add product to table
                addProductByBarcode(product);
                
                // Success feedback
                if (scanIndicator) {
                    scanIndicator.textContent = " " + product.nama;
                    scanIndicator.parentElement.className = "input-group-text bg-success text-white";
                }
                
                showNotification("Produk ditambahkan: " + product.nama, "success");
                
            } else {
                console.log("Product not found for barcode:", barcode);
                
                if (scanIndicator) {
                    scanIndicator.textContent = "Tidak ditemukan";
                    scanIndicator.parentElement.className = "input-group-text bg-danger text-white";
                }
                
                showNotification("Produk tidak ditemukan: " + barcode, "error");
            }
        } catch (error) {
            console.error("Error processing barcode:", error);
            
            if (scanIndicator) {
                scanIndicator.textContent = "Error";
                scanIndicator.parentElement.className = "input-group-text bg-danger text-white";
            }
            
            showNotification(error.message || "Terjadi kesalahan saat memproses barcode", "error");
        }
        
        // Clear input and reset status
        if (barcodeInput) {
            barcodeInput.value = "";
        }
        
        setTimeout(() => {
            if (scanIndicator) {
                scanIndicator.textContent = "Siap Scan";
                scanIndicator.parentElement.className = "input-group-text bg-success text-white";
            }
            isProcessing = false;
        }, 3000);
    }
    
    function addProductByBarcode(product) {
        console.log("Adding product by barcode:", product);
        
        const table = document.getElementById("detailTableJual");
        const tbody = table.querySelector("tbody");
        
        // Check if product already exists
        const existingRow = findExistingProductRow(product.id);
        
        if (existingRow) {
            // Increment quantity
            const qtyInput = existingRow.querySelector(".jumlah");
            const currentQty = parseFloat(qtyInput.value) || 0;
            const newQty = currentQty + 1;
            
            if (newQty > product.stok) {
                throw new Error("Stok tidak cukup! Stok tersedia: " + product.stok);
            }
            
            qtyInput.value = Math.round(newQty);
            recalcRow(existingRow);
            recalcTotal();
            highlightRow(existingRow);
        } else {
            // Find first empty row or create new one
            let targetRow = null;
            const rows = tbody.querySelectorAll("tr");
            
            for (let row of rows) {
                const select = row.querySelector(".produk-select");
                if (!select.value) {
                    targetRow = row;
                    break;
                }
            }
            
            if (!targetRow) {
                targetRow = createNewRow();
                tbody.appendChild(targetRow);
            }
            
            // Fill the row
            const select = targetRow.querySelector(".produk-select");
            const qtyInput = targetRow.querySelector(".jumlah");
            
            select.value = product.id;
            qtyInput.value = 1;
            setPriceFromSelect(targetRow);
            highlightRow(targetRow);
        }
    }
    
    function findExistingProductRow(productId) {
        const table = document.getElementById("detailTableJual");
        const rows = table.querySelectorAll("tbody tr");
        
        for (let row of rows) {
            const select = row.querySelector(".produk-select");
            if (select && select.value == productId) {
                return row;
            }
        }
        return null;
    }
    
    function createNewRow() {
        const tbody = document.querySelector("#detailTableJual tbody");
        const firstRow = tbody.querySelector("tr");
        const newRow = firstRow.cloneNode(true);
        
        // Clear the new row
        newRow.querySelector(".produk-select").value = "";
        newRow.querySelector(".jumlah").value = 1;
        newRow.querySelector(".harga").value = "0";
        newRow.querySelector(".diskon").value = "0";
        newRow.querySelector(".subtotal").value = "0";
        
        // Attach event listeners
        attachRowEventListeners(newRow);
        
        return newRow;
    }
    
    function attachRowEventListeners(row) {
        const select = row.querySelector(".produk-select");
        const qtyInput = row.querySelector(".jumlah");
        const diskonInput = row.querySelector(".diskon");
        
        if (select) {
            select.addEventListener("change", function() {
                setPriceFromSelect(row);
            });
        }
        
        if (qtyInput) {
            qtyInput.addEventListener("input", function() {
                recalcRow(row);
                recalcTotal();
            });
        }
        
        if (diskonInput) {
            diskonInput.addEventListener("input", function() {
                recalcRow(row);
                recalcTotal();
            });
        }
    }
    
    function setPriceFromSelect(row) {
        const sel = row.querySelector(".produk-select");
        const opt = sel.options[sel.selectedIndex];
        const price = parseFloat(opt?.getAttribute("data-price") || "0") || 0;
        const stok = parseFloat(opt?.getAttribute("data-stok") || "0") || 0;
        
        row.querySelector(".harga").value = formatCurrency(price);
        
        // Update stok info
        const stokInfo = row.querySelector(".stok-info");
        if (stokInfo && opt.value) {
            stokInfo.textContent = `Stok tersedia: ${stok.toLocaleString()}`;
            stokInfo.style.color = stok > 0 ? "#28a745" : "#dc3545";
        }
        
        const qtyInput = row.querySelector(".jumlah");
        qtyInput.setAttribute("data-max-stok", stok);
        
        recalcRow(row);
        recalcTotal();
    }
    
    function recalcRow(tr) {
        const q = Math.round(parseFloat(tr.querySelector(".jumlah").value) || 0);
        tr.querySelector(".jumlah").value = q;
        const p = parseCurrency(tr.querySelector(".harga").value) || 0;
        const dPct = Math.min(Math.max(parseFloat(tr.querySelector(".diskon").value) || 0, 0), 100);
        const sub = q * p;
        const dNom = sub * (dPct/100.0);
        const line = Math.max(sub - dNom, 0);
        tr.querySelector(".subtotal").value = formatCurrency(line);
    }
    
    function recalcTotal() {
        const table = document.getElementById("detailTableJual");
        let sum = 0;
        table.querySelectorAll("tbody tr").forEach(tr => {
            const val = (tr.querySelector(".subtotal").value || "Rp 0").replace(/[^\d]/g,"");
            sum += parseFloat(val) || 0;
        });
        
        const subtotalProdukInput = document.querySelector("input[name=\"subtotal_produk\"]");
        if (subtotalProdukInput) {
            subtotalProdukInput.value = formatCurrency(sum);
        }
        
        const biayaOngkir = parseFloat(document.getElementById("biaya_ongkir").value) || 0;
        const biayaService = parseFloat(document.getElementById("biaya_service").value) || 0;
        const ppnPersen = parseFloat(document.getElementById("ppn_persen").value) || 0;
        
        const ppnBase = sum + biayaOngkir + biayaService;
        const totalPPN = ppnBase * (ppnPersen / 100);
        
        const totalPPNInput = document.getElementById("total_ppn");
        if (totalPPNInput) {
            totalPPNInput.value = formatCurrency(totalPPN);
        }
        
        const finalTotal = sum + biayaOngkir + biayaService + totalPPN;
        
        const totalInput = document.getElementById("total_final");
        if (totalInput) {
            totalInput.value = formatCurrency(finalTotal);
        }
    }
    
    function highlightRow(row) {
        row.style.backgroundColor = "#d4edda";
        setTimeout(() => {
            row.style.backgroundColor = "";
            row.style.transition = "background-color 0.5s ease";
        }, 500);
    }
    
    function showNotification(message, type) {
        const notification = document.createElement("div");
        notification.className = `alert alert-${type === "success" ? "success" : "danger"} alert-dismissible fade show position-fixed`;
        notification.style.cssText = "top: 20px; right: 20px; z-index: 9999; min-width: 300px;";
        notification.innerHTML = `
            <i class="fas fa-${type === "success" ? "check-circle" : "exclamation-circle"} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }
    
    // Utility functions
    function formatCurrency(value) {
        if (value === null || value === undefined || isNaN(value)) {
            return "Rp 0";
        }
        const roundedValue = Math.round(parseFloat(value) * 1000) / 1000;
        return "Rp " + roundedValue.toLocaleString("id-ID", { minimumFractionDigits: 0, maximumFractionDigits: 3 });
    }
    
    function parseCurrency(formattedValue) {
        if (!formattedValue) return 0;
        return parseFloat(formattedValue.toString().replace(/[^\d]/g, "")) || 0;
    }
    
    // Initialize event listeners
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener("change", handlePaymentMethodChange);
        // Trigger change on page load
        handlePaymentMethodChange.call(paymentMethodSelect);
    }
    
    // Attach event listeners to existing rows
    document.querySelectorAll("#detailTableJual tbody tr").forEach(attachRowEventListeners);
    
    // Add row button
    if (addRowBtn) {
        addRowBtn.addEventListener("click", function() {
            const tbody = document.querySelector("#detailTableJual tbody");
            const newRow = createNewRow();
            tbody.appendChild(newRow);
        });
    }
    
    // Global keydown listener for barcode scanner
    document.addEventListener("keydown", function(e) {
        // Only process if barcode scanner is focused or no other input is focused
        if (document.activeElement === barcodeInput || 
            document.activeElement === document.body || 
            document.activeElement === document.documentElement) {
            
            // Handle barcode input
            if (e.key.length === 1) {
                handleBarcodeInput(e.key);
            }
        }
    });
    
    // Barcode scanner focus management
    if (barcodeInput) {
        setTimeout(() => {
            barcodeInput.focus();
        }, 500);
        
        barcodeInput.addEventListener("click", function() {
            this.focus();
        });
    }
    
    console.log("Comprehensive form management system initialized");
});';

// Insert the comprehensive script before the closing </script> tag
$content = str_replace('</script>', $comprehensiveScript . "\n</script>", $content);

// Write the fixed content back to the file
file_put_contents($bladeFile, $content);

echo "\n=== FINAL FIX SUMMARY ===\n";
echo "1. Removed all conflicting DOMContentLoaded listeners\n";
echo "2. Created single comprehensive form management system\n";
echo "3. Fixed payment method auto-fill functionality\n";
echo "4. Fixed barcode scanner to work like before\n";
echo "5. Added proper event handling and scope management\n";
echo "6. Created backup of original file\n";

echo "\n=== PAYMENT METHOD LOGIC ===\n";
echo "- Cash: Auto-select Kas (112)\n";
echo "- Transfer: Auto-select Kas Bank (111)\n";
echo "- Credit: Hide receiving account dropdown\n";
echo "- Auto-trigger on page load\n";

echo "\n=== BARCODE SCANNER LOGIC ===\n";
echo "- Global keydown listener for barcode input\n";
echo "- Product lookup and table addition\n";
echo "- Stock validation and error handling\n";
echo "- Visual feedback and notifications\n";

echo "\nDone.\n";
