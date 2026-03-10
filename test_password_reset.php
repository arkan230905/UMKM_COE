<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing password reset functionality...\n\n";

try {
    // Test user email
    $testEmail = 'arkan230905@gmail.com';
    
    echo "Looking for user: $testEmail\n";
    $user = \DB::table('users')->where('email', $testEmail)->first();
    
    if (!$user) {
        echo "⚠️ User not found: $testEmail\n";
        echo "Available users:\n";
        $users = \DB::table('users')->pluck('email');
        foreach ($users as $email) {
            echo "- $email\n";
        }
        exit(1);
    }
    
    echo "✓ User found: " . $user->name . " (ID: " . $user->id . ")\n";
    
    // Test password reset token generation
    echo "\nGenerating password reset token...\n";
    $token = \Illuminate\Support\Str::random(60);
    
    // Store token in database
    $tokenStored = \DB::table('password_reset_tokens')->insert([
        'email' => $testEmail,
        'token' => $token,
        'created_at' => now()
    ]);
    
    if ($tokenStored) {
        echo "✓ Token stored in database: " . substr($token, 0, 20) . "...\n";
    } else {
        echo "✗ Failed to store token\n";
        exit(1);
    }
    
    // Verify token exists
    echo "\nVerifying token storage...\n";
    $tokenExists = \DB::table('password_reset_tokens')
        ->where('email', $testEmail)
        ->where('token', $token)
        ->exists();
    
    if ($tokenExists) {
        echo "✓ Token verification successful\n";
    } else {
        echo "✗ Token verification failed\n";
        exit(1);
    }
    
    // Generate reset URL
    $resetUrl = route('password.reset', ['token' => $token]);
    echo "✓ Reset URL generated: $resetUrl\n";
    
    // Test the actual Password::sendResetLink method
    echo "\nTesting Password::sendResetLink method...\n";
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    $request->merge(['email' => $testEmail]);
    
    try {
        $status = \Illuminate\Support\Facades\Password::sendResetLink($request->only('email'));
        
        echo "Password reset status: " . $status . "\n";
        
        if ($status === \Illuminate\Auth\Passwords\Password::RESET_LINK_SENT) {
            echo "✅ Password reset link sent successfully!\n";
            
            // Check log file for email content
            echo "\nChecking email logs...\n";
            $logFile = storage_path('logs/laravel.log');
            if (file_exists($logFile)) {
                $logContent = file_get_contents($logFile);
                $recentLogs = substr($logContent, -2000); // Last 2000 characters
                
                if (strpos($recentLogs, 'password') !== false) {
                    echo "✓ Password reset email found in logs\n";
                    
                    // Extract email content
                    $lines = explode("\n", $recentLogs);
                    foreach ($lines as $line) {
                        if (strpos($line, 'password') !== false || strpos($line, 'reset') !== false) {
                            echo "Log: " . trim($line) . "\n";
                        }
                    }
                } else {
                    echo "⚠️ No password reset email found in recent logs\n";
                }
            } else {
                echo "⚠️ Log file not found: $logFile\n";
            }
        } else {
            echo "✗ Password reset failed with status: " . $status . "\n";
        }
        
    } catch (\Exception $e) {
        echo "✗ Error testing password reset: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    echo "\n=== Test Summary ===\n";
    echo "1. ✓ User exists in database\n";
    echo "2. ✓ Token generation works\n";
    echo "3. ✓ Token storage works\n";
    echo "4. ✓ Reset URL generation works\n";
    echo "5. Password reset link sending: " . ($status === \Illuminate\Auth\Passwords\Password::RESET_LINK_SENT ? '✅ SUCCESS' : '❌ FAILED') . "\n";
    
    echo "\nNext steps:\n";
    echo "1. Check your email (including spam folder)\n";
    echo "2. If using Mailtrap, check the Mailtrap dashboard\n";
    echo "3. Verify SMTP credentials in .env file\n";
    echo "4. Reset URL format: http://127.0.0.1:8000/password/reset/{token}\n";
    
} catch (\Exception $e) {
    echo "✗ Critical error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
