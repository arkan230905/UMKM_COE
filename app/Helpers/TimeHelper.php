<?php

namespace App\Helpers;

use Carbon\Carbon;

class TimeHelper
{
    /**
     * Get current time with system time correction
     */
    public static function now()
    {
        // Get Windows system time if on Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('powershell -Command "Get-Date -Format \'yyyy-MM-dd HH:mm:ss\'"', $output);
            if (!empty($output[0])) {
                $systemTime = str_replace('.', ':', $output[0]); // Fix Windows time format
                try {
                    // Create Carbon instance from Windows system time and set timezone
                    return Carbon::createFromFormat('Y-m-d H:i:s', $systemTime, 'Asia/Jakarta');
                } catch (\Exception $e) {
                    // Fallback to manual offset correction
                }
            }
        }
        
        // Fallback: Add 5 hours to PHP time to match Windows WIB time
        $phpTime = new \DateTime();
        $phpTime->add(new \DateInterval('PT5H')); // Add 5 hours
        
        return Carbon::createFromFormat('Y-m-d H:i:s', $phpTime->format('Y-m-d H:i:s'), 'Asia/Jakarta');
    }
    
    /**
     * Format time for display
     */
    public static function formatForDisplay($time = null)
    {
        $time = $time ?: self::now();
        return $time->format('d/m/Y H:i:s');
    }
}