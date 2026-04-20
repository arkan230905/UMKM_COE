<?php

namespace App\Helpers;

use App\Models\Produk;

class StockConsistencyHelper
{
    /**
     * Get stock value consistently from produks table
     * 
     * @param Produk|int $produk Product model or ID
     * @return float Stock value from produks.stok column
     */
    public static function getStock($produk): float
    {
        if (is_numeric($produk)) {
            $produk = Produk::find($produk);
        }
        
        if (!$produk) {
            return 0.0;
        }
        
        return (float)($produk->stok ?? 0);
    }
    
    /**
     * Check if stock is sufficient for a given quantity
     * 
     * @param Produk|int $produk Product model or ID
     * @param float $requiredQty Required quantity
     * @return bool True if stock is sufficient
     */
    public static function isStockSufficient($produk, float $requiredQty): bool
    {
        $availableStock = self::getStock($produk);
        return $availableStock >= $requiredQty;
    }
    
    /**
     * Get formatted stock display string
     * 
     * @param Produk|int $produk Product model or ID
     * @return string Formatted stock display
     */
    public static function getFormattedStock($produk): string
    {
        $stock = self::getStock($produk);
        return number_format($stock, 0, ',', '.');
    }
    
    /**
     * Validate that code is using correct stock source
     * This method can be used in tests to ensure consistency
     * 
     * @param string $code Code to check
     * @return array Issues found
     */
    public static function validateStockUsage(string $code): array
    {
        $issues = [];
        
        // Check for usage of actual_stok (should not be used in user-facing code)
        if (strpos($code, 'actual_stok') !== false) {
            $issues[] = 'Found usage of actual_stok - should use stok from produks table instead';
        }
        
        // Check for inconsistent stock field usage in JavaScript
        if (preg_match('/data-stok.*actual_stok/', $code)) {
            $issues[] = 'Found inconsistent stock field usage in HTML data attributes';
        }
        
        return $issues;
    }
}