<?php
/**
 * Script untuk menggenerate favicon dalam berbagai ukuran dari logo.png
 * Jalankan script ini untuk membuat favicon yang optimal
 */

// Path ke logo asli
$logoPath = 'public/images/logo.png';
$faviconDir = 'public/images/favicons/';

// Buat direktori favicon jika belum ada
if (!file_exists($faviconDir)) {
    mkdir($faviconDir, 0755, true);
}

// Ukuran favicon yang akan dibuat
$sizes = [16, 32, 48, 64, 128, 180];

if (file_exists($logoPath)) {
    echo "Menggenerate favicon dari logo.png...\n";
    
    // Load logo asli
    $originalImage = imagecreatefrompng($logoPath);
    if (!$originalImage) {
        die("Error: Tidak dapat membaca file logo.png\n");
    }
    
    $originalWidth = imagesx($originalImage);
    $originalHeight = imagesy($originalImage);
    
    echo "Logo asli: {$originalWidth}x{$originalHeight}\n";
    
    foreach ($sizes as $size) {
        // Buat image baru dengan ukuran yang diinginkan
        $newImage = imagecreatetruecolor($size, $size);
        
        // Preserve transparency
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefill($newImage, 0, 0, $transparent);
        
        // Resize image
        imagecopyresampled(
            $newImage, $originalImage,
            0, 0, 0, 0,
            $size, $size,
            $originalWidth, $originalHeight
        );
        
        // Simpan favicon
        $faviconPath = $faviconDir . "favicon-{$size}x{$size}.png";
        if (imagepng($newImage, $faviconPath)) {
            echo "✓ Favicon {$size}x{$size} berhasil dibuat: {$faviconPath}\n";
        } else {
            echo "✗ Gagal membuat favicon {$size}x{$size}\n";
        }
        
        imagedestroy($newImage);
    }
    
    // Buat favicon.ico (format ICO untuk kompatibilitas maksimal)
    $favicon16 = imagecreatefrompng($faviconDir . 'favicon-16x16.png');
    $favicon32 = imagecreatefrompng($faviconDir . 'favicon-32x32.png');
    
    if ($favicon16 && $favicon32) {
        // Untuk membuat ICO file, kita akan copy favicon 32x32 sebagai favicon utama
        copy($faviconDir . 'favicon-32x32.png', 'public/favicon.png');
        echo "✓ Favicon utama (favicon.png) berhasil dibuat\n";
    }
    
    imagedestroy($originalImage);
    
    echo "\n=== SELESAI ===\n";
    echo "Favicon telah berhasil digenerate dalam berbagai ukuran.\n";
    echo "Silakan refresh browser untuk melihat perubahan.\n";
    
} else {
    echo "Error: File logo.png tidak ditemukan di {$logoPath}\n";
    echo "Pastikan file logo.png ada di folder public/images/\n";
}
?>