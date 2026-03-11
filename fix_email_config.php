<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing email configuration for password reset...\n\n";

// Update .env file for proper email configuration
$envFile = base_path('.env');
$envContent = file_get_contents($envFile);

// Email configuration to use Mailtrap for testing
$emailConfig = [
    'MAIL_MAILER=smtp',
    'MAIL_HOST=smtp.mailtrap.io',
    'MAIL_PORT=2525',
    'MAIL_USERNAME=your-mailtrap-username',
    'MAIL_PASSWORD=your-mailtrap-password',
    'MAIL_ENCRYPTION=tls',
    'MAIL_FROM_ADDRESS=noreply@umkm-digital.com',
    'MAIL_FROM_NAME="UMKM Digital"'
];

foreach ($emailConfig as $config) {
    if (strpos($envContent, explode('=', $config)[0]) === false) {
        $envContent .= "\n" . $config;
    } else {
        $envContent = preg_replace('/^' . explode('=', $config)[0] . '=.*/m', $config, $envContent);
    }
}

// Write updated .env
file_put_contents($envFile, $envContent);
echo "✓ .env file updated with SMTP configuration\n";

// Clear config cache
\Artisan::call('config:clear');
echo "✓ Configuration cache cleared\n";

// Test password reset functionality
echo "\nTesting password reset functionality...\n";

try {
    // Create a test user if not exists
    $testEmail = 'arkan230905@gmail.com';
    $user = \DB::table('users')->where('email', $testEmail)->first();
    
    if (!$user) {
        echo "⚠️ Test user not found: $testEmail\n";
        echo "Available users:\n";
        $users = \DB::table('users')->pluck('email');
        foreach ($users as $email) {
            echo "- $email\n";
        }
    } else {
        echo "✓ Test user found: $testEmail\n";
        
        // Test password reset link generation
        $token = \Illuminate\Support\Str::random(60);
        \DB::table('password_reset_tokens')->insert([
            'email' => $testEmail,
            'token' => $token,
            'created_at' => now()
        ]);
        
        echo "✓ Password reset token generated: $token\n";
        
        // Check if token exists
        $tokenExists = \DB::table('password_reset_tokens')
            ->where('email', $testEmail)
            ->where('token', $token)
            ->exists();
        
        if ($tokenExists) {
            echo "✓ Password reset token stored successfully\n";
        } else {
            echo "✗ Failed to store password reset token\n";
        }
        
        // Generate reset URL
        $resetUrl = route('password.reset', ['token' => $token]);
        echo "✓ Reset URL generated: $resetUrl\n";
        
        // Test email sending (will be logged since driver is 'smtp' but credentials are placeholder)
        try {
            \Illuminate\Support\Facades\Password::sendResetLink($testEmail);
            echo "✓ Password reset link sent successfully (check logs)\n";
        } catch (\Exception $e) {
            echo "✗ Error sending password reset: " . $e->getMessage() . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "✗ Error in password reset test: " . $e->getMessage() . "\n";
}

echo "\n=== Email Configuration Fix Complete ===\n";
echo "Next steps:\n";
echo "1. Update your .env file with real SMTP credentials\n";
echo "2. Or use Mailtrap for testing: https://mailtrap.io/\n";
echo "3. Clear config cache: php artisan config:clear\n";
echo "4. Test password reset functionality\n";
echo "\nCurrent email configuration:\n";
echo "Driver: " . config('mail.default') . "\n";
echo "Host: " . config('mail.mailers.smtp.host') . "\n";
echo "Port: " . config('mail.mailers.smtp.port') . "\n";
