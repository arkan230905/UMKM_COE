<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\DepreciationSchedule;
use App\Services\DepreciationCalculationService;
use App\Services\DepreciationJournalService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AsetController extends Controller
{
    private DepreciationCalculationService $calculationService;
    private DepreciationJournalService $journalService;

    public function __construct(
        DepreciationCalculationService $calculationService,
        DepreciationJournalService $journalService
    ) {
        $this->calculationService = $calculationService;
        $this->journalService = $journalService;
    }

    /**
     * GET /api/asets
     * List semua aset dengan pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Aset::query();

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan metode penyusutan
        if ($request->has('metode_penyusutan')) {
            $query->where('metode_penyusutan', $request->metode_penyusutan);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_aset', 'like', "%$search%")
                  ->orWhere('kode_aset', 'like', "%$search%")
                  ->orWhere('kategori', 'like', "%$search%");
            });
        }

        $asets = $query->paginate($request->get('per_page', 15));

        return response()->json($asets);
    }

    /**
     * GET /api/asets/{id}
     * Detail aset
     */
    public function show(Aset $aset): JsonResponse
    {
        return response()->json($aset->load('depreciationSchedules'));
    }

    /**
     * POST /api/asets
     * Buat aset baru
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_aset' => 'required|string',
            'kategori' => 'required|string',
            'tanggal_perolehan' => 'required|date',
            'harga_perolehan' => 'required|numeric|min:0',
            'nilai_sisa' => 'required|numeric|min:0',
            'umur_ekonomis_tahun' => 'required|integer|min:1',
            'metode_penyusutan' => 'required|in:garis_lurus,saldo_menurun,sum_of_years_digits',
            'coa_id' => 'nullable|exists:coas,id',
            'lokasi' => 'nullable|string',
            'nomor_serial' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $aset = Aset::create($validated);

        return response()->json($aset, 201);
    }

    /**
     * PUT /api/asets/{id}
     * Update aset
     */
    public function update(Request $request, Aset $aset): JsonResponse
    {
        $validated = $request->validate([
            'nama_aset' => 'sometimes|string',
            'kategori' => 'sometimes|string',
            'harga_perolehan' => 'sometimes|numeric|min:0',
            'nilai_sisa' => 'sometimes|numeric|min:0',
            'umur_ekonomis_tahun' => 'sometimes|integer|min:1',
            'metode_penyusutan' => 'sometimes|in:garis_lurus,saldo_menurun,sum_of_years_digits',
            'lokasi' => 'nullable|string',
            'nomor_serial' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'status' => 'sometimes|in:aktif,tidak_aktif,dihapus',
        ]);

        $aset->update($validated);

        return response()->json($aset);
    }

    /**
     * DELETE /api/asets/{id}
     * Hapus aset
     */
    public function destroy(Aset $aset): JsonResponse
    {
        if (!$aset->bisaDihapus()) {
            return response()->json([
                'message' => 'Tidak bisa menghapus aset yang sudah memiliki akumulasi penyusutan'
            ], 422);
        }

        $aset->delete();

        return response()->json(null, 204);
    }

    /**
     * POST /api/asets/{id}/generate-schedule
     * Generate depreciation schedule
     */
    public function generateSchedule(Request $request, Aset $aset): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after:tanggal_mulai',
            'periodisitas' => 'required|in:bulanan,tahunan',
        ]);

        $tanggalMulai = Carbon::parse($validated['tanggal_mulai']);
        $tanggalAkhir = Carbon::parse($validated['tanggal_akhir']);

        $schedules = $this->calculationService->generateSchedule(
            $aset,
            $tanggalMulai,
            $tanggalAkhir,
            $validated['periodisitas']
        );

        return response()->json($schedules);
    }

    /**
     * POST /api/asets/{id}/save-schedule
     * Simpan depreciation schedule ke database
     */
    public function saveSchedule(Request $request, Aset $aset): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after:tanggal_mulai',
            'periodisitas' => 'required|in:bulanan,tahunan',
        ]);

        $tanggalMulai = Carbon::parse($validated['tanggal_mulai']);
        $tanggalAkhir = Carbon::parse($validated['tanggal_akhir']);

        $schedules = $this->calculationService->generateSchedule(
            $aset,
            $tanggalMulai,
            $tanggalAkhir,
            $validated['periodisitas']
        );

        $this->calculationService->saveSchedule($aset, $schedules);

        return response()->json([
            'message' => 'Schedule berhasil disimpan',
            'count' => count($schedules)
        ]);
    }

    /**
     * POST /api/depreciation-schedules/{id}/post
     * Post depreciation schedule
     */
    public function postSchedule(DepreciationSchedule $schedule): JsonResponse
    {
        try {
            $this->journalService->postSchedule($schedule);

            return response()->json([
                'message' => 'Schedule berhasil di-post',
                'schedule' => $schedule->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * POST /api/depreciation-schedules/{id}/reverse
     * Reverse depreciation schedule
     */
    public function reverseSchedule(Request $request, DepreciationSchedule $schedule): JsonResponse
    {
        try {
            $alasan = $request->get('alasan', '');
            $this->journalService->reverseSchedule($schedule, $alasan);

            return response()->json([
                'message' => 'Schedule berhasil di-reverse',
                'schedule' => $schedule->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * GET /api/asets/{id}/depreciation-schedules
     * List depreciation schedules untuk aset
     */
    public function depreciationSchedules(Aset $aset): JsonResponse
    {
        $schedules = $aset->depreciationSchedules()
            ->orderBy('periode_mulai')
            ->get();

        return response()->json($schedules);
    }

    /**
     * GET /api/asets/kategori-options
     * Get kategori options (untuk backward compatibility)
     */
    public function getKategoriByJenis(Request $request)
    {
        $jenisAset = $request->query('jenis_aset');
        
        $kategoriOptions = [
            'Aset Tetap' => [
                'Kendaraan Operasional',
                'Peralatan Kantor',
                'Peralatan Produksi',
                'Furniture & Fixtures',
                'Gedung & Bangunan',
                'Tanah',
            ],
            'Aset Lancar' => [
                'Persediaan Barang Dagang',
                'Bahan Baku',
                'Barang Jadi',
            ],
            'Aset Tak Berwujud' => [
                'Hak Cipta',
                'Merek Dagang',
                'Paten',
                'Goodwill',
            ]
        ];

        if ($jenisAset && array_key_exists($jenisAset, $kategoriOptions)) {
            return response()->json($kategoriOptions[$jenisAset]);
        }

        return response()->json([]);
    }
}
