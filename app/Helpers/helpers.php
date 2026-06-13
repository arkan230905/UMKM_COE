<?php

use App\Helpers\PerusahaanHelper;
use App\Models\Perusahaan;

/**
 * Get perusahaan slug
 */
if (!function_exists('perusahaan_slug')) {
    function perusahaan_slug(Perusahaan $perusahaan = null): ?string
    {
        if ($perusahaan) {
            return PerusahaanHelper::getSlug($perusahaan);
        }
        return PerusahaanHelper::getCurrentSlug();
    }
}

/**
 * Get current perusahaan
 */
if (!function_exists('current_perusahaan')) {
    function current_perusahaan(): ?Perusahaan
    {
        return PerusahaanHelper::getCurrent();
    }
}

/**
 * Generate pelanggan route URL
 */
if (!function_exists('pelanggan_route')) {
    function pelanggan_route(string $routeName, array $parameters = []): string
    {
        return PerusahaanHelper::pelangganRoute($routeName, $parameters);
    }
}

/**
 * Generate pelanggan URL
 */
if (!function_exists('pelanggan_url')) {
    function pelanggan_url(string $path): string
    {
        return PerusahaanHelper::pelangganUrl($path);
    }
}


/**
 * Generate storage URL for a file
 * Usage: {{ storage_url('produk/filename.jpg') }}
 */
if (!function_exists('storage_url')) {
    function storage_url(string $path): string
    {
        if (empty($path)) {
            return asset('images/default-avatar.png');
        }
        
        // If path already starts with http, return as is
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        
        // Return storage URL
        return asset('storage/' . $path);
    }
}

/**
 * Get product image URL with fallback
 * Handles all possible image path formats and provides default
 * 
 * @param string|null $imagePath
 * @param string $default Default image if not found
 * @return string
 */
if (!function_exists('product_image_url')) {
    function product_image_url($imagePath, $default = 'images/default-product.png'): string
    {
        // If no image path, return default
        if (empty($imagePath)) {
            return asset($default);
        }
        
        // If already full URL, return as is
        if (str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }
        
        // Handle different path formats
        // 1. Already starts with 'storage/' - use as is
        if (str_starts_with($imagePath, 'storage/')) {
            $fullPath = public_path($imagePath);
            if (file_exists($fullPath)) {
                return asset($imagePath);
            }
        }
        
        // 2. Starts with 'produk/' or other directory - check in storage
        if (!str_starts_with($imagePath, 'storage/')) {
            $storagePath = public_path('storage/' . $imagePath);
            if (file_exists($storagePath)) {
                return asset('storage/' . $imagePath);
            }
            
            // Also check in storage/app/public
            $appPublicPath = storage_path('app/public/' . $imagePath);
            if (file_exists($appPublicPath)) {
                return asset('storage/' . $imagePath);
            }
        }
        
        // 3. If file not found anywhere, return default
        return asset($default);
    }
}

/**
 * Format angka secara smart - hilangkan .00 jika bukan desimal
 * 
 * @param float|int $number
 * @param int $decimals Default decimals if number has decimal
 * @return string
 */
if (!function_exists('format_number_smart')) {
    function format_number_smart($number, $decimals = 2)
    {
        // Cek apakah angka memiliki desimal
        if ($number == floor($number)) {
            // Tidak ada desimal, format tanpa desimal
            return number_format($number, 0, ',', '.');
        } else {
            // Ada desimal, format dengan desimal
            return number_format($number, $decimals, ',', '.');
        }
    }
}

/**
 * Format number to Indonesian Rupiah format
 * 
 * @param float|int $number
 * @param int $decimals Number of decimal places (default: 0)
 * @return string Formatted rupiah string with "Rp" prefix
 */
if (!function_exists('format_rupiah')) {
    function format_rupiah($number, $decimals = 0)
    {
        return 'Rp ' . number_format($number, $decimals, ',', '.');
    }
}
