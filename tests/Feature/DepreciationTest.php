<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Aset;
use App\Models\Coa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\DepreciationService;

class DepreciationTest extends TestCase
{
    use RefreshDatabase;

    public function test_straight_line_depreciation(): void
    {
        $expense = Coa::create(['nama_akun'=>'Beban Penyusutan','tipe_akun'=>'Beban']);
        $accum = Coa::create(['nama_akun'=>'Akumulasi Penyusutan','tipe_akun'=>'Aset']);
        $aset = Aset::create([
            'nama_aset' => 'Kursi Salon',
            'tanggal_perolehan' => '2021-01-01',
            'harga_perolehan' => 1000000,
            'nilai_sisa' => 0,
            'umur_ekonomis_tahun' => 4,
            'expense_coa_id' => $expense->id,
            'accum_coa_id' => $accum->id,
        ]);

        $rows = DepreciationService::calculateAndPersist($aset);
        $this->assertCount(4, $rows);
        $this->assertEquals(250000, $rows[0]['beban']);
    }
}
