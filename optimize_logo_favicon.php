<?php
/**
 * Script untuk mengoptimalkan logo.png menjadi favicon yang lebih besar dan jelas
 * Jalankan script ini untuk membuat favicon dari logo asli
 */

$logoPath = 'public/images/logo.png';
$outputDir = 'public/';

echo "🎨 Mengoptimalkan logo asli menjadi favicon...\n\n";

if (!file_exists($logoPath)) {
    die("❌ Error: File logo.png tidak ditemukan di {$logoPath}\n");
}

// Cek ekstensi GD
if (!extension_loaded('gd')) {
    die("❌ Error: PHP GD extension tidak tersedia\n");
}

try {
    // Load logo asli
    $originalImage = imagecreatefrompng($logoPath);
    if (!$originalImage) {
        die("❌ Error: Tidak dapat membaca file logo.png\n");
    }
    
    $originalWidth = imagesx($originalImage);
    $originalHeight = imagesy($originalImage);
    
    echo "📏 Logo asli: {$originalWidth}x{$originalHeight} pixels\n";
    
    // Buat favicon dalam berbagai ukuran
    $sizes = [
        16 => 'favicon-16x16.png',
        32 => 'favicon-32x32.png', 
        48 => 'favicon-48x48.png',
        64 => 'favicon-64x64.png'
    ];
    
    foreach ($sizes as $size => $filename) {
        // Buat canvas baru
        $newImage = imagecreatetruecolor($size, $size);
        
        // Preserve transparency
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefill($newImage, 0, 0, $transparent);
        
        // Resize dengan kualitas tinggi
        imagecopyresampled(
            $newImage, $originalImage,
            0, 0, 0, 0,
            $size, $size,
            $originalWidth, $originalHeight
        );
        
        // Tingkatkan kontras dan ketajaman
        imagefilter($newImage, IMG_FILTER_CONTRAST, -20);
        imagefilter($newImage, IMG_FILTER_BRIGHTNESS, 10);
        
        // Simpan favicon
        $outputPath = $outputDir . $filename;
        if (imagepng($newImage, $outputPath, 0)) {
            echo "✅ Favicon {$size}x{$size} berhasil dibuat: {$filename}\n";
        } else {
            echo "❌ Gagal membuat favicon {$size}x{$size}\n";
        }
        
        imagedestroy($newImage);
    }
    
    // Buat favicon utama (copy dari 32x32)
    if (file_exists($outputDir . 'favicon-32x32.png')) {
        copy($outputDir . 'favicon-32x32.png', $outputDir . 'favicon.png');
        echo "✅ Favicon utama (favicon.png) berhasil dibuat\n";
    }
    
    // Buat favicon.ico untuk kompatibilitas maksimal
    if (file_exists($outputDir . 'favicon-16x16.png')) {
        copy($outputDir . 'favicon-16x16.png', $outputDir . 'favicon.ico');
        echo "✅ Favicon ICO (favicon.ico) berhasil dibuat\n";
    }
    
    imagedestroy($originalImage);
    
    echo "\n🎉 SELESAI!\n";
    echo "Favicon telah berhasil dioptimalkan dari logo asli.\n";
    echo "Logo sekarang akan terlihat lebih besar dan jelas di tab browser.\n\n";
    echo "📋 File yang dibuat:\n";
    foreach ($sizes as $size => $filename) {
        if (file_exists($outputDir . $filename)) {
            echo "   - {$filename}\n";
        }
    }
    echo "   - favicon.png (utama)\n";
    echo "   - favicon.ico (kompatibilitas)\n\n";
    echo "🔄 Silakan refresh browser (Ctrl+F5) untuk melihat perubahan.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>