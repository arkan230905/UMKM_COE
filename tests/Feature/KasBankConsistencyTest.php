<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Coa;
use App\Models\JournalLine;
use App\Models\JournalEntry;
use App\Models\JurnalUmum;
use App\Services\KasBankConsistencyService;
use App\Helpers\AccountHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KasBankConsistencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test duplicate detection logic
     */
    public function test_duplicate_detection()
    {
        // Create test data
        $coa = Coa::factory()->create([
            'kode_akun' => '112',
            'nama_akun' => 'Kas',
            'tipe_akun' => 'Debit'
        ]);

        // Create journal entry (new system)
        $journalEntry = JournalEntry::factory()->create([
            'tanggal' => '2026-04-23',
            'ref_type' => 'sale',
            'ref_id' => 1,
            'memo' => 'Penjualan #SJ-20260423-001'
        ]);

        // Create journal line (new system)
        $journalLine = JournalLine::factory()->create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $coa->id,
            'debit' => 1393050.00,
            'credit' => 0
        ]);

        // Create jurnal umum (old system) - duplicate
        $jurnalUmum = JurnalUmum::factory()->create([
            'coa_id' => $coa->id,
            'tanggal' => '2026-04-23',
            'tipe_referensi' => 'sale',
            'referensi' => 'sale#1',
            'debit' => 1393050.00,
            'kredit' => 0
        ]);

        // Test consistency validation
        $issues = KasBankConsistencyService::validateConsistency('2026-04-23', '2026-04-23');

        // Should detect duplicate
        $this->assertArrayHasKey('112', $issues);
        $this->assertArrayHasKey('duplicates', $issues['112']);
        $this->assertCount(1, $issues['112']['duplicates']);

        $duplicate = $issues['112']['duplicates'][0];
        $this->assertEquals('2026-04-23', $duplicate['tanggal']);
        $this->assertEquals(1393050.00, $duplicate['nominal']);
    }

    /**
     * Test duplicate key generation
     */
    public function test_duplicate_key_generation()
    {
        $key = KasBankConsistencyService::generateDuplicateKey('2026-04-23', 'sale#1', 1393050.00);
        
        $this->assertEquals('2026-04-23 00:00:00_sale#1_1393050.00', $key);
    }

    /**
     * Test unreferenced transaction detection
     */
    public function test_unreferenced_transaction_detection()
    {
        $coa = Coa::factory()->create([
            'kode_akun' => '112',
            'nama_akun' => 'Kas'
        ]);

        // Create transaction without reference
        $journalEntry = JournalEntry::factory()->create([
            'tanggal' => '2026-04-23',
            'ref_type' => null, // Missing reference
            'ref_id' => null
        ]);

        $journalLine = JournalLine::factory()->create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $coa->id,
            'debit' => 1000000.00
        ]);

        $issues = KasBankConsistencyService::validateConsistency('2026-04-23', '2026-04-23');

        $this->assertArrayHasKey('112', $issues);
        $this->assertArrayHasKey('unreferenced', $issues['112']);
    }

    /**
     * Test zero amount detection
     */
    public function test_zero_amount_detection()
    {
        $coa = Coa::factory()->create([
            'kode_akun' => '112',
            'nama_akun' => 'Kas'
        ]);

        // Create transaction with zero amount
        $journalEntry = JournalEntry::factory()->create([
            'tanggal' => '2026-04-23'
        ]);

        $journalLine = JournalLine::factory()->create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $coa->id,
            'debit' => 0,
            'credit' => 0
        ]);

        $issues = KasBankConsistencyService::validateConsistency('2026-04-23', '2026-04-23');

        $this->assertArrayHasKey('112', $issues);
        $this->assertArrayHasKey('inconsistent_amounts', $issues['112']);
    }

    /**
     * Test consistency check with no issues
     */
    public function test_consistency_check_no_issues()
    {
        $coa = Coa::factory()->create([
            'kode_akun' => '112',
            'nama_akun' => 'Kas'
        ]);

        // Create clean transaction
        $journalEntry = JournalEntry::factory()->create([
            'tanggal' => '2026-04-23',
            'ref_type' => 'sale',
            'ref_id' => 1
        ]);

        $journalLine = JournalLine::factory()->create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $coa->id,
            'debit' => 1000000.00
        ]);

        $issues = KasBankConsistencyService::validateConsistency('2026-04-23', '2026-04-23');

        $this->assertEmpty($issues);
    }

    /**
     * Test various reference formats
     */
    public function test_reference_format_matching()
    {
        $coa = Coa::factory()->create([
            'kode_akun' => '112',
            'nama_akun' => 'Kas'
        ]);

        // Test sale# format
        $journalEntry1 = JournalEntry::factory()->create([
            'tanggal' => '2026-04-23',
            'ref_type' => 'sale',
            'ref_id' => 1
        ]);

        $journalLine1 = JournalLine::factory()->create([
            'journal_entry_id' => $journalEntry1->id,
            'coa_id' => $coa->id,
            'debit' => 1000000.00
        ]);

        $jurnalUmum1 = JurnalUmum::factory()->create([
            'coa_id' => $coa->id,
            'tanggal' => '2026-04-23',
            'tipe_referensi' => 'sale',
            'referensi' => 'sale#1',
            'debit' => 1000000.00
        ]);

        // Test SJ- format
        $journalEntry2 = JournalEntry::factory()->create([
            'tanggal' => '2026-04-24',
            'ref_type' => 'sale',
            'ref_id' => 2
        ]);

        $journalLine2 = JournalLine::factory()->create([
            'journal_entry_id' => $journalEntry2->id,
            'coa_id' => $coa->id,
            'debit' => 2000000.00
        ]);

        $jurnalUmum2 = JurnalUmum::factory()->create([
            'coa_id' => $coa->id,
            'tanggal' => '2026-04-24',
            'tipe_referensi' => 'sale',
            'referensi' => 'SJ-20260424-002',
            'debit' => 2000000.00
        ]);

        $issues = KasBankConsistencyService::validateConsistency('2026-04-23', '2026-04-24');

        $this->assertArrayHasKey('112', $issues);
        $this->assertArrayHasKey('duplicates', $issues['112']);
        $this->assertCount(2, $issues['112']['duplicates']);
    }
}
