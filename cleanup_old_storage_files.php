<?php
/**
 * Cleanup Old Storage Fix Files
 * 
 * Script ini akan menghapus file-file test dan fix lama yang sudah tidak diperlukan
 * karena sudah ada solusi final yang lebih baik
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          CLEANUP OLD STORAGE FIX FILES                         ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// File-file yang akan dihapus (file test/fix lama)
$filesToDelete = [
    'create_storage_route.php',
    'final_storage_solution.php',
    'fix_storage_access.php',
    'fix_storage_access_laravel.php',
    'manual_storage_fix.php',
    'test_storage_fix.php',
    'test_storage_route.php',
    'final_bukti_solution.php',
];

// File-file yang TIDAK akan dihapus (file baru yang berguna)
$filesToKeep = [
    'test_storage_complete.php',      // Test script yang lengkap
    'STORAGE_FIX_DOCUMENTATION.md',   // Dokumentasi lengkap
    'STORAGE_QUICK_GUIDE.md',         // Quick reference
    'STORAGE_FIX_SUMMARY.md',         // Summary
    'cleanup_old_storage_files.php',  // Script ini sendiri
];

echo "📋 Files to be deleted:\n";
echo "─────────────────────────────────────────────────────────────────\n";

$deletedCount = 0;
$notFoundCount = 0;

foreach ($filesToDelete as $file) {
    if (file_exists($file)) {
        echo "🗑️  Deleting: $file\n";
        if (unlink($file)) {
            $deletedCount++;
        } else {
            echo "   ❌ Failed to delete\n";
        }
    } else {
        echo "⚠️  Not found: $file (already deleted?)\n";
        $notFoundCount++;
    }
}

echo "\n📦 Files to keep:\n";
echo "─────────────────────────────────────────────────────────────────\n";
foreach ($filesToKeep as $file) {
    if (file_exists($file)) {
        echo "✅ Keeping: $file\n";
    }
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                        CLEANUP SUMMARY                         ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Deleted: $deletedCount files\n";
echo "⚠️  Not found: $notFoundCount files\n";
echo "📦 Kept: " . count($filesToKeep) . " files\n\n";

if ($deletedCount > 0) {
    echo "🎉 Cleanup completed successfully!\n\n";
    echo "📚 Remaining documentation:\n";
    echo "   - STORAGE_FIX_DOCUMENTATION.md (Full documentation)\n";
    echo "   - STORAGE_QUICK_GUIDE.md (Quick reference)\n";
    echo "   - STORAGE_FIX_SUMMARY.md (Summary)\n";
    echo "   - test_storage_complete.php (Testing script)\n\n";
} else {
    echo "ℹ️  No files were deleted (already clean or files not found)\n\n";
}

echo "✨ Your project is now clean and organized!\n";
