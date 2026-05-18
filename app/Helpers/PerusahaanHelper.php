<?php

namespace App\Helpers;

use App\Models\Perusahaan;

class PerusahaanHelper
{
    /**
     * Get perusahaan slug
     * Prefer slug column, fallback to kode or nama
     */
    public static function getSlug(Perusahaan $perusahaan): string
    {
        // Use slug if available
        if ($perusahaan->slug) {
            return $perusahaan->slug;
        }
        
        // Fallback to kode or nama
        $identifier = $perusahaan->kode ?: $perusahaan->nama;
        
        // Convert to lowercase and replace spaces with hyphens
        return strtolower(str_replace(' ', '-', trim($identifier)));
    }

    /**
     * Get current perusahaan from request
     */
    public static function getCurrent(): ?Perusahaan
    {
        return request()->attributes->get('perusahaan');
    }

    /**
     * Get current perusahaan slug
     */
    public static function getCurrentSlug(): ?string
    {
        $perusahaan = self::getCurrent();
        if ($perusahaan) {
            return self::getSlug($perusahaan);
        }
        return null;
    }

    /**
     * Generate pelanggan route URL using direct URL generation
     * This avoids using route() helper which requires parameters at generation time
     * Usage in views: {{ PerusahaanHelper::pelangganRoute('dashboard') }}
     */
    public static function pelangganRoute(string $routeName, array $parameters = []): string
    {
        $slug = self::getCurrentSlug();
        
        if (!$slug) {
            // Fallback: try to get from first perusahaan
            $perusahaan = Perusahaan::first();
            if ($perusahaan) {
                $slug = self::getSlug($perusahaan);
            } else {
                return url('/');
            }
        }

        // Map route names to URL paths
        $routeMap = [
            'dashboard' => 'dashboard',
            'cart' => 'cart',
            'cart.store' => 'cart/store',
            'cart.update' => 'cart/update',
            'cart.remove' => 'cart/remove',
            'checkout' => 'checkout',
            'checkout.process' => 'checkout/process',
            'orders' => 'orders',
            'orders.show' => 'orders/show',
            'favorites' => 'favorites',
            'favorites.toggle' => 'favorites/toggle',
            'returns.create' => 'returns/create',
            'returns.store' => 'returns/store',
            'login' => 'login',
            'logout' => 'logout',
            'register' => 'register',
        ];

        $path = $routeMap[$routeName] ?? $routeName;
        $url = "/{$slug}/pelanggan/{$path}";

        // Add query parameters if provided
        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        return url($url);
    }

    /**
     * Generate pelanggan URL with perusahaan slug
     * Usage in views: {{ pelangganUrl('dashboard') }}
     */
    public static function url(string $slug, string $routeName, array $parameters = []): string
    {
        // Build the URL manually
        $url = "/{$slug}/pelanggan/{$routeName}";
        
        // Add query parameters if provided
        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }
        
        return url($url);
    }
}

