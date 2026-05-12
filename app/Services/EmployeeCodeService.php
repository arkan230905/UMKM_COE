<?php

namespace App\Services;

use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;
use Exception;

class EmployeeCodeService
{
    /**
     * Generate the next sequential employee code with proper locking
     */
    public function generateNextCode(): string
    {
        return DB::transaction(function () {
            // Lock the table to prevent concurrent issues
            $lastCode = $this->getLastUsedCode();
            $nextNumber = $this->extractNumberFromCode($lastCode) + 1;
            $newCode = $this->formatCode($nextNumber);
            
            // Validate the format before returning
            if (!$this->validateCodeFormat($newCode)) {
                throw new Exception("Generated code format is invalid: {$newCode}");
            }
            
            return $newCode;
        });
    }
    
    /**
     * Get the highest existing employee code
     */
    public function getLastUsedCode(): string
    {
        $lastEmployee = Pegawai::lockForUpdate()
            ->whereNotNull('kode_pegawai')
            ->where('kode_pegawai', 'LIKE', 'PGW%')
            ->orderByRaw('CAST(SUBSTRING(kode_pegawai, 4) AS UNSIGNED) DESC')
            ->first();
            
        return $lastEmployee ? $lastEmployee->kode_pegawai : 'PGW0000';
    }
    
    /**
     * Extract numeric part from employee code
     */
    private function extractNumberFromCode(string $code): int
    {
        if (preg_match('/^PGW(\d{4})$/', $code, $matches)) {
            return (int) $matches[1];
        }
        
        return 0;
    }
    
    /**
     * Format number into proper employee code
     */
    private function formatCode(int $number): string
    {
        return 'PGW' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Validate employee code format
     */
    public function validateCodeFormat(string $code): bool
    {
        return preg_match('/^PGW\d{4}$/', $code) === 1;
    }
    
    /**
     * Generate code with retry mechanism for concurrent scenarios
     */
    public function generateCodeWithRetry(int $maxRetries = 3): string
    {
        $attempts = 0;
        
        while ($attempts < $maxRetries) {
            try {
                $code = $this->generateNextCode();
                
                // Double-check that this code doesn't exist
                if (!Pegawai::where('kode_pegawai', $code)->exists()) {
                    return $code;
                }
                
                $attempts++;
            } catch (Exception $e) {
                $attempts++;
                if ($attempts >= $maxRetries) {
                    throw new Exception("Failed to generate unique employee code after {$maxRetries} attempts: " . $e->getMessage());
                }
            }
        }
        
        throw new Exception("Failed to generate unique employee code after {$maxRetries} attempts");
    }
}