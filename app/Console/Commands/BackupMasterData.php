<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupMasterData extends Command
{
    protected $signature = 'master:backup {--output= : Output directory for backup file}';
    protected $description = 'Backup database untuk protect master data';

    public function handle()
    {
        $this->info('=== Backup Master Data ===');
        $this->newLine();
        
        // Get database config
        $database = config('database.default');
        $connection = config("database.connections.{$database}");
        
        $host = $connection['host'];
        $port = $connection['port'] ?? 3306;
        $dbName = $connection['database'];
        $username = $connection['username'];
        $password = $connection['password'];
        
        $this->info("Database: {$dbName}");
        $this->info("Host: {$host}:{$port}");
        $this->newLine();
        
        // Determine output directory
        $outputDir = $this->option('output') ?: storage_path('backups');
        
        // Create directory if not exists
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
            $this->info("Created backup directory: {$outputDir}");
        }
        
        // Generate filename
        $timestamp = date('Y-m-d_His');
        $filename = "backup_master_data_{$timestamp}.sql";
        $filepath = $outputDir . DIRECTORY_SEPARATOR . $filename;
        
        $this->info("Backup file: {$filepath}");
        $this->newLine();
        
        // Build mysqldump command
        $passwordArg = $password ? "-p{$password}" : '';
        
        // Windows uses different command format
        if (PHP_OS_FAMILY === 'Windows') {
            $command = sprintf(
                'mysqldump -h %s -P %d -u %s %s %s > "%s"',
                $host,
                $port,
                $username,
                $passwordArg,
                $dbName,
                $filepath
            );
        } else {
            $command = sprintf(
                'mysqldump -h %s -P %d -u %s %s %s > %s',
                $host,
                $port,
                $username,
                $passwordArg,
                $dbName,
                escapeshellarg($filepath)
            );
        }
        
        $this->info('Running backup...');
        
        // Execute backup
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && File::exists($filepath)) {
            $filesize = File::size($filepath);
            $filesizeMB = round($filesize / 1024 / 1024, 2);
            
            $this->newLine();
            $this->info('✓ Backup completed successfully!');
            $this->info("File: {$filepath}");
            $this->info("Size: {$filesizeMB} MB");
            $this->newLine();
            
            // Also create a "latest" symlink/copy
            $latestPath = $outputDir . DIRECTORY_SEPARATOR . 'backup_master_data_latest.sql';
            File::copy($filepath, $latestPath);
            $this->info("Latest backup: {$latestPath}");
            
            return 0;
        } else {
            $this->error('✗ Backup failed!');
            $this->error('Make sure mysqldump is installed and accessible in PATH');
            $this->newLine();
            $this->warn('Manual backup command:');
            $this->line($command);
            
            return 1;
        }
    }
}
