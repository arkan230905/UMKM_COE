<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MarkMigrationsAsRun extends Command
{
    protected $signature   = 'migrate:mark-all-run';
    protected $description = 'Mark all pending migrations as already run without executing them';

    public function handle()
    {
        $migrationPath = database_path('migrations');
        $files = File::glob($migrationPath . '/*.php');

        // Get already recorded migrations
        $recorded = DB::table('migrations')->pluck('migration')->toArray();

        $batch = (DB::table('migrations')->max('batch') ?? 0) + 1;
        $count = 0;

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if (!in_array($name, $recorded)) {
                DB::table('migrations')->insert([
                    'migration' => $name,
                    'batch'     => $batch,
                ]);
                $count++;
            }
        }

        $this->info("Marked {$count} migrations as run (batch {$batch}).");
        return 0;
    }
}
