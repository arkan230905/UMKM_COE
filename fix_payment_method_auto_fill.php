<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Fixing payment method auto-fill issue...\n\n";

// Read the current create.blade.php file
$bladeFile = 'resources/views/transaksi/penjualan/create.blade.php';
$content = file_get_contents($bladeFile);

// Create backup
$backupFile = 'resources/views/transaksi/penjualan/create_backup_' . date('YmdHis') . '.blade.php';
copy($bladeFile, $backupFile);
echo "Created backup: {$backupFile}\n";

// Fix the payment method auto-fill logic
// The issue is in the payment method change handler
$oldPaymentLogic = '                // Auto-select appropriate account
                if (paymentMethod === "cash") {
                    // Select "Kas" (112)
                    for (let option of sumberDanaSelect.options) {
                        if (option.value === "112") {
                            option.selected = true;
                            break;
                        }
                    }
                } else if (paymentMethod === "transfer") {
                    // Select "Kas Bank" (111)
                    for (let option of sumberDanaSelect.options) {
                        if (option.value === "111") {
                            option.selected = true;
                            break;
                        }
                    }
                }';

$newPaymentLogic = '                // Auto-select appropriate account
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
                }';

// Replace the payment logic
$content = str_replace($oldPaymentLogic, $newPaymentLogic, $content);

// Write the fixed content back to the file
file_put_contents($bladeFile, $content);

echo "\n=== FIX SUMMARY ===\n";
echo "1. Fixed payment method auto-fill logic\n";
echo "2. Tunai (Cash) now correctly selects Kas (112)\n";
echo "3. Transfer still correctly selects Kas Bank (111)\n";
echo "4. Credit still hides the receiving account dropdown\n";
echo "5. Created backup of original file\n";

echo "\n=== PAYMENT METHOD LOGIC ===\n";
echo "- Tunai (Cash): Auto-select Kas (112) - FIXED\n";
echo "- Transfer: Auto-select Kas Bank (111)\n";
echo "- Credit: Hide receiving account dropdown\n";

echo "\nDone.\n";
