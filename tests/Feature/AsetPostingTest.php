<?php

namespace Tests\Feature;

use App\Models\Aset;
use App\Models\KategoriAset;
use App\Models\Coa;
use App\Models\JurnalUmum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsetPostingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private KategoriAset $kategori;
    private Coa $assetCoa;
    private Coa $accumDeprCoa;
    private Coa $expenseCoa;
    private Coa $kasCoa;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create COA categories
        $this->assetCoa = Coa::create([
            'user_id' => $this->user->id,
            'kode_akun' => '1010',
            'nama_akun' => 'Peralatan Produksi',
            'tipe_akun' => 'Aset',
            'kategori_akun' => 'Aset Tetap',
        ]);

        $this->accumDeprCoa = Coa::create([
            'user_id' => $this->user->id,
            'kode_akun' => '1020',
            'nama_akun' => 'Akumulasi Penyusutan Peralatan',
            'tipe_akun' => 'Aset',
            'kategori_akun' => 'Aset Kontra',
        ]);

        $this->expenseCoa = Coa::create([
            'user_id' => $this->user->id,
            'kode_akun' => '6001',
            'nama_akun' => 'Beban Penyusutan Peralatan',
            'tipe_akun' => 'Beban',
            'kategori_akun' => 'Beban Operasional',
        ]);

        $this->kasCoa = Coa::create([
            'user_id' => $this->user->id,
            'kode_akun' => '1001',
            'nama_akun' => 'Kas',
            'tipe_akun' => 'Aset',
            'kategori_akun' => 'Kas & Bank',
        ]);

        // Create kategori aset
        $this->kategori = KategoriAset::create([
            'user_id' => $this->user->id,
            'nama' => 'Mesin Produksi',
            'coa_aset_id' => $this->assetCoa->id,
            'coa_akumulasi_penyusutan_id' => $this->accumDeprCoa->id,
            'coa_beban_penyusutan_id' => $this->expenseCoa->id,
        ]);
    }

    /**
     * Test: Aset baru dengan jenis_perolehan = 'pembelian_baru'
     * Expected: Posting ke Jurnal Umum
     */
    public function test_asset_pembelian_baru_posts_to_journal()
    {
        $this->actingAs($this->user);

        $aset = Aset::create([
            'user_id' => $this->user->id,
            'kode_aset' => 'AST-202406-0001',
            'nama_aset' => 'Mesin Produksi Baru',
            'kategori_aset_id' => $this->kategori->id,
            'harga_perolehan' => 50000000,
            'biaya_perolehan' => 5000000,
            'umur_manfaat' => 5,
            'tanggal_perolehan' => now(),
            'asset_coa_id' => $this->assetCoa->id,
            'accum_depr_coa_id' => $this->accumDeprCoa->id,
            'expense_coa_id' => $this->expenseCoa->id,
            'jenis_perolehan' => 'pembelian_baru',  // Explicitly set
            'sumber_dana_coa_id' => $this->kasCoa->id,
        ]);

        // Post aset
        $response = $this->postJson(route('aset.post', $aset->id));

        // Verify response
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.posted_to_journal', true);

        // Verify Jurnal Umum entries created
        $entries = JurnalUmum::where('referensi', 'ASET-' . $aset->id)->get();
        $this->assertEquals(2, $entries->count(), 'Should create 2 entries (debit aset, kredit kas)');

        // Verify debit entry
        $debitEntry = $entries->where('debit', '>', 0)->first();
        $this->assertNotNull($debitEntry);
        $this->assertEquals($this->assetCoa->id, $debitEntry->coa_id);
        $this->assertEquals(55000000, $debitEntry->debit);

        // Verify kredit entry
        $kreditEntry = $entries->where('kredit', '>', 0)->first();
        $this->assertNotNull($kreditEntry);
        $this->assertEquals($this->kasCoa->id, $kreditEntry->coa_id);
        $this->assertEquals(55000000, $kreditEntry->kredit);
    }

    /**
     * Test: Aset lama dengan jenis_perolehan = 'saldo_awal'
     * Expected: TIDAK posting ke Jurnal Umum
     */
    public function test_asset_saldo_awal_does_not_post_to_journal()
    {
        $this->actingAs($this->user);

        $aset = Aset::create([
            'user_id' => $this->user->id,
            'kode_aset' => 'AST-202406-0002',
            'nama_aset' => 'Mesin Lama (Saldo Awal)',
            'kategori_aset_id' => $this->kategori->id,
            'harga_perolehan' => 100000000,
            'biaya_perolehan' => 0,
            'umur_manfaat' => 10,
            'tanggal_perolehan' => now()->subYears(3),
            'asset_coa_id' => $this->assetCoa->id,
            'accum_depr_coa_id' => $this->accumDeprCoa->id,
            'expense_coa_id' => $this->expenseCoa->id,
            'jenis_perolehan' => 'saldo_awal',  // Explicitly set
        ]);

        // Post aset
        $response = $this->postJson(route('aset.post', $aset->id));

        // Verify response
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.posted_to_journal', false);

        // Verify NO Jurnal Umum entries created
        $entries = JurnalUmum::where('referensi', 'ASET-' . $aset->id)->get();
        $this->assertEquals(0, $entries->count(), 'Should NOT create any entries for Saldo Awal');
    }

    /**
     * Test: Aset lama TANPA jenis_perolehan (backward compatibility)
     * Expected: TIDAK posting ke Jurnal Umum (treated as Saldo Awal)
     */
    public function test_asset_lama_without_jenis_perolehan_does_not_post_to_journal()
    {
        $this->actingAs($this->user);

        $aset = Aset::create([
            'user_id' => $this->user->id,
            'kode_aset' => 'AST-202406-0003',
            'nama_aset' => 'Mesin Sangat Lama (Tanpa jenis_perolehan)',
            'kategori_aset_id' => $this->kategori->id,
            'harga_perolehan' => 150000000,
            'biaya_perolehan' => 0,
            'umur_manfaat' => 15,
            'tanggal_perolehan' => now()->subYears(5),
            'asset_coa_id' => $this->assetCoa->id,
            'accum_depr_coa_id' => $this->accumDeprCoa->id,
            'expense_coa_id' => $this->expenseCoa->id,
            // jenis_perolehan NOT set = NULL
        ]);

        // Post aset
        $response = $this->postJson(route('aset.post', $aset->id));

        // Verify response
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.posted_to_journal', false);

        // Verify NO Jurnal Umum entries created
        $entries = JurnalUmum::where('referensi', 'ASET-' . $aset->id)->get();
        $this->assertEquals(0, $entries->count(), 'Should NOT create any entries for old asset without jenis_perolehan');
    }
}
