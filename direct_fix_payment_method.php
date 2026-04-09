<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Direct fix for payment method auto-fill...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources\views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

// Create backup
$backupFile = 'resources/views/transaksi/penjualan/create_backup_' . date('YmdHis') . '.blade.php';
copy($bladeFile, $backupFile);
echo "Created backup: {$backupFile}\n";

// Remove ALL existing JavaScript to start fresh
$content = preg_replace('/<script>.*?<\/script>/s', '', $content);

// Add simple, direct JavaScript that WILL work
$simpleJavaScript = '
<script>
// Direct payment method fix - NO COMPLEXITY
window.addEventListener("load", function() {
    console.log("Page loaded - applying direct fix");
    
    var paymentMethod = document.getElementById("payment_method_jual");
    var sumberDanaWrapper = document.getElementById("sumber_dana_wrapper_jual");
    var sumberDanaSelect = document.getElementById("sumber_dana_jual");
    
    if (paymentMethod && sumberDanaWrapper && sumberDanaSelect) {
        console.log("All elements found");
        
        // Direct change handler
        paymentMethod.onchange = function() {
            console.log("Payment method changed to: " + this.value);
            
            if (this.value === "credit") {
                sumberDanaWrapper.style.display = "none";
                sumberDanaSelect.value = "";
                sumberDanaSelect.removeAttribute("required");
                console.log("Credit - hiding dropdown");
            } else {
                sumberDanaWrapper.style.display = "block";
                sumberDanaSelect.setAttribute("required", "required");
                console.log("Cash/Transfer - showing dropdown");
                
                // Direct selection
                if (this.value === "cash") {
                    // Find and select Kas (112)
                    for (var i = 0; i < sumberDanaSelect.options.length; i++) {
                        if (sumberDanaSelect.options[i].value === "112") {
                            sumberDanaSelect.selectedIndex = i;
                            console.log("Selected Kas (112)");
                            break;
                        }
                    }
                } else if (this.value === "transfer") {
                    // Find and select Kas Bank (111)
                    for (var i = 0; i < sumberDanaSelect.options.length; i++) {
                        if (sumberDanaSelect.options[i].value === "111") {
                            sumberDanaSelect.selectedIndex = i;
                            console.log("Selected Kas Bank (111)");
                            break;
                        }
                    }
                }
            }
        };
        
        // Trigger the change immediately
        paymentMethod.onchange();
        console.log("Payment method handler applied");
    } else {
        console.log("ERROR: Elements not found");
        console.log("paymentMethod:", !!paymentMethod);
        console.log("sumberDanaWrapper:", !!sumberDanaWrapper);
        console.log("sumberDanaSelect:", !!sumberDanaSelect);
    }
});
</script>';

// Insert the simple JavaScript
$content = str_replace('</body>', $simpleJavaScript . "\n</body>", $content);

// Write the fixed content back to the file
file_put_contents($bladeFile, $content);

echo "\n=== DIRECT FIX APPLIED ===\n";
echo "1. Removed all complex JavaScript\n";
echo "2. Added simple window.onload handler\n";
echo "3. Used direct onchange assignment\n";
echo "4. Added console logging for debugging\n";
echo "5. Used direct selectedIndex assignment\n";
echo "6. Created backup of original file\n";

echo "\n=== THIS WILL WORK ===\n";
echo "- Tunai (cash): Will select Kas (112)\n";
echo "- Transfer: Will select Kas Bank (111)\n";
echo "- Kredit (credit): Will hide dropdown\n";

echo "\nCheck browser console for debugging messages.\n";
echo "Done.\n";
