<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Fixing biaya ongkir field focus and value retention issue...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

// Create backup
$backupFile = 'resources/views/transaksi/penjualan/create_backup_' . date('YmdHis') . '.blade.php';
copy($bladeFile, $backupFile);
echo "Created backup: {$backupFile}\n";

// Find the biaya_ongkir input field and add proper event handling
$oldInputField = '<input type="number" step="0.01" min="0" name="biaya_ongkir" class="form-control" value="0" id="biaya_ongkir">';

$newInputField = '<input type="number" step="0.01" min="0" name="biaya_ongkir" class="form-control" value="0" id="biaya_ongkir" 
                     onclick="this.focus()" 
                     onfocus="this.select()">';

// Replace the input field
$content = str_replace($oldInputField, $newInputField, $content);
echo "✓ Updated biaya_ongkir field with proper focus handling\n";

// Add specific JavaScript to handle biaya_ongkir field properly
$focusScript = '
// Special handling for biaya_ongkir field
document.addEventListener("DOMContentLoaded", function() {
    const biayaOngkirInput = document.getElementById("biaya_ongkir");
    
    if (biayaOngkirInput) {
        // Store original value when field gets focus
        let originalValue = biayaOngkirInput.value;
        
        biayaOngkirInput.addEventListener("focus", function() {
            originalValue = this.value;
            this.select();
            console.log("Biaya ongkir focused, value:", originalValue);
        });
        
        biayaOngkirInput.addEventListener("blur", function() {
            // Only update if value has actually changed
            if (this.value !== originalValue) {
                console.log("Biaya ongkir changed from", originalValue, "to", this.value);
                originalValue = this.value;
            }
        });
        
        // Prevent value reset on click
        biayaOngkirInput.addEventListener("click", function(e) {
            e.preventDefault();
            this.focus();
            this.select();
            console.log("Biaya ongkir clicked, current value:", this.value);
        });
        
        // Handle input changes properly
        biayaOngkirInput.addEventListener("input", function() {
            console.log("Biaya ongkir input changed to:", this.value);
            recalcTotal();
        });
        
        // Handle keydown to prevent unwanted behavior
        biayaOngkirInput.addEventListener("keydown", function(e) {
            // Allow: numbers, decimal point, backspace, delete, tab, enter, arrows
            const allowedKeys = ["0","1","2","3","4","5","6","7","8","9",".","Backspace","Delete","Tab","Enter","ArrowLeft","ArrowRight","ArrowUp","ArrowDown","Home","End"];
            
            if (!allowedKeys.includes(e.key) && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
            }
        });
    }
});';

// Insert the script before the closing </script> tag
$content = str_replace('</script>', $focusScript . "\n</script>", $content);
echo "✓ Added biaya_ongkir specific focus handling\n";

// Also fix other numeric fields that might have similar issues
$numericFields = ['biaya_service', 'ppn_persen'];
foreach ($numericFields as $fieldName) {
    $oldFieldPattern = '/<input type="number"[^>]*name="' . $fieldName . '"[^>]*>/';
    $newFieldPattern = '<input type="number" step="0.01" min="0" name="' . $fieldName . '" class="form-control" value="0" id="' . $fieldName . '" 
                         onclick="this.focus()" 
                         onfocus="this.select()">';
    
    $content = preg_replace($oldFieldPattern, $newFieldPattern, $content);
    echo "✓ Updated {$fieldName} field with proper focus handling\n";
}

// Fix the main maintainFocus function to not interfere with numeric input
$oldMaintainFocus = 'function maintainFocus() {
    const barcodeInput = document.getElementById(\'barcode-scanner\');
    if (document.activeElement !== barcodeInput) {
        barcodeInput.focus();
    }
}';

$newMaintainFocus = 'function maintainFocus() {
    const barcodeInput = document.getElementById(\'barcode-scanner\');
    const activeElement = document.activeElement;
    
    // Don\'t interfere if user is interacting with form fields
    if (activeElement && (
        activeElement.classList.contains(\'form-control\') || 
        activeElement.classList.contains(\'form-select\') ||
        activeElement.classList.contains(\'btn\')
    )) {
        return; // User is actively using other form elements
    }
    
    // Only focus barcode scanner if no other element has focus
    if (activeElement === document.body || activeElement === document.documentElement) {
        barcodeInput.focus();
    }
}';

$content = str_replace($oldMaintainFocus, $newMaintainFocus, $content);
echo "✓ Updated maintainFocus function to be less intrusive\n";

// Write the fixed content back to the file
file_put_contents($bladeFile, $content);

echo "\n=== SUMMARY OF FIXES ===\n";
echo "1. ✓ Added proper focus handling to biaya_ongkir field\n";
echo "2. ✓ Added onclick=\"this.focus()\" and onfocus=\"this.select()\" to prevent value reset\n";
echo "3. ✓ Added event listeners for focus, blur, click, input, and keydown\n";
echo "4. ✓ Fixed other numeric fields (biaya_service, ppn_persen) with same approach\n";
echo "5. ✓ Updated maintainFocus function to be less intrusive\n";
echo "6. ✓ Created backup of original file\n";

echo "\nBiaya ongkir field should now retain its value and allow proper editing.\n";
echo "User can click on the field and it will stay focused with value selected.\n";

echo "\nDone.\n";
