<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KasBankConsistencyService;
use Carbon\Carbon;

class ValidateKasBankConsistency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kasbank:validate-consistency 
                            {--start-date= : Start date (Y-m-d)}
                            {--end-date= : End date (Y-m-d)}
                            {--days=30 : Number of days to check from end date}
                            {--log : Log results to file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate Kas Bank data consistency and check for duplicates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Kas Bank consistency validation...');

        // Determine date range
        $endDate = $this->option('end-date') 
            ? Carbon::createFromFormat('Y-m-d', $this->option('end-date'))
            : Carbon::now();

        $startDate = $this->option('start-date')
            ? Carbon::createFromFormat('Y-m-d', $this->option('start-date'))
            : $endDate->copy()->subDays($this->option('days'));

        $this->info("Checking period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        // Run validation
        $issues = KasBankConsistencyService::validateConsistency(
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        // Display results
        $this->displayResults($issues);

        // Log if requested
        if ($this->option('log')) {
            $this->logResults($issues, $startDate, $endDate);
        }

        // Return appropriate exit code
        return empty($issues) ? 0 : 1;
    }

    /**
     * Display validation results
     */
    private function displayResults($issues)
    {
        if (empty($issues)) {
            $this->info('No consistency issues found. All data is clean!');
            return;
        }

        $this->warn('Consistency issues found:');
        $totalIssues = 0;

        foreach ($issues as $accountCode => $accountIssues) {
            $this->line("\nAccount: {$accountCode}");
            
            foreach ($accountIssues as $issueType => $issueData) {
                $this->line("  {$issueType}: " . count($issueData) . ' issues');
                $totalIssues += count($issueData);

                // Show first few issues as examples
                $examples = array_slice($issueData, 0, 3);
                foreach ($examples as $example) {
                    $this->line("    - " . $this->formatIssue($example));
                }

                if (count($issueData) > 3) {
                    $this->line("    ... and " . (count($issueData) - 3) . " more");
                }
            }
        }

        $this->warn("\nTotal issues found: {$totalIssues}");
        $this->comment('Please review the issues and take corrective action.');
    }

    /**
     * Format issue for display
     */
    private function formatIssue($issue)
    {
        switch ($issue['issue'] ?? 'unknown') {
            case 'Missing ref_type or ref_id':
                return "{$issue['tanggal']} - {$issue['system']} system - {$issue['memo']}";
            
            case 'Missing tipe_referensi or referensi':
                return "{$issue['tanggal']} - {$issue['system']} system - {$issue['keterangan']}";
            
            case 'Zero amount transaction':
                return "{$issue['tanggal']} - {$issue['system']} system";
            
            default:
                if (isset($issue['tanggal']) && isset($issue['nominal'])) {
                    return "{$issue['tanggal']} - Rp " . number_format($issue['nominal'], 0, ',', '.');
                }
                return json_encode($issue);
        }
    }

    /**
     * Log results to file
     */
    private function logResults($issues, $startDate, $endDate)
    {
        $logFile = storage_path('logs/kasbank_consistency_' . date('Y-m-d_H-i-s') . '.log');
        
        $logContent = [
            'timestamp' => now()->toISOString(),
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ],
            'summary' => [
                'total_accounts_checked' => count($issues),
                'total_issues' => array_sum(array_map(function($accountIssues) {
                    return array_sum(array_map('count', $accountIssues));
                }, $issues))
            ],
            'issues' => $issues
        ];

        file_put_contents($logFile, json_encode($logContent, JSON_PRETTY_PRINT));
        $this->info("Results logged to: {$logFile}");
    }
}
