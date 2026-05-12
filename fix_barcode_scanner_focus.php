<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Fixing barcode scanner focus issues...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

// Create backup
$backupFile = 'resources/views/transaksi/penjualan/create_backup_' . date('YmdHis') . '.blade.php';
copy($bladeFile, $backupFile);
echo "Created backup: {$backupFile}\n";

// Fix 1: Restore autofocus to barcode scanner but make it smarter
$oldScannerInput = '<input type="text" id="barcode-scanner" class="form-control form-control-lg" 
                                   placeholder="Siap untuk scan barcode..." 
                                   autocomplete="off" readonly>';

$newScannerInput = '<input type="text" id="barcode-scanner" class="form-control form-control-lg" 
                                   placeholder="Siap untuk scan barcode..." 
                                   autocomplete="off" readonly autofocus>';

$content = str_replace($oldScannerInput, $newScannerInput, $content);
echo "1. Restored autofocus to barcode scanner\n";

// Fix 2: Update maintainFocus to be less intrusive for barcode scanner
$oldMaintainFocus = 'function maintainFocus() {
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

$newMaintainFocus = 'function maintainFocus() {
    const barcodeInput = document.getElementById(\'barcode-scanner\');
    const activeElement = document.activeElement;
    
    // Don\'t interfere if user is typing in other fields
    if (activeElement && (
        activeElement.classList.contains(\'form-control\') && 
        activeElement.id !== \'barcode-scanner\'
    )) {
        return; // User is typing in other form fields
    }
    
    // Allow barcode scanner to get focus when needed
    if (activeElement === document.body || 
        activeElement === document.documentElement ||
        activeElement === barcodeInput) {
        barcodeInput.focus();
    }
}';

$content = str_replace($oldMaintainFocus, $newMaintainFocus, $content);
echo "2. Updated maintainFocus to be barcode scanner friendly\n";

// Fix 3: Update barcode input handler to work with new focus system
$oldBarcodeHandler = '// Automatic barcode detection
function handleBarcodeInput(char) {
    // Only process barcode input if barcode scanner is focused
    const barcodeInput = document.getElementById(\'barcode-scanner\');
    if (document.activeElement !== barcodeInput) {
        return; // Ignore if user is typing elsewhere
    }
    
    // Add character to buffer
    barcodeBuffer += char;';

$newBarcodeHandler = '// Automatic barcode detection
function handleBarcodeInput(char) {
    const barcodeInput = document.getElementById(\'barcode-scanner\');
    
    // Always process barcode input - scanner should always be ready
    // Add character to buffer
    barcodeBuffer += char;';

$content = str_replace($oldBarcodeHandler, $newBarcodeHandler, $content);
echo "3. Updated barcode input handler to be more permissive\n";

// Fix 4: Add barcode scanner focus restoration
$focusScript = '
// Ensure barcode scanner is always ready
document.addEventListener("DOMContentLoaded", function() {
    const barcodeInput = document.getElementById("barcode-scanner");
    
    // Make sure barcode scanner gets focus on page load
    setTimeout(function() {
        barcodeInput.focus();
        console.log("Barcode scanner focused on page load");
    }, 500);
    
    // Add click handler to barcode scanner
    barcodeInput.addEventListener("click", function() {
        this.focus();
        console.log("Barcode scanner clicked and focused");
    });
    
    // Add focus handler to barcode scanner
    barcodeInput.addEventListener("focus", function() {
        console.log("Barcode scanner focused");
    });
    
    // Add blur handler to barcode scanner
    barcodeInput.addEventListener("blur", function() {
        console.log("Barcode scanner blurred");
        // Refocus after a short delay unless user is typing elsewhere
        setTimeout(function() {
            const activeElement = document.activeElement;
            if (activeElement === document.body || 
                activeElement === document.documentElement ||
                !activeElement.classList.contains(\'form-control\') ||
                activeElement.id === \'barcode-scanner\') {
                barcodeInput.focus();
            }
        }, 100);
    });
});';

// Insert the script before the closing </script> tag
$content = str_replace('</script>', $focusScript . "\n</script>", $content);
echo "4. Added barcode scanner focus restoration\n";

// Fix 5: Update the enhanced focus management to not interfere with barcode scanner
$oldFocusManagement = '// Enhanced focus management system
document.addEventListener("DOMContentLoaded", function() {
    const formFields = document.querySelectorAll("input[type=number], input[type=text], select, textarea");
    const barcodeInput = document.getElementById("barcode-scanner");';

$newFocusManagement = '// Enhanced focus management system
document.addEventListener("DOMContentLoaded", function() {
    const formFields = document.querySelectorAll("input[type=number], input[type=text], select, textarea");
    const barcodeInput = document.getElementById("barcode-scanner");
    
    // Exclude barcode scanner from form fields list
    const nonBarcodeFields = Array.from(formFields).filter(field => field.id !== "barcode-scanner");';

$content = str_replace($oldFocusManagement, $newFocusManagement, $content);

// Update the formFields reference in the enhanced focus management
$content = str_replace('formFields.forEach(function(field) {', 'nonBarcodeFields.forEach(function(field) {', $content);
echo "5. Updated enhanced focus management to exclude barcode scanner\n";

// Write the fixed content back to the file
file_put_contents($bladeFile, $content);

echo "\n=== FIX SUMMARY ===\n";
echo "1. Restored autofocus to barcode scanner\n";
echo "2. Updated maintainFocus to be barcode scanner friendly\n";
echo "3. Updated barcode input handler to be more permissive\n";
echo "4. Added barcode scanner focus restoration\n";
echo "5. Updated enhanced focus management to exclude barcode scanner\n";
echo "6. Created backup of original file\n";

echo "\nBarcode scanner should now work properly again.\n";
echo "The scanner will maintain focus and process barcode inputs correctly.\n";

echo "\nDone.\n";
