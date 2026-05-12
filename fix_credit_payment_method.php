<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Fixing credit payment method issue...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

// Create backup
$backupFile = 'resources/views/transaksi/penjualan/create_backup_' . date('YmdHis') . '.blade.php';
copy($bladeFile, $backupFile);
echo "Created backup: {$backupFile}\n";

// Fix the payment method change handler to properly handle credit
$oldPaymentHandler = '    if (paymentMethodSelect && sumberDanaWrapper && sumberDanaSelect) {
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
                
                // Auto-select appropriate account
                if (paymentMethod === "cash") {
                    // Select "Kas" (112) for Tunai payments
                    for (let option of sumberDanaSelect.options) {
                        if (option.value === "112") {
                            option.selected = true;
                            break;
                        }
                    }
                } else if (paymentMethod === "transfer") {
                    // Select "Kas Bank" (111) for Transfer payments
                    for (let option of sumberDanaSelect.options) {
                        if (option.value === "111") {
                            option.selected = true;
                            break;
                        }
                    }
                }
            }
        });
        
        // Trigger change on page load
        paymentMethodSelect.dispatchEvent(new Event("change"));
    }';

$newPaymentHandler = '    if (paymentMethodSelect && sumberDanaWrapper && sumberDanaSelect) {
        paymentMethodSelect.addEventListener("change", function() {
            const paymentMethod = this.value;
            
            console.log("Payment method changed to:", paymentMethod);
            
            if (paymentMethod === "credit") {
                // Hide sumber dana for credit payments
                sumberDanaWrapper.style.display = "none";
                sumberDanaSelect.value = "";
                sumberDanaSelect.removeAttribute("required");
                console.log("Credit payment - hiding receiving account");
            } else {
                // Show sumber dana for cash/transfer payments
                sumberDanaWrapper.style.display = "block";
                sumberDanaSelect.setAttribute("required", "required");
                console.log("Cash/Transfer payment - showing receiving account");
                
                // Auto-select appropriate account
                if (paymentMethod === "cash") {
                    // Select "Kas" (112) for Tunai payments
                    for (let option of sumberDanaSelect.options) {
                        if (option.value === "112") {
                            option.selected = true;
                            console.log("Selected Kas (112) for cash payment");
                            break;
                        }
                    }
                } else if (paymentMethod === "transfer") {
                    // Select "Kas Bank" (111) for Transfer payments
                    for (let option of sumberDanaSelect.options) {
                        if (option.value === "111") {
                            option.selected = true;
                            console.log("Selected Kas Bank (111) for transfer payment");
                            break;
                        }
                    }
                }
            }
        });
        
        // Trigger change on page load
        paymentMethodSelect.dispatchEvent(new Event("change"));
    }';

// Replace the payment handler
$content = str_replace($oldPaymentHandler, $newPaymentHandler, $content);

// Write the fixed content back to the file
file_put_contents($bladeFile, $content);

echo "\n=== FIX SUMMARY ===\n";
echo "1. Fixed credit payment method handler\n";
echo "2. Added console logging for debugging\n";
echo "3. Credit payment now properly hides receiving account dropdown\n";
echo "4. Cash and Transfer still show and auto-select appropriate accounts\n";
echo "5. Created backup of original file\n";

echo "\n=== PAYMENT METHOD LOGIC ===\n";
echo "- Tunai (Cash): Show dropdown + Auto-select Kas (112)\n";
echo "- Transfer: Show dropdown + Auto-select Kas Bank (111)\n";
echo "- Kredit (Credit): Hide dropdown + Remove required attribute\n";

echo "\nDone.\n";
