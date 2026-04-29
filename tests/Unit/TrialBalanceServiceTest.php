<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TrialBalanceService;
use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class TrialBalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $trialBalanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trialBalanceService = new TrialBalanceService();
    }

    /** @test */
    public function it_can_calculate_trial_balance_for_empty_period()
    {
        // Create some COAs without any transactions
        $kas = Coa::factory()->create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'tipe_akun' => 'ASET',
            'saldo_awal' => 0
        ]);

        $modal = Coa::factory()->create([
            'kode_akun' => '3101',
            'nama_akun' => 'Modal Pemilik',
            'tipe_akun' => 'MODAL',
            'saldo_awal' => 0
        ]);

        $result = $this->trialBalanceService->calculateTrialBalance('2026-04-01', '2026-04-30');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('accounts', $result);
        $this->assertArrayHasKey('total_debit', $result);
        $this->assertArrayHasKey('total_kredit', $result);
        $this->assertArrayHasKey('is_balanced', $result);
        
        // Should be empty since no transactions and saldo_awal = 0
        $this->assertEmpty($result['accounts']);
        $this->assertEquals(0, $result['total_debit']);
        $this->assertEquals(0, $result['total_kredit']);
        $this->assertTrue($result['is_balanced']);
    }

    /** @test */
    public function it_can_calculate_trial_balance_with_transactions()
    {
        // Create COAs
        $kas = Coa::factory()->create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'tipe_akun' => 'ASET',
            'saldo_awal' => 10000000
        ]);

        $modal = Coa::factory()->create([
            'kode_akun' => '3101',
            'nama_akun' => 'Modal Pemilik',
            'tipe_akun' => 'MODAL',
            'saldo_awal' => 10000000
        ]);

        // Create journal entry
        $journalEntry = JournalEntry::factory()->create([
            'tanggal' => '2026-04-15'
        ]);

        // Create journal lines (Kas bertambah 5jt, Modal bertambah 5jt)
        JournalLine::factory()->create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $kas->id,
            'debit' => 5000000,
            'credit' => 0
        ]);

        JournalLine::factory()->create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $modal->id,
            'debit' => 0,
            'credit' => 5000000
        ]);

        $result = $this->trialBalanceService->calculateTrialBalance('2026-04-01', '2026-04-30');

        $this->assertCount(2, $result['accounts']);
        
        // Check Kas account
        $kasAccount = collect($result['accounts'])->firstWhere('kode_akun', '1101');
        $this->assertNotNull($kasAccount);
        $this->assertEquals(15000000, $kasAccount['saldo_akhir']); // 10jt + 5jt
        $this->assertEquals(15000000, $kasAccount['debit']); // Tampil di kolom debit
        $this->assertEquals(0, $kasAccount['kredit']);

        // Check Modal account
        $modalAccount = collect($result['accounts'])->firstWhere('kode_akun', '3101');
        $this->assertNotNull($modalAccount);
        $this->assertEquals(15000000, $modalAccount['saldo_akhir']); // 10jt + 5jt
        $this->assertEquals(0, $modalAccount['debit']);
        $this->assertEquals(15000000, $modalAccount['kredit']); // Tampil di kolom kredit

        // Check totals
        $this->assertEquals(15000000, $result['total_debit']);
        $this->assertEquals(15000000, $result['total_kredit']);
        $this->assertTrue($result['is_balanced']);
    }

    /** @test */
    public function it_correctly_identifies_debit_normal_accounts()
    {
        $reflection = new \ReflectionClass($this->trialBalanceService);
        $method = $reflection->getMethod('isDebitNormalAccount');
        $method->setAccessible(true);

        // Test asset account (1xx)
        $aset = new Coa(['kode_akun' => '1101', 'tipe_akun' => 'ASET']);
        $this->assertTrue($method->invoke($this->trialBalanceService, $aset));

        // Test expense account (5xx)
        $beban = new Coa(['kode_akun' => '5101', 'tipe_akun' => 'BEBAN']);
        $this->assertTrue($method->invoke($this->trialBalanceService, $beban));

        // Test liability account (2xx)
        $hutang = new Coa(['kode_akun' => '2101', 'tipe_akun' => 'KEWAJIBAN']);
        $this->assertFalse($method->invoke($this->trialBalanceService, $hutang));

        // Test equity account (3xx)
        $modal = new Coa(['kode_akun' => '3101', 'tipe_akun' => 'MODAL']);
        $this->assertFalse($method->invoke($this->trialBalanceService, $modal));

        // Test revenue account (4xx)
        $pendapatan = new Coa(['kode_akun' => '4101', 'tipe_akun' => 'PENDAPATAN']);
        $this->assertFalse($method->invoke($this->trialBalanceService, $pendapatan));
    }

    /** @test */
    public function it_correctly_maps_saldo_to_trial_balance_columns()
    {
        $reflection = new \ReflectionClass($this->trialBalanceService);
        $method = $reflection->getMethod('mapToTrialBalanceColumns');
        $method->setAccessible(true);

        // Test positive balance for debit normal account
        $aset = new Coa(['kode_akun' => '1101', 'tipe_akun' => 'ASET']);
        $result = $method->invoke($this->trialBalanceService, 1000000, $aset);
        $this->assertEquals(1000000, $result['debit']);
        $this->assertEquals(0, $result['kredit']);

        // Test positive balance for credit normal account
        $modal = new Coa(['kode_akun' => '3101', 'tipe_akun' => 'MODAL']);
        $result = $method->invoke($this->trialBalanceService, 1000000, $modal);
        $this->assertEquals(0, $result['debit']);
        $this->assertEquals(1000000, $result['kredit']);

        // Test negative balance for debit normal account (abnormal)
        $result = $method->invoke($this->trialBalanceService, -500000, $aset);
        $this->assertEquals(0, $result['debit']);
        $this->assertEquals(500000, $result['kredit']);

        // Test zero balance
        $result = $method->invoke($this->trialBalanceService, 0, $aset);
        $this->assertEquals(0, $result['debit']);
        $this->assertEquals(0, $result['kredit']);
    }

    /** @test */
    public function it_calculates_correct_saldo_akhir_for_debit_normal_account()
    {
        $reflection = new \ReflectionClass($this->trialBalanceService);
        $method = $reflection->getMethod('calculateSaldoAkhir');
        $method->setAccessible(true);

        $aset = new Coa(['kode_akun' => '1101', 'tipe_akun' => 'ASET']);
        
        // Saldo Akhir = Saldo Awal + Debit - Kredit
        $result = $method->invoke($this->trialBalanceService, $aset, 1000000, 500000, 200000);
        $this->assertEquals(1300000, $result); // 1000000 + 500000 - 200000
    }

    /** @test */
    public function it_calculates_correct_saldo_akhir_for_credit_normal_account()
    {
        $reflection = new \ReflectionClass($this->trialBalanceService);
        $method = $reflection->getMethod('calculateSaldoAkhir');
        $method->setAccessible(true);

        $modal = new Coa(['kode_akun' => '3101', 'tipe_akun' => 'MODAL']);
        
        // Saldo Akhir = Saldo Awal - Debit + Kredit
        $result = $method->invoke($this->trialBalanceService, $modal, 1000000, 200000, 500000);
        $this->assertEquals(1300000, $result); // 1000000 - 200000 + 500000
    }

    /** @test */
    public function it_validates_balance_correctly()
    {
        $balancedData = [
            'total_debit' => 1000000,
            'total_kredit' => 1000000,
            'is_balanced' => true
        ];

        $validation = $this->trialBalanceService->validateBalance($balancedData);
        $this->assertTrue($validation['is_balanced']);
        $this->assertEquals(0, $validation['difference']);

        $unbalancedData = [
            'total_debit' => 1000000,
            'total_kredit' => 800000,
            'is_balanced' => false
        ];

        $validation = $this->trialBalanceService->validateBalance($unbalancedData);
        $this->assertFalse($validation['is_balanced']);
        $this->assertEquals(200000, $validation['difference']);
    }
}