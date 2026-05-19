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
