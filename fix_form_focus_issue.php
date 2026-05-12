<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Fixing form field focus issue on penjualan create page...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
if (!file_exists($bladeFile)) {
    echo "Create.blade.php file not found!\n";
    exit;
}

$content = file_get_contents($bladeFile);

// Create backup
$backupFile = 'resources/views/transaksi/penjualan/create_backup_' . date('YmdHis') . '.blade.php';
copy($bladeFile, $backupFile);
echo "Created backup: {$backupFile}\n";

// Fix 1: Remove autofocus from barcode scanner
$content = str_replace('autofocus readonly>', 'readonly>', $content);
echo "✓ Removed autofocus from barcode scanner\n";

// Fix 2: Modify maintainFocus function to not interfere when user is manually typing
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
echo "✓ Modified maintainFocus function to not interfere with manual input\n";

// Fix 3: Add better event handling for form fields
$oldBarcodeHandler = '// Automatic barcode detection
function handleBarcodeInput(char) {
    // Add character to buffer
    barcodeBuffer += char;';

$newBarcodeHandler = '// Automatic barcode detection
function handleBarcodeInput(char) {
    // Only process barcode input if barcode scanner is focused
    const barcodeInput = document.getElementById(\'barcode-scanner\');
    if (document.activeElement !== barcodeInput) {
        return; // Ignore if user is typing elsewhere
    }
    
    // Add character to buffer
    barcodeBuffer += char;';

$content = str_replace($oldBarcodeHandler, $newBarcodeHandler, $content);
echo "✓ Modified barcode input handler to respect focus\n";

// Fix 4: Add click event listeners to form fields to properly set focus
$focusScript = '
// Add click event listeners to form fields
document.addEventListener("DOMContentLoaded", function() {
    const formFields = document.querySelectorAll("input, select, textarea");
    const barcodeInput = document.getElementById("barcode-scanner");
    
    formFields.forEach(function(field) {
        field.addEventListener("click", function() {
            // When user clicks on a form field, remove focus from barcode scanner
            barcodeInput.blur();
            console.log("Field clicked:", field.name || field.id);
        });
        
        field.addEventListener("focus", function() {
            // When user focuses on a form field, remove focus from barcode scanner
            barcodeInput.blur();
            console.log("Field focused:", field.name || field.id);
        });
    });
    
    // Add click listener to barcode scanner to ensure it can get focus when needed
    barcodeInput.addEventListener("click", function() {
        barcodeInput.focus();
        console.log("Barcode scanner clicked");
    });
});';

// Insert the new script before the closing </script> tag
$content = str_replace('</script>', $focusScript . "\n</script>", $content);
echo "✓ Added proper click event listeners for form fields\n";

// Write the fixed content back to the file
file_put_contents($bladeFile, $content);

echo "✓ Fixed form field focus issues\n";

echo "\n=== SUMMARY OF FIXES ===\n";
echo "1. Removed autofocus from barcode scanner\n";
echo "2. Modified maintainFocus() to not interfere with manual input\n";
echo "3. Modified barcode input handler to respect focus\n";
echo "4. Added proper click event listeners for form fields\n";
echo "5. Created backup of original file\n";

echo "\nForm fields should now be properly accessible and fillable.\n";
echo "Users can now click and type in form fields without focus jumping to barcode scanner.\n";

echo "\nDone.\n";
