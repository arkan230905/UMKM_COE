<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Fixing payment method and barcode scanner issues...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources\views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

// Create backup
$backupFile = 'resources/views/transaksi/penjualan/create_backup_' . date('YmdHis') . '.blade.php';
copy($bladeFile, $backupFile);
echo "Created backup: {$backupFile}\n";

// Add payment method change handler and fix barcode scanner
$additionalScript = '
    // Payment method change handler
    const paymentMethodSelect = document.getElementById("payment_method_jual");
    const sumberDanaWrapper = document.getElementById("sumber_dana_wrapper_jual");
    const sumberDanaSelect = document.getElementById("sumber_dana_jual");
    
    if (paymentMethodSelect && sumberDanaWrapper && sumberDanaSelect) {
        paymentMethodSelect.addEventListener("change", function() {
            const paymentMethod = this.value;
            
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
            
            console.log("Payment method changed to:", paymentMethod);
        });
        
        // Initialize on page load
        paymentMethodSelect.dispatchEvent(new Event("change"));
    }
    
    // Fix barcode scanner product addition
    function addProductByBarcode(product) {
        console.log("Adding product by barcode:", product);
        
        const table = document.getElementById("detailTableJual");
        const tbody = table.querySelector("tbody");
        
        // Check if product already exists in table
        const existingRow = findExistingProductRow(product.id);
        
        if (existingRow) {
            // Increment quantity
            const qtyInput = existingRow.querySelector(".jumlah");
            const currentQty = parseFloat(qtyInput.value) || 0;
            const newQty = currentQty + 1;
            
            // Check stock
            if (newQty > product.stok) {
                throw new Error("Stok tidak cukup! Stok tersedia: " + product.stok);
            }
            
            qtyInput.value = Math.round(newQty);
            recalcRow(existingRow);
            recalcTotal();
            
            // Highlight row
            highlightRow(existingRow);
        } else {
            // Find first empty row or create new one
            let targetRow = null;
            const rows = tbody.querySelectorAll("tr");
            
            // Look for empty row (no product selected)
            for (let row of rows) {
                const select = row.querySelector(".produk-select");
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
            const select = targetRow.querySelector(".produk-select");
            const qtyInput = targetRow.querySelector(".jumlah");
            const hargaInput = targetRow.querySelector(".harga");
            const diskonInput = targetRow.querySelector(".diskon");
            
            // Set product selection
            select.value = product.id;
            
            // Set quantity to 1
            qtyInput.value = 1;
            
            // Set price (will trigger recalc)
            setPriceFromSelect(targetRow);
            
            // Highlight row
            highlightRow(targetRow);
        }
    }
    
    // Helper function to create new row
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
        
        // Add event listeners to new row
        attachRowEventListeners(newRow);
        
        return newRow;
    }
    
    // Helper function to attach event listeners to a row
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
    
    // Helper function to find existing product row
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
    
    // Helper function to set price from select
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
        
        // Set max qty to available stock
        const qtyInput = row.querySelector(".jumlah");
        qtyInput.setAttribute("data-max-stok", stok);
        
        recalcRow(row);
        recalcTotal();
    }
    
    // Helper function to recalc row
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
    
    // Helper function to recalc total
    function recalcTotal() {
        const table = document.getElementById("detailTableJual");
        let sum = 0;
        table.querySelectorAll("tbody tr").forEach(tr => {
            const val = (tr.querySelector(".subtotal").value || "Rp 0").replace(/[^\d]/g,"");
            sum += parseFloat(val) || 0;
        });
        
        // Update subtotal produk
        const subtotalProdukInput = document.querySelector("input[name=\"subtotal_produk\"]");
        if (subtotalProdukInput) {
            subtotalProdukInput.value = formatCurrency(sum);
        }
        
        // Get additional costs
        const biayaOngkir = parseFloat(document.getElementById("biaya_ongkir").value) || 0;
        const biayaService = parseFloat(document.getElementById("biaya_service").value) || 0;
        const ppnPersen = parseFloat(document.getElementById("ppn_persen").value) || 0;
        
        // Calculate PPN base (subtotal + ongkir + service)
        const ppnBase = sum + biayaOngkir + biayaService;
        const totalPPN = ppnBase * (ppnPersen / 100);
        
        // Update PPN
        const totalPPNInput = document.getElementById("total_ppn");
        if (totalPPNInput) {
            totalPPNInput.value = formatCurrency(totalPPN);
        }
        
        // Calculate final total
        const finalTotal = sum + biayaOngkir + biayaService + totalPPN;
        
        // Update total
        const totalInput = document.getElementById("total_final");
        if (totalInput) {
            totalInput.value = formatCurrency(finalTotal);
        }
    }
    
    // Helper function to highlight row
    function highlightRow(row) {
        row.style.backgroundColor = "#d4edda";
        setTimeout(() => {
            row.style.backgroundColor = "";
            row.style.transition = "background-color 0.5s ease";
        }, 500);
    }
    
    // Attach event listeners to existing rows
    document.querySelectorAll("#detailTableJual tbody tr").forEach(attachRowEventListeners);
    
    // Add row button functionality
    const addRowBtn = document.getElementById("addRowJual");
    if (addRowBtn) {
        addRowBtn.addEventListener("click", function() {
            const tbody = document.querySelector("#detailTableJual tbody");
            const newRow = createNewRow();
            tbody.appendChild(newRow);
        });
    }
    
    console.log("Payment method and barcode scanner fixes applied");';

// Insert the additional script before the closing </script> tag
$content = str_replace('</script>', $additionalScript . "\n</script>", $content);

// Write the fixed content back to the file
file_put_contents($bladeFile, $content);

echo "\n=== FIX SUMMARY ===\n";
echo "1. Added payment method change event handler\n";
echo "2. Fixed barcode scanner to add products to table\n";
echo "3. Added automatic account selection based on payment method\n";
echo "4. Fixed product addition functions\n";
echo "5. Added row creation and event attachment functions\n";
echo "6. Created backup of original file\n";

echo "\n=== PAYMENT METHOD LOGIC ===\n";
echo "- Cash: Auto-select Kas (112)\n";
echo "- Transfer: Auto-select Kas Bank (111)\n";
echo "- Credit: Hide receiving account dropdown\n";

echo "\n=== BARCODE SCANNER LOGIC ===\n";
echo "- Scanner will add products to table\n";
echo "- Existing products will increment quantity\n";
echo "- New products will create new rows\n";
echo "- Stock validation will be enforced\n";

echo "\nDone.\n";
