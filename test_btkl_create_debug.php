<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "TEST BTKL CREATE CONTROLLER\n";
echo "==========================\n";

// Simulate user login
$user = \App\Models\User::find(1);
if ($user) {
    \Auth::login($user);
    echo "Logged in as user: {$user->name} (ID: {$user->id})\n\n";
} else {
    echo "No user found with ID 1\n";
    exit;
}

// Test the controller method directly
$controller = new \App\Http\Controllers\MasterData\BtklController();

echo "Testing BtklController::create() method...\n";

try {
    // Call the create method
    $response = $controller->create();
    echo "Controller method executed successfully\n";
    echo "Response type: " . get_class($response) . "\n";
    
    // Check if it's a view response
    if (method_exists($response, 'getData')) {
        $viewData = $response->getData();
        echo "View data keys: " . implode(', ', array_keys($viewData)) . "\n";
        
        if (isset($viewData['jabatanBtkl'])) {
            echo "Jabatan BTKL count: " . $viewData['jabatanBtkl']->count() . "\n";
            foreach ($viewData['jabatanBtkl'] as $jabatan) {
                echo "  - {$jabatan->nama}: {$jabatan->pegawais->count()} pegawai\n";
            }
        }
        
        if (isset($viewData['employeeData'])) {
            echo "EmployeeData count: " . $viewData['employeeData']->count() . "\n";
            foreach ($viewData['employeeData'] as $data) {
                echo "  - {$data['nama']}: {$data['pegawai_count']} pegawai, tarif: {$data['tarif']}\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
