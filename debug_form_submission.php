<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUGGING FORM SUBMISSION ISSUE ===\n\n";

try {
    // Simulate authentication
    $user = \App\Models\User::find(1);
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        echo "✅ User authenticated: {$user->name}\n";
        
        // Check current data
        echo "\n1. Current data in database:\n";
        $currentData = \App\Models\BiayaBahanBaku::where('user_id', 1)->get();
        foreach ($currentData as $item) {
            echo "   - ID {$item->id}: {$item->bahanBaku->nama_bahan} - {$item->jumlah} {$item->satuan} x Rp {$item->harga_satuan} = Rp {$item->subtotal}\n";
        }
        
        // Simulate the exact form data that should be sent based on your input
        echo "\n2. Simulating form data based on your input:\n";
        echo "   - Bahan Baku: Jagung (ID: 8)\n";
        echo "   - Jumlah: 50\n";
        echo "   - Satuan: Gram\n";
        echo "   - Expected Subtotal: 50 × 50 = Rp 2.500\n";
        
        $formData = [
            'bahan_baku' => [
                0 => [
                    'id' => '8',
                    'jumlah' => '50',
                    'satuan' => 'Gram'
                ]
            ]
        ];
        
        echo "\n3. Form data structure:\n";
        echo json_encode($formData, JSON_PRETTY_PRINT) . "\n";
        
        // Test the update
        echo "\n4. Testing update method...\n";
        $request = new \Illuminate\Http\Request();
        $request->merge($formData);
        
        $controller = new \App\Http\Controllers\BiayaBahanController();
        
        try {
            $response = $controller->update($request, 2);
            
            if ($response instanceof \Illuminate\Http\RedirectResponse) {
                echo "✅ SUCCESS: Update method worked!\n";
                echo "   Redirect URL: " . $response->getTargetUrl() . "\n";
                
                // Check if success message is set
                if ($response->getSession() && $response->getSession()->has('success')) {
                    echo "✅ Success message: " . $response->getSession()->get('success') . "\n";
                }
                
                // Check updated data
                echo "\n5. Data after update:\n";
                $updatedData = \App\Models\BiayaBahanBaku::where('user_id', 1)->get();
                foreach ($updatedData as $item) {
                    echo "   - ID {$item->id}: {$item->bahanBaku->nama_bahan} - {$item->jumlah} {$item->satuan} x Rp {$item->harga_satuan} = Rp {$item->subtotal}\n";
                }
                
            } else {
                echo "❌ PROBLEM: Update method did not return redirect\n";
                echo "   Response type: " . get_class($response) . "\n";
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            echo "❌ VALIDATION ERROR:\n";
            foreach ($e->errors() as $field => $errors) {
                echo "   {$field}: " . implode(', ', $errors) . "\n";
            }
        } catch (\Exception $e) {
            echo "❌ ERROR: " . $e->getMessage() . "\n";
            echo "   File: " . $e->getFile() . "\n";
            echo "   Line: " . $e->getLine() . "\n";
        }
        
    } else {
        echo "❌ User not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== CONCLUSION ===\n";
echo "If the test above works, the problem is in the frontend JavaScript.\n";
echo "The form is not submitting the data correctly to the server.\n";
echo "\nSOLUTION: Use the simple edit page without JavaScript interference.\n";
echo "URL: /master-data/biaya-bahan/2/edit-simple\n";