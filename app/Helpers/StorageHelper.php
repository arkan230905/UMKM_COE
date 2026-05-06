<?php

namespace App\Helpers;

/**
 * Storage Helper
 * 
 * Helper untuk menangani URL storage dengan konsisten
 * Mengatasi masalah symbolic link di Windows
 */
class StorageHelper
{
    /**
     * Get storage URL untuk file
     * 
     * @param string|null $path Path relatif dari storage/app/public/
     * @return string URL lengkap atau empty string jika path null
     * 
     * @example
     * StorageHelper::url('bukti_faktur/1/file.png')
     * // Returns: http://localhost:8000/storage/bukti_faktur/1/file.png
     */
    public static function url(?string $path): string
    {
        if (empty($path)) {
            return '';
        }
        
        // Gunakan url() helper untuk generate URL lengkap
        // Ini akan menggunakan route /storage/{path} yang sudah kita buat
        return url('/storage/' . ltrim($path, '/'));
    }
    
    /**
     * Check apakah file exists di storage
     * 
     * @param string|null $path Path relatif dari storage/app/public/
     * @return bool
     */
    public static function exists(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }
        
        $fullPath = storage_path('app/public/' . ltrim($path, '/'));
        return file_exists($fullPath);
    }
    
    /**
     * Get full path untuk file di storage
     * 
     * @param string $path Path relatif dari storage/app/public/
     * @return string Full path
     */
    public static function path(string $path): string
    {
        return storage_path('app/public/' . ltrim($path, '/'));
    }
    
    /**
     * Get file size dalam bytes
     * 
     * @param string $path Path relatif dari storage/app/public/
     * @return int|false File size atau false jika tidak ada
     */
    public static function size(string $path)
    {
        if (!self::exists($path)) {
            return false;
        }
        
        return filesize(self::path($path));
    }
    
    /**
     * Get mime type dari file
     * 
     * @param string $path Path relatif dari storage/app/public/
     * @return string|false Mime type atau false jika tidak ada
     */
    public static function mimeType(string $path)
    {
        if (!self::exists($path)) {
            return false;
        }
        
        return mime_content_type(self::path($path));
    }
    
    /**
     * Check apakah file adalah gambar
     * 
     * @param string $path Path relatif dari storage/app/public/
     * @return bool
     */
    public static function isImage(string $path): bool
    {
        $mimeType = self::mimeType($path);
        
        if (!$mimeType) {
            return false;
        }
        
        return strpos($mimeType, 'image/') === 0;
    }
    
    /**
     * Get allowed extensions untuk upload
     * 
     * @return array
     */
    public static function allowedExtensions(): array
    {
        return ['png', 'jpg', 'jpeg', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
    }
    
    /**
     * Check apakah extension diperbolehkan
     * 
     * @param string $filename Nama file atau path
     * @return bool
     */
    public static function isAllowedExtension(string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, self::allowedExtensions());
    }
}
