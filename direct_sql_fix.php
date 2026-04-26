<?php

// Direct database connection without Laravel
$host = '127.0.0.1';
$dbname = 'eadt_umkm';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if jabatan_id column exists
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM information_schema.columns 
        WHERE table_schema = ? 
        AND table_name = 'pegawais' 
        AND column_name = 'jabatan_id'
    ");
    $stmt->execute([$dbname]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        echo "Adding jabatan_id column...\n";
        
        // Add column
        $pdo->exec("ALTER TABLE pegawais ADD COLUMN jabatan_id BIGINT UNSIGNED NULL AFTER jenis_kelamin");
        echo "Column added successfully.\n";
        
        // Add foreign key
        $pdo->exec("ALTER TABLE pegawais ADD CONSTRAINT pegawais_jabatan_id_foreign FOREIGN KEY (jabatan_id) REFERENCES jabatans(id) ON DELETE SET NULL");
        echo "Foreign key added successfully.\n";
        
    } else {
        echo "Column jabatan_id already exists.\n";
    }
    
    // Show current table structure
    $stmt = $pdo->query("DESCRIBE pegawais");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent pegawais table structure:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']} " . 
             ($column['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . 
             ($column['Key'] ? " KEY: {$column['Key']}" : '') . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}