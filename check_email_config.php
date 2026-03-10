<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking email configuration...\n\n";

// Check mail configuration
echo "Mail Configuration:\n";
echo "Driver: " . config('mail.default') . "\n";
echo "Host: " . config('mail.mailers.smtp.host') . "\n";
echo "Port: " . config('mail.mailers.smtp.port') . "\n";
echo "Username: " . config('mail.mailers.smtp.username') . "\n";
echo "Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
echo "From Address: " . config('mail.from.address') . "\n";
echo "From Name: " . config('mail.from.name') . "\n";

// Check environment variables
echo "\nEnvironment Variables:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER', 'not set') . "\n";
echo "MAIL_HOST: " . env('MAIL_HOST', 'not set') . "\n";
echo "MAIL_PORT: " . env('MAIL_PORT', 'not set') . "\n";
echo "MAIL_USERNAME: " . env('MAIL_USERNAME', 'not set') . "\n";
echo "MAIL_PASSWORD: " . (env('MAIL_PASSWORD') ? '***SET***' : 'NOT SET') . "\n";
echo "MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION', 'not set') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS', 'not set') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME', 'not set') . "\n";

// Test email configuration
echo "\nTesting email configuration...\n";
try {
    $config = [
        'driver' => 'smtp',
        'host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
        'port' => env('MAIL_PORT', 2525),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'no-reply@umkm.com'),
            'name' => env('MAIL_FROM_NAME', 'UMKM Digital'),
        ],
    ];
    
    echo "✓ Email configuration loaded successfully\n";
    
    // Create a test transport
    $transport = new \Swift_SmtpTransport(
        $config['host'],
        $config['port'],
        $config['encryption']
    );
    $transport->setUsername($config['username']);
    $transport->setPassword($config['password']);
    
    $mailer = new \Swift_Mailer($transport);
    
    // Test connection
    echo "Testing SMTP connection...\n";
    if ($transport->isStarted()) {
        echo "✓ SMTP connection is active\n";
    } else {
        echo "⚠️ SMTP connection not started\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Email configuration error: " . $e->getMessage() . "\n";
}

echo "\n=== Email Configuration Check Complete ===\n";
echo "Please check your .env file for proper email settings.\n";
