<?php
use Illuminate\Support\Facades\Route;

/**
 * Storage Routes
 * 
 * Routes untuk menangani akses file storage ketika symbolic link tidak berfungsi
 * Ini adalah solusi untuk masalah 403 Forbidden pada Windows
 */

// Route utama untuk serve semua file storage
// Format: /storage/{path} dimana path bisa berupa bukti_faktur/1/filename.png
Route::get('/storage/{path}', function ($path) {
    try {
        // Security: Hanya izinkan file type tertentu
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            abort(403, 'File type not allowed');
        }
        
        // Build full path
        $fullPath = storage_path('app/public/' . $path);
        
        // Security: Check if file exists
        if (!file_exists($fullPath)) {
            abort(404, 'File not found');
        }
        
        // Security: Check if file is within storage directory
        $realPath = realpath($fullPath);
        $storagePath = realpath(storage_path('app/public'));
        
        if ($realPath === false || strpos($realPath, $storagePath) !== 0) {
            abort(403, 'Access denied');
        }
        
        // Get mime type
        $mimeType = mime_content_type($fullPath);
        
        // Return file dengan proper headers untuk display langsung di browser
        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
        
    } catch (Exception $e) {
        \Log::error('Storage route error: ' . $e->getMessage());
        abort(500, 'Server error');
    }
})->where('path', '.*')->name('storage.serve');
