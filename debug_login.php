<?php
// Debug script untuk login
echo "=== DEBUG LOGIN SYSTEM ===\n\n";

// Check if Laravel is loaded
if (!function_exists('app')) {
    echo "Laravel not loaded. Run this from artisan tinker or web route.\n";
    exit;
}

// Check routes
echo "1. Checking routes:\n";
try {
    $loginRoute = route('login');
    echo "   Login route: $loginRoute ✓\n";
} catch (Exception $e) {
    echo "   Login route error: " . $e->getMessage() . " ✗\n";
}

// Check controller
echo "\n2. Checking LoginController:\n";
try {
    $controller = new \App\Http\Controllers\Auth\LoginController();
    echo "   LoginController instantiated ✓\n";
} catch (Exception $e) {
    echo "   LoginController error: " . $e->getMessage() . " ✗\n";
}

// Check view
echo "\n3. Checking login view:\n";
try {
    $viewPath = resource_path('views/auth/login.blade.php');
    if (file_exists($viewPath)) {
        echo "   Login view exists ✓\n";
    } else {
        echo "   Login view missing ✗\n";
    }
} catch (Exception $e) {
    echo "   View check error: " . $e->getMessage() . " ✗\n";
}

// Check database connection
echo "\n4. Checking database:\n";
try {
    \DB::connection()->getPdo();
    echo "   Database connected ✓\n";
} catch (Exception $e) {
    echo "   Database error: " . $e->getMessage() . " ✗\n";
}

// Check sample data
echo "\n5. Checking sample data:\n";
try {
    $userCount = \App\Models\User::count();
    echo "   Users in database: $userCount\n";
    
    $perusahaanCount = \App\Models\Perusahaan::count();
    echo "   Companies in database: $perusahaanCount\n";
} catch (Exception $e) {
    echo "   Data check error: " . $e->getMessage() . " ✗\n";
}

echo "\n=== END DEBUG ===\n";
?>