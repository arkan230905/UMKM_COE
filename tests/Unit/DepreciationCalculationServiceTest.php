<?php

namespace Tests\Unit;

use App\Models\Aset;
use App\Services\DepreciationCalculationService;
use Carbon\Carbon;
use Tests\TestCase;

class DepreciationCalculationServiceTest extends TestCase
{
    private DepreciationCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DepreciationCalculationService();
    }

    /**
     * Test Metode Garis Lurus - Kursi Salon
     */
    public function test_garis_lurus_kursi_salon()
    {
        $aset = new Aset([
            'nama_aset' => 'Kursi Salon',
            'harga_perolehan' => 4000000,
            'nilai_sisa' => 2500000,
            'umur_ekonomis_tahun' => 4,
            'metode_penyusutan' => 'garis_lurus',
        ]);

        $bebanBulanan = $this->service->hitungGarisLurus($aset, 1);
        $bebanTahunan = $this->service->hitungGarisLurus($aset, 12);

        // Beban per bulan: (4.000.000 - 2.500.000) / 48 = 31.250
        $this->assertEquals(31250, $bebanBulanan);
        
        // Beban per tahun: (4.000.000 - 2.500.000) / 4 = 375.000
        $this->assertEquals(375000, $bebanTahunan);
    }

    /**
     * Test Metode Garis Lurus - Gedung
     */
    public function test_garis_lurus_gedung()
    {
        $aset = new Aset([
            'nama_aset' => 'Gedung',
            'harga_perolehan' => 30000000,
            'nilai_sisa' => 20000000,
            'umur_ekonomis_tahun' => 4,
            'metode_penyusutan' => 'garis_lurus',
        ]);

        $bebanBulanan = $this->service->hitungGarisLurus($aset, 1);

        // Beban per bulan: (30.000.000 - 20.000.000) / 48 = 208.333,33
        $this->assertEqualsWithDelta(208333.33, $bebanBulanan, 1);
    }

    /**
     * Test Metode Saldo Menurun
     */
    public function test_saldo_menurun()
    {
        $aset = new Aset([
            'nama_aset' => 'Aset Test',
            'harga_perolehan' => 10000000,
            'nilai_sisa' => 4000000,
            'umur_ekonomis_tahun' => 4,
            'metode_penyusutan' => 'saldo_menurun',
            'persentase_penyusutan' => 26.49,
        ]);

        $nilaiBukuAwal = 10000000;
        $beban = $this->service->hitungSaldoMenurun($aset, $nilaiBukuAwal);

        // Beban: 10.000.000 × 26,49% = 2.649.000
        $this->assertEqualsWithDelta(2649000, $beban, 1000);
    }

    /**
     * Test Metode Sum of Years Digits
     */
    public function test_sum_of_years_digits()
    {
        $aset = new Aset([
            'nama_aset' => 'Aset Test',
            'harga_perolehan' => 10000000,
            'nilai_sisa' => 4000000,
            'umur_ekonomis_tahun' => 4,
            'metode_penyusutan' => 'sum_of_years_digits',
        ]);

        $bebanTahun1 = $this->service->hitungSumOfYearsDigits($aset, 1);
        $bebanTahun2 = $this->service->hitungSumOfYearsDigits($aset, 2);
        $bebanTahun4 = $this->service->hitungSumOfYearsDigits($aset, 4);

        // Total digit = 4 × 5 / 2 = 10
        // Tahun 1: (4/10) × 6.000.000 = 2.400.000
        $this->assertEquals(2400000, $bebanTahun1);
        
        // Tahun 2: (3/10) × 6.000.000 = 1.800.000
        $this->assertEquals(1800000, $bebanTahun2);
        
        // Tahun 4: (1/10) × 6.000.000 = 600.000
        $this->assertEquals(600000, $bebanTahun4);
    }

    /**
     * Test Generate Schedule Bulanan
     */
    public function test_generate_schedule_bulanan()
    {
        $aset = new Aset([
            'nama_aset' => 'Kursi Salon',
            'harga_perolehan' => 4000000,
            'nilai_sisa' => 2500000,
            'umur_ekonomis_tahun' => 4,
            'metode_penyusutan' => 'garis_lurus',
        ]);

        $tanggalMulai = Carbon::parse('2022-11-02');
        $tanggalAkhir = Carbon::parse('2022-12-31');

        $schedules = $this->service->generateSchedule($aset, $tanggalMulai, $tanggalAkhir, 'bulanan');

        // Harus ada 2 bulan (November dan Desember)
        $this->assertCount(2, $schedules);

        // Bulan pertama
        $this->assertEquals('2022-11-02', $schedules[0]['periode_mulai']);
        $this->assertEquals(31250, $schedules[0]['beban_penyusutan']);
        $this->assertEquals(31250, $schedules[0]['akumulasi_penyusutan']);
        $this->assertEquals(3968750, $schedules[0]['nilai_buku']);

        // Bulan kedua
        $this->assertEquals('2022-12-01', $schedules[1]['periode_mulai']);
        $this->assertEquals(31250, $schedules[1]['beban_penyusutan']);
        $this->assertEquals(62500, $schedules[1]['akumulasi_penyusutan']);
        $this->assertEquals(3937500, $schedules[1]['nilai_buku']);
    }

    /**
     * Test Generate Schedule Tahunan
     */
    public function test_generate_schedule_tahunan()
    {
        $aset = new Aset([
            'nama_aset' => 'Gedung',
            'harga_perolehan' => 30000000,
            'nilai_sisa' => 20000000,
            'umur_ekonomis_tahun' => 4,
            'metode_penyusutan' => 'garis_lurus',
        ]);

        $tanggalMulai = Carbon::parse('2022-11-02');
        $tanggalAkhir = Carbon::parse('2026-11-02');

        $schedules = $this->service->generateSchedule($aset, $tanggalMulai, $tanggalAkhir, 'tahunan');

        // Harus ada 4 tahun
        $this->assertCount(4, $schedules);

        // Tahun pertama
        $this->assertEquals(2500000, $schedules[0]['beban_penyusutan']);
        $this->assertEquals(2500000, $schedules[0]['akumulasi_penyusutan']);
        $this->assertEquals(27500000, $schedules[0]['nilai_buku']);

        // Tahun keempat
        $this->assertEquals(2500000, $schedules[3]['beban_penyusutan']);
        $this->assertEquals(10000000, $schedules[3]['akumulasi_penyusutan']);
        $this->assertEquals(20000000, $schedules[3]['nilai_buku']);
    }

    /**
     * Test Nilai Buku tidak boleh di bawah Nilai Sisa
     */
    public function test_nilai_buku_tidak_kurang_dari_nilai_sisa()
    {
        $aset = new Aset([
            'nama_aset' => 'Aset Test',
            'harga_perolehan' => 10000000,
            'nilai_sisa' => 4000000,
            'umur_ekonomis_tahun' => 4,
            'metode_penyusutan' => 'garis_lurus',
        ]);

        $tanggalMulai = Carbon::parse('2022-01-01');
        $tanggalAkhir = Carbon::parse('2026-12-31');

        $schedules = $this->service->generateSchedule($aset, $tanggalMulai, $tanggalAkhir, 'tahunan');

        // Nilai buku terakhir harus = nilai sisa
        $lastSchedule = end($schedules);
        $this->assertEquals(4000000, $lastSchedule['nilai_buku']);
    }
}
