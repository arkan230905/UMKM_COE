<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Checking EventServiceProvider for observer registration...\n\n";

$eventServiceProviderFile = 'app/Providers/EventServiceProvider.php';
if (file_exists($eventServiceProviderFile)) {
    $content = file_get_contents($eventServiceProviderFile);
    echo "EventServiceProvider content:\n";
    echo $content . "\n\n";
    
    if (strpos($content, 'PembelianObserver') !== false) {
        echo "PembelianObserver found in EventServiceProvider ✓\n";
    } else {
        echo "PembelianObserver NOT found in EventServiceProvider ✗\n";
    }
} else {
    echo "EventServiceProvider not found\n";
}

echo "\nChecking all service providers...\n";

$providers = $app->getLoadedProviders();
foreach ($providers as $provider => $loaded) {
    if (strpos($provider, 'EventServiceProvider') !== false) {
        echo "Found: $provider (loaded: " . ($loaded ? 'YES' : 'NO') . ")\n";
    }
}

echo "\nChecking if we can manually trigger the observer...\n";

try {
    $pembelian = \App\Models\Pembelian::find(10);
    if ($pembelian) {
        echo "Found pembelian ID 10, testing observer trigger...\n";
        
        // Get the observer instance
        $observer = new \App\Observers\PembelianObserver();
        
        // Manually trigger created event to test
        echo "Manually triggering observer created event...\n";
        $observer->created($pembelian);
        
        echo "Observer triggered successfully. Checking if journal was created...\n";
        
        // Check if journal entries were created
        $journalEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
            ->where('ref_id', 10)
            ->get();
            
        echo "Journal entries found: " . $journalEntries->count() . "\n";
        
        if ($journalEntries->count() > 0) {
            foreach ($journalEntries as $journal) {
                echo "Journal ID: {$journal->id}, Date: {$journal->tanggal}, Memo: {$journal->memo}\n";
            }
        }
    } else {
        echo "Pembelian ID 10 not found\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\nDone.\n";
