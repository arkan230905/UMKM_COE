<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Simple Password Reset Test\n";
echo "========================\n\n";

try {
    // Test with existing user
    $testEmail = 'arkan230905@gmail.com';
    $user = \DB::table('users')->where('email', $testEmail)->first();
    
    if (!$user) {
        echo "❌ User not found: $testEmail\n";
        exit(1);
    }
    
    echo "✅ User found: " . $user->name . " (ID: " . $user->id . ")\n";
    
    // Generate token
    $token = \Illuminate\Support\Str::random(60);
    echo "✅ Token generated: " . substr($token, 0, 20) . "...\n";
    
    // Store token
    \DB::table('password_reset_tokens')->insert([
        'email' => $testEmail,
        'token' => $token,
        'created_at' => now()
    ]);
    echo "✅ Token stored in database\n";
    
    // Generate reset URL
    $resetUrl = url('/password/reset/' . $token);
    echo "✅ Reset URL: $resetUrl\n";
    
    // Test email sending with simple method
    echo "\nTesting email sending...\n";
    
    // Check current mail config
    echo "Current mail driver: " . config('mail.default') . "\n";
    
    if (config('mail.default') === 'log') {
        echo "⚠️  Mail driver is set to 'log' - emails will be logged, not sent\n";
        echo "To fix this, update your .env file:\n";
        echo "MAIL_MAILER=smtp\n";
        echo "MAIL_HOST=smtp.gmail.com\n";
        echo "MAIL_PORT=587\n";
        echo "MAIL_USERNAME=your-email@gmail.com\n";
        echo "MAIL_PASSWORD=your-app-password\n";
        echo "MAIL_ENCRYPTION=tls\n";
        echo "MAIL_FROM_ADDRESS=noreply@umkm.com\n";
        echo "MAIL_FROM_NAME=UMKM Digital\n";
    } else {
        echo "✅ Mail driver configured: " . config('mail.default') . "\n";
        
        // Try to send notification
        try {
            // Create notification manually
            $notification = new \Illuminate\Auth\Notifications\ResetPassword($token);
            $user->notify($notification);
            echo "✅ Password reset notification sent\n";
        } catch (\Exception $e) {
            echo "❌ Error sending notification: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Manual Test Instructions ===\n";
    echo "1. Visit: $resetUrl\n";
    echo "2. The form should load with the token\n";
    echo "3. Enter new password and confirm\n";
    echo "4. Submit to reset password\n";
    
    echo "\n=== Troubleshooting ===\n";
    echo "If email not received:\n";
    echo "- Check spam folder\n";
    echo "- Verify email address is correct\n";
    echo "- Check .env mail configuration\n";
    echo "- Run: php artisan config:clear\n";
    echo "- Run: php artisan cache:clear\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
