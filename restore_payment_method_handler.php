<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Restoring payment method handler...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

// Create backup
$backupFile = 'resources/views/transaksi/penjualan/create_backup_' . date('YmdHis') . '.blade.php';
copy($bladeFile, $backupFile);
echo "Created backup: {$backupFile}\n";

// Add the payment method handler at the end of the script
$paymentMethodHandler = '
// Payment method change handler
document.addEventListener("DOMContentLoaded", function() {
    console.log("Initializing payment method handler");
    
    const paymentMethodSelect = document.getElementById("payment_method_jual");
    const sumberDanaWrapper = document.getElementById("sumber_dana_wrapper_jual");
    const sumberDanaSelect = document.getElementById("sumber_dana_jual");
    
    if (paymentMethodSelect && sumberDanaWrapper && sumberDanaSelect) {
        console.log("Payment method elements found");
        
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
        
        // Trigger change on page load to set initial state
        console.log("Triggering initial payment method change");
        paymentMethodSelect.dispatchEvent(new Event("change"));
    } else {
        console.log("Payment method elements not found:");
        console.log("paymentMethodSelect:", !!paymentMethodSelect);
        console.log("sumberDanaWrapper:", !!sumberDanaWrapper);
        console.log("sumberDanaSelect:", !!sumberDanaSelect);
    }
});
';

// Insert the payment method handler before the closing </script> tag
$content = str_replace('</script>', $paymentMethodHandler . "\n</script>", $content);

// Write the fixed content back to the file
file_put_contents($bladeFile, $content);

echo "\n=== RESTORATION SUMMARY ===\n";
echo "1. Added payment method change handler\n";
echo "2. Added console logging for debugging\n";
echo "3. Added page load trigger to set initial state\n";
echo "4. Fixed payment method logic:\n";
echo "   - Tunai (Cash): Show dropdown + Auto-select Kas (112)\n";
echo "   - Transfer: Show dropdown + Auto-select Kas Bank (111)\n";
echo "   - Kredit (Credit): Hide dropdown + Remove required\n";
echo "5. Created backup of original file\n";

echo "\nDone.\n";
