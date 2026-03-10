<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking User model and mass assignment...\n\n";

try {
    // Check User model
    $userModel = new \App\Models\User();
    echo "User model loaded successfully\n";
    
    // Check fillable fields
    $fillable = $userModel->getFillable();
    echo "Fillable fields: " . implode(', ', $fillable) . "\n";
    
    // Check if plain_password is in fillable
    if (in_array('plain_password', $fillable)) {
        echo "✅ plain_password is in fillable array\n";
    } else {
        echo "❌ plain_password is NOT in fillable array\n";
        echo "This is the problem! Adding plain_password to fillable...\n";
        
        // Read the User model file
        $modelFile = app_path('Models/User.php');
        if (file_exists($modelFile)) {
            $content = file_get_contents($modelFile);
            
            // Check if $fillable exists
            if (strpos($content, 'protected $fillable') !== false) {
                echo "Found fillable property, updating...\n";
                
                // Add plain_password to fillable array
                $pattern = '/protected \$fillable\s*=\s*\[([^\]]*)\];/';
                if (preg_match($pattern, $content, $matches)) {
                    $currentFillable = $matches[1];
                    
                    // Check if plain_password already exists
                    if (strpos($currentFillable, 'plain_password') === false) {
                        // Add plain_password to the array
                        $newFillable = rtrim($currentFillable) . ",\n        'plain_password'";
                        
                        $newContent = str_replace($matches[0], "protected \$fillable = [{$newFillable}];", $content);
                        
                        // Write back to file
                        file_put_contents($modelFile, $newContent);
                        echo "✅ Added plain_password to fillable array\n";
                    } else {
                        echo "plain_password already in fillable array\n";
                    }
                }
            } else {
                echo "No fillable property found, adding...\n";
                // Add fillable property to the model
                $content = file_get_contents($modelFile);
                $fillableProperty = "\n    protected \$fillable = [\n        'name',\n        'email',\n        'phone',\n        'password',\n        'plain_password',\n        'role',\n        'email_verified_at',\n    ];\n";
                
                // Insert after class declaration
                $pattern = '/class User extends Authenticatable\s*\{/';
                if (preg_match($pattern, $content)) {
                    $newContent = preg_replace($pattern, '$0' . $fillableProperty, $content);
                    file_put_contents($modelFile, $newContent);
                    echo "✅ Added fillable property to User model\n";
                }
            }
        } else {
            echo "❌ User model file not found\n";
        }
    }
    
    // Test mass assignment again
    echo "\n=== TESTING MASS ASSIGNMENT ===\n";
    
    $testData = [
        'name' => 'Mass Assignment Test',
        'email' => 'mass@test.com',
        'phone' => '0800000001',
        'password' => 'masstest123',
        'plain_password' => 'masstest123',
        'role' => 'pelanggan',
        'email_verified_at' => now(),
    ];
    
    try {
        $user = \App\Models\User::create($testData);
        echo "✅ Mass assignment successful\n";
        echo "   User ID: {$user->id}\n";
        echo "   Plain Password: " . ($user->plain_password ?? 'NULL') . "\n";
        
        if ($user->plain_password === $testData['plain_password']) {
            echo "✅ Plain password saved correctly via mass assignment\n";
        } else {
            echo "❌ Plain password not saved via mass assignment\n";
        }
    } catch (\Exception $e) {
        echo "❌ Mass assignment failed: " . $e->getMessage() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
