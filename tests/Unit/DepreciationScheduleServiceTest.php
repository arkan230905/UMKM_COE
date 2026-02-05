<?php

namespace Tests\Unit;

use App\DTOs\DepreciationScheduleRowDTO;
use App\Models\Aset;
use App\Services\DepreciationScheduleService;
use Carbon\Carbon;
use Tests\TestCase;

class DepreciationScheduleServiceTest extends TestCase
{
    private DepreciationScheduleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DepreciationScheduleService();
    }

    /** @test */
    public function it_generates_straight_line_schedule_correctly()
    {
        // Test Case A: Garis Lurus
        $asset = $this->createTestAsset([
            'harga_perolehan' => 85000000,
            'nilai_residu' => 5000000,
            'umur_ekonomis' => 5,
            'tanggal_mulai_penyusutan' => '2026-02-05',
            'metode_penyusutan' => 'GARIS_LURUS',
        ]);

        // Manually set the attributes to simulate database data
        $asset->setAttribute('umur_ekonomis_tahun', 5);
        $asset->setAttribute('tanggal_mulai_penyusutan', '2026-02-05');

        $schedule = $this->service->generateDepreciationSchedule($asset);
        $summary = $this->service->getScheduleSummary($asset);

        // Assertions
        $this->assertNotEmpty($schedule);
        $this->assertEquals(60, $schedule->count()); // 5 years * 12 months
        
        $firstRow = $schedule->first();
        $this->assertEquals('2026-02-01', $firstRow->period_date);
        $this->assertEquals('February 2026', $firstRow->period_label);
        $this->assertEqualsWithDelta(1333333.33, $firstRow->depreciation_amount, 0.01);
        
        $lastRow = $schedule->last();
        $this->assertEquals(5000000, $lastRow->book_value_amount); // Should equal residual value
        $this->assertEquals(80000000, $lastRow->accumulated_amount); // Total depreciation
        
        $this->assertEquals(80000000, $summary['total_depreciation']);
        $this->assertEquals(5000000, $summary['current_book_value']);
        $this->assertTrue($summary['is_fully_depreciated']);
    }

    /** @test */
    public function it_generates_double_declining_balance_schedule_correctly()
    {
        // Test Case B: DDB with 5 years (40% rate)
        $asset = $this->createTestAsset([
            'harga_perolehan' => 100000000,
            'nilai_residu' => 10000000,
            'umur_ekonomis' => 5,
            'tanggal_mulai_penyusutan' => '2026-01-01',
            'metode_penyusutan' => 'SALDO_MENURUN_GANDA',
        ]);

        // Manually set attributes to simulate database data
        $asset->setAttribute('umur_ekonomis_tahun', 5);
        $asset->setAttribute('tanggal_mulai_penyusutan', '2026-01-01');

        $schedule = $this->service->generateDepreciationSchedule($asset);
        $summary = $this->service->getScheduleSummary($asset);

        // Assertions
        $this->assertNotEmpty($schedule);
        $this->assertLessThanOrEqual(60, $schedule->count()); // May stop early
        
        // First year should have higher depreciation
        $firstYearTotal = $schedule->take(12)->sum('depreciation_amount');
        $this->assertEqualsWithDelta(40000, $firstYearTotal, 100); // 100M * 40% / 12
        
        // Final book value should not go below residual
        $lastRow = $schedule->last();
        $this->assertGreaterThanOrEqual(10000000, $lastRow->book_value_amount);
        
        // Total depreciation should not exceed depreciable amount
        $this->assertLessThanOrEqual(90000000, $summary['total_depreciation']);
    }

    /** @test */
    public function it_generates_sum_of_years_digits_schedule_correctly()
    {
        // Test Case C: SYD with 5 years (SYD = 15)
        $asset = $this->createTestAsset([
            'harga_perolehan' => 120000000,
            'nilai_residu' => 20000000,
            'umur_ekonomis' => 5,
            'tanggal_mulai_penyusutan' => '2026-01-01',
            'metode_penyusutan' => 'JUMLAH_ANGKA_TAHUN',
        ]);

        // Manually set attributes to simulate database data
        $asset->setAttribute('umur_ekonomis_tahun', 5);
        $asset->setAttribute('tanggal_mulai_penyusutan', '2026-01-01');

        $schedule = $this->service->generateDepreciationSchedule($asset);
        $summary = $this->service->getScheduleSummary($asset);

        // Assertions
        $this->assertNotEmpty($schedule);
        
        // Year 1 should have weight 5/15 = 33.33%
        $firstYearTotal = $schedule->take(12)->sum('depreciation_amount');
        $expectedYear1 = (120000000 - 20000000) * (5/15) / 12; // Monthly
        $this->assertEqualsWithDelta($expectedYear1 * 12, $firstYearTotal, 100);
        
        // Year 2 should have weight 4/15 = 26.67%
        $secondYearTotal = $schedule->slice(12, 12)->sum('depreciation_amount');
        $expectedYear2 = (120000000 - 20000000) * (4/15) / 12; // Monthly
        $this->assertEqualsWithDelta($expectedYear2 * 12, $secondYearTotal, 100);
        
        // Final book value should equal residual
        $lastRow = $schedule->last();
        $this->assertEquals(20000000, $lastRow->book_value_amount);
        
        // Total depreciation should equal depreciable amount
        $this->assertEquals(100000000, $summary['total_depreciation']);
        $this->assertEquals(20000000, $summary['current_book_value']);
        $this->assertTrue($summary['is_fully_depreciated']);
    }

    /** @test */
    public function it_handles_edge_cases_correctly()
    {
        // Test with zero useful life
        $asset = $this->createTestAsset([
            'harga_perolehan' => 100000000,
            'nilai_residu' => 0,
            'umur_ekonomis' => 0,
            'metode_penyusutan' => 'GARIS_LURUS',
        ]);

        // Manually set attributes to simulate database data
        $asset->setAttribute('umur_ekonomis', 0);

        $schedule = $this->service->generateDepreciationSchedule($asset);
        $this->assertEmpty($schedule);

        // Test with zero acquisition cost
        $asset2 = $this->createTestAsset([
            'harga_perolehan' => 0,
            'nilai_residu' => 0,
            'umur_ekonomis' => 5,
            'metode_penyusutan' => 'GARIS_LURUS',
        ]);

        // Manually set attributes to simulate database data
        $asset2->setAttribute('umur_ekonomis', 5);

        $schedule2 = $this->service->generateDepreciationSchedule($asset2);
        $this->assertEmpty($schedule2);
    }

    /** @test */
    public function it_adjusts_final_month_to_not_go_below_residual_value()
    {
        // Test case where calculation would go below residual
        $asset = $this->createTestAsset([
            'harga_perolehan' => 11000000, // Small amount for testing
            'nilai_residu' => 1000000,
            'umur_ekonomis' => 5,
            'tanggal_mulai_penyusutan' => '2026-01-01',
            'metode_penyusutan' => 'GARIS_LURUS',
        ]);

        // Manually set attributes to simulate database data
        $asset->setAttribute('umur_ekonomis_tahun', 5);
        $asset->setAttribute('tanggal_mulai_penyusutan', '2026-01-01');

        $schedule = $this->service->generateDepreciationSchedule($asset);
        
        // Find the month where adjustment occurs
        $adjustmentMonth = $schedule->first(function ($row) {
            return $row->book_value_amount <= 1000000 + 0.005;
        });

        $this->assertNotNull($adjustmentMonth);
        $this->assertEquals(1000000, $adjustmentMonth->book_value_amount);
        $this->assertGreaterThan(0, $adjustmentMonth->depreciation_amount); // Should have adjusted amount
    }

    private function createTestAsset(array $overrides): Aset
    {
        return new Aset([
            'nama_aset' => 'Test Asset',
            'harga_perolehan' => 0,
            'nilai_residu' => 0,
            'umur_manfaat_tahun' => 0,
            'tanggal_mulai_penyusutan' => now(),
            'metode_penyusutan' => 'GARIS_LURUS',
            ...$overrides
        ]);
    }
}
