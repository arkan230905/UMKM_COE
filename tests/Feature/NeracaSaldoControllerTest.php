<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Coa;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NeracaSaldoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user for testing
        $this->user = User::factory()->create([
            'role' => 'admin'
        ]);
    }

    /** @test */
    public function it_can_display_neraca_saldo_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('akuntansi.neraca-saldo-new'));

        $response->assertStatus(200);
        $response->assertViewIs('akuntansi.neraca-saldo-new');
        $response->assertViewHas(['neracaSaldoData', 'bulan', 'tahun']);
    }

    /** @test */
    public function it_can_display_neraca_saldo_with_specific_period()
    {
        $response = $this->actingAs($this->user)
            ->get(route('akuntansi.neraca-saldo-new', ['bulan' => '04', 'tahun' => '2026']));

        $response->assertStatus(200);
        $response->assertViewHas('bulan', '04');
        $response->assertViewHas('tahun', '2026');
    }

    /** @test */
    public function it_validates_invalid_month()
    {
        $response = $this->actingAs($this->user)
            ->get(route('akuntansi.neraca-saldo-new', ['bulan' => '13', 'tahun' => '2026']));

        $response->assertSessionHasErrors('bulan');
    }

    /** @test */
    public function it_validates_invalid_year()
    {
        $response = $this->actingAs($this->user)
            ->get(route('akuntansi.neraca-saldo-new', ['bulan' => '04', 'tahun' => '2050']));

        $response->assertSessionHasErrors('tahun');
    }

    /** @test */
    public function it_requires_admin_or_owner_role()
    {
        $regularUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($regularUser)
            ->get(route('akuntansi.neraca-saldo-new'));

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_export_pdf()
    {
        // Create some test data
        $kas = Coa::factory()->aset()->create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'saldo_awal' => 1000000
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('akuntansi.neraca-saldo-new.pdf', ['bulan' => '04', 'tahun' => '2026']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function it_can_return_api_data()
    {
        // Create test COAs
        $kas = Coa::factory()->aset()->create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'saldo_awal' => 1000000
        ]);

        $modal = Coa::factory()->modal()->create([
            'kode_akun' => '3101',
            'nama_akun' => 'Modal Pemilik',
            'saldo_awal' => 1000000
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('akuntansi.neraca-saldo-new.api', ['bulan' => '04', 'tahun' => '2026']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'accounts',
                'total_debit',
                'total_kredit',
                'is_balanced'
            ],
            'message'
        ]);

        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function it_shows_correct_trial_balance_data()
    {
        // Create COAs
        $kas = Coa::factory()->aset()->create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'saldo_awal' => 5000000
        ]);

        $modal = Coa::factory()->modal()->create([
            'kode_akun' => '3101',
            'nama_akun' => 'Modal Pemilik',
            'saldo_awal' => 5000000
        ]);

        // Create journal entry with transactions
        $journalEntry = JournalEntry::factory()->create([
            'tanggal' => '2026-04-15'
        ]);

        JournalLine::factory()->create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $kas->id,
            'debit' => 2000000,
            'credit' => 0
        ]);

        JournalLine::factory()->create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $modal->id,
            'debit' => 0,
            'credit' => 2000000
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('akuntansi.neraca-saldo-new', ['bulan' => '04', 'tahun' => '2026']));

        $response->assertStatus(200);
        
        $neracaSaldoData = $response->viewData('neracaSaldoData');
        
        // Should have 2 accounts
        $this->assertCount(2, $neracaSaldoData['accounts']);
        
        // Check if totals are balanced
        $this->assertEquals($neracaSaldoData['total_debit'], $neracaSaldoData['total_kredit']);
        $this->assertTrue($neracaSaldoData['is_balanced']);
        
        // Check Kas account (should have 7,000,000 in debit column)
        $kasAccount = collect($neracaSaldoData['accounts'])->firstWhere('kode_akun', '1101');
        $this->assertEquals(7000000, $kasAccount['debit']);
        $this->assertEquals(0, $kasAccount['kredit']);
        
        // Check Modal account (should have 7,000,000 in credit column)
        $modalAccount = collect($neracaSaldoData['accounts'])->firstWhere('kode_akun', '3101');
        $this->assertEquals(0, $modalAccount['debit']);
        $this->assertEquals(7000000, $modalAccount['kredit']);
    }

    /** @test */
    public function it_handles_unbalanced_trial_balance()
    {
        // Create COAs
        $kas = Coa::factory()->aset()->create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'saldo_awal' => 1000000
        ]);

        // Create unbalanced journal entry (only debit, no credit)
        $journalEntry = JournalEntry::factory()->create([
            'tanggal' => '2026-04-15'
        ]);

        JournalLine::factory()->create([
            'journal_entry_id' => $journalEntry->id,
            'coa_id' => $kas->id,
            'debit' => 500000,
            'credit' => 0
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('akuntansi.neraca-saldo-new', ['bulan' => '04', 'tahun' => '2026']));

        $response->assertStatus(200);
        
        $neracaSaldoData = $response->viewData('neracaSaldoData');
        
        // Should be unbalanced
        $this->assertFalse($neracaSaldoData['is_balanced']);
        $this->assertNotEquals($neracaSaldoData['total_debit'], $neracaSaldoData['total_kredit']);
    }
}