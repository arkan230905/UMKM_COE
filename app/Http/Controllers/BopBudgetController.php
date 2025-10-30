<?php

namespace App\Http\Controllers;

use App\Models\BopBudget;
use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class BopBudgetController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->periode ?? date('Y-m');
        
        // Ambil semua akun beban dari COA
        $expenseAccounts = Coa::where('tipe_akun', 'like', '%beban%')
            ->orWhere('tipe_akun', 'like', '%expense%')
            ->orWhere('nama_akun', 'like', '%beban%')
            ->orWhere('kode_akun', 'like', '5%') // Asumsi akun beban dimulai dengan 5
            ->orderBy('kode_akun')
            ->get();
            
        // Buat atau update BOP Budget untuk setiap akun beban
        foreach ($expenseAccounts as $coa) {
            $existing = BopBudget::where('coa_id', $coa->id)
                ->where('periode', $periode)
                ->first();
                
            if ($existing) {
                // Update kode_akun dan nama_akun jika sudah ada
                $existing->update([
                    'kode_akun' => $coa->kode_akun,
                    'nama_akun' => $coa->nama_akun
                ]);
            } else {
                // Buat baru jika belum ada
                BopBudget::create([
                    'coa_id' => $coa->id,
                    'kode_akun' => $coa->kode_akun,
                    'nama_akun' => $coa->nama_akun,
                    'jumlah_budget' => 0,
                    'periode' => $periode,
                    'keterangan' => 'BOP ' . $coa->nama_akun
                ]);
            }
        }
        
        if ($request->ajax()) {
            $query = BopBudget::with('coa')
                ->where('periode', $periode)
                ->select('bop_budgets.*')
                ->orderBy(DB::raw('LENGTH(kode_akun), kode_akun'));

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="'.route('master-data.bop-budget.edit', $row->id).'" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->editColumn('jumlah_budget', function($row) {
                    return number_format($row->jumlah_budget, 0, ',', '.');
                })
                ->editColumn('actual_amount', function($row) {
                    $class = $row->actual_amount > $row->jumlah_budget ? 'text-danger' : 'text-success';
                    return '<span class="'.$class.'">' . number_format($row->actual_amount, 0, ',', '.') . '</span>';
                })
                ->editColumn('variance', function($row) {
                    $class = $row->variance < 0 ? 'text-danger' : 'text-success';
                    return '<span class="'.$class.'">' . number_format($row->variance, 0, ',', '.') . '</span>';
                })
                ->editColumn('variance_percent', function($row) {
                    $class = $row->variance < 0 ? 'text-danger' : 'text-success';
                    return '<span class="'.$class.'">' . number_format($row->variance_percent, 2, ',', '.') . '%</span>';
                })
                ->rawColumns(['action', 'actual_amount', 'variance', 'variance_percent'])
                ->make(true);
        }

        return view('master-data.bop-budget.index', [
            'periode' => $periode
        ]);
    }

    public function create()
    {
        // Ambil COA yang belum memiliki budget di periode ini
        $periode = request('periode', date('Y-m'));
        
        $existingCoaIds = BopBudget::where('periode', $periode)
            ->pluck('coa_id')
            ->toArray();
            
        $coas = Coa::where(function($query) {
                $query->where('kategori', 'like', '%Beban%')
                      ->orWhere('nama_akun', 'like', '%Beban%');
            })
            ->whereNotIn('id', $existingCoaIds)
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama_akun']);

        return view('master-data.bop-budget.create', [
            'coas' => $coas,
            'periode' => $periode
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coa_id' => 'required|exists:coas,id',
            'jumlah_budget' => 'required|numeric|min:0',
            'periode' => 'required|date_format:Y-m',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Cek apakah sudah ada budget untuk COA ini di periode yang sama
            $existing = BopBudget::where('coa_id', $request->coa_id)
                ->where('periode', $request->periode)
                ->exists();

            if ($existing) {
                return response()->json([
                    'status' => false,
                    'message' => 'Budget untuk akun ini sudah ada di periode yang dipilih'
                ], 422);
            }

            BopBudget::create([
                'coa_id' => $request->coa_id,
                'jumlah_budget' => str_replace('.', '', $request->jumlah_budget),
                'periode' => $request->periode,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'BOP Budget berhasil ditambahkan',
                'redirect' => route('master-data.bop-budget.index', ['periode' => $request->periode])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(BopBudget $bopBudget)
    {
        return view('master-data.bop-budget.edit', [
            'bopBudget' => $bopBudget->load('coa')
        ]);
    }

    public function update(Request $request, BopBudget $bopBudget)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_budget' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $bopBudget->update([
                'jumlah_budget' => str_replace('.', '', $request->jumlah_budget),
                'keterangan' => $request->keterangan
            ]);

            return response()->json([
                'status' => true,
                'message' => 'BOP Budget berhasil diperbarui',
                'redirect' => route('master-data.bop-budget.index', ['periode' => $bopBudget->periode])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(BopBudget $bopBudget)
    {
        try {
            $periode = $bopBudget->periode;
            $bopBudget->delete();

            return response()->json([
                'status' => true,
                'message' => 'BOP Budget berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus BOP Budget: ' . $e->getMessage()
            ], 500);
        }
    }
}
