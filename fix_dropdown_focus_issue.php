<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Fixing dropdown field focus and closing issue...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

// Create backup
$backupFile = 'resources/views/transaksi/penjualan/create_backup_' . date('YmdHis') . '.blade.php';
copy($bladeFile, $backupFile);
echo "Created backup: {$backupFile}\n";

// Add dropdown-specific focus management
$dropdownScript = '
// Dropdown-specific focus management
document.addEventListener("DOMContentLoaded", function() {
    const dropdowns = document.querySelectorAll("select");
    const barcodeInput = document.getElementById("barcode-scanner");
    
    // Track dropdown interaction state
    let isDropdownOpen = false;
    let activeDropdown = null;
    
    dropdowns.forEach(function(dropdown) {
        // Handle dropdown click
        dropdown.addEventListener("mousedown", function(e) {
            e.preventDefault(); // Prevent default behavior
            e.stopPropagation(); // Stop event propagation
            
            isDropdownOpen = true;
            activeDropdown = this;
            
            // Remove focus from barcode scanner
            if (barcodeInput) {
                barcodeInput.blur();
            }
            
            console.log("Dropdown clicked:", this.name || this.id);
            
            // Let the dropdown open naturally
            setTimeout(function() {
                dropdown.focus();
            }, 10);
        });
        
        // Handle dropdown focus
        dropdown.addEventListener("focus", function(e) {
            isDropdownOpen = true;
            activeDropdown = this;
            
            // Remove focus from barcode scanner
            if (barcodeInput) {
                barcodeInput.blur();
            }
            
            console.log("Dropdown focused:", this.name || this.id);
        });
        
        // Handle dropdown blur
        dropdown.addEventListener("blur", function(e) {
            // Check if blur is due to selecting an option
            setTimeout(function() {
                if (document.activeElement !== dropdown && 
                    !dropdown.contains(document.activeElement)) {
                    isDropdownOpen = false;
                    activeDropdown = null;
                    console.log("Dropdown closed:", dropdown.name || dropdown.id);
                }
            }, 100);
        });
        
        // Handle dropdown change
        dropdown.addEventListener("change", function(e) {
            console.log("Dropdown changed:", this.name || this.id, "value:", this.value);
            
            // Keep dropdown focused after change
            setTimeout(function() {
                dropdown.focus();
            }, 10);
        });
        
        // Handle dropdown keydown
        dropdown.addEventListener("keydown", function(e) {
            // Allow arrow keys, enter, escape, tab
            const allowedKeys = ["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight", "Enter", "Escape", "Tab", "Home", "End"];
            
            if (!allowedKeys.includes(e.key) && !e.ctrlKey && !e.metaKey) {
                // For other keys, let them pass through but maintain focus
                setTimeout(function() {
                    dropdown.focus();
                }, 10);
            }
        });
    });
    
    // Update maintainFocus to respect dropdown state
    window.originalMaintainFocus = window.maintainFocus || function() {};
    
    window.maintainFocus = function() {
        // Don\'t interfere if dropdown is open
        if (isDropdownOpen && activeDropdown) {
            return;
        }
        
        // Call original maintainFocus
        window.originalMaintainFocus();
    };
    
    // Update enhanced focus management to respect dropdown state
    const originalFocusManagement = window.enhancedFocusManagement;
    
    window.enhancedFocusManagement = function() {
        // Don\'t interfere if dropdown is open
        if (isDropdownOpen && activeDropdown) {
            return;
        }
        
        // Call original focus management if it exists
        if (typeof originalFocusManagement === "function") {
            originalFocusManagement();
        }
    };
    
    // Add global click handler to detect when user clicks outside dropdown
    document.addEventListener("click", function(e) {
        if (isDropdownOpen && activeDropdown && 
            !activeDropdown.contains(e.target) && 
            e.target.tagName !== "OPTION") {
            
            // Small delay to allow option selection
            setTimeout(function() {
                if (document.activeElement !== activeDropdown) {
                    isDropdownOpen = false;
                    activeDropdown = null;
                    console.log("Dropdown closed due to outside click");
                }
            }, 50);
        }
    });
});';

// Insert the dropdown script before the closing </script> tag
$content = str_replace('</script>', $dropdownScript . "\n</script>", $content);
echo "1. Added dropdown-specific focus management\n";

// Update the enhanced focus management to be more dropdown-friendly
$oldFocusManagement = '// Enhanced focus management system
document.addEventListener("DOMContentLoaded", function() {
    const formFields = document.querySelectorAll("input[type=number], input[type=text], select, textarea");
    const barcodeInput = document.getElementById("barcode-scanner");
    
    // Exclude barcode scanner from form fields list
    const nonBarcodeFields = Array.from(formFields).filter(field => field.id !== "barcode-scanner");';

$newFocusManagement = '// Enhanced focus management system (dropdown-friendly)
document.addEventListener("DOMContentLoaded", function() {
    const formFields = document.querySelectorAll("input[type=number], input[type=text], select, textarea");
    const barcodeInput = document.getElementById("barcode-scanner");
    
    // Exclude barcode scanner and dropdowns from aggressive focus management
    const nonBarcodeFields = Array.from(formFields).filter(field => 
        field.id !== "barcode-scanner" && 
        field.tagName !== "SELECT"
    );';

$content = str_replace($oldFocusManagement, $newFocusManagement, $content);
echo "2. Updated enhanced focus management to exclude dropdowns\n";

// Update the maintainFocus function to be more dropdown-friendly
$oldMaintainFocus = 'function maintainFocus() {
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

$newMaintainFocus = 'function maintainFocus() {
    const barcodeInput = document.getElementById(\'barcode-scanner\');
    const activeElement = document.activeElement;
    
    // Don\'t interfere if user is typing in other fields or using dropdowns
    if (activeElement && (
        (activeElement.classList.contains(\'form-control\') && 
         activeElement.id !== \'barcode-scanner\') ||
        activeElement.tagName === "SELECT"
    )) {
        return; // User is typing in other form fields or using dropdown
    }
    
    // Allow barcode scanner to get focus when needed
    if (activeElement === document.body || 
        activeElement === document.documentElement ||
        activeElement === barcodeInput) {
        barcodeInput.focus();
    }
}';

$content = str_replace($oldMaintainFocus, $newMaintainFocus, $content);
echo "3. Updated maintainFocus to respect dropdowns\n";

// Write the fixed content back to the file
file_put_contents($bladeFile, $content);

echo "\n=== FIX SUMMARY ===\n";
echo "1. Added dropdown-specific focus management\n";
echo "2. Updated enhanced focus management to exclude dropdowns\n";
echo "3. Updated maintainFocus to respect dropdowns\n";
echo "4. Added dropdown interaction state tracking\n";
echo "5. Created backup of original file\n";

echo "\nDropdown fields should now stay open and allow proper selection.\n";
echo "Focus management will not interfere with dropdown interactions.\n";

echo "\nDone.\n";
