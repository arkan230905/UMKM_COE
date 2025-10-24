<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coa;

class CoaController extends Controller
{
    public function index()
    {
        $coas = Coa::all();
        return view('master-data.coa.index', compact('coas'));
    }

    public function create()
    {
        return view('master-data.coa.create');
    }

    public function store(Request $request)
    {
        // Generate kode otomatis dulu sebelum validasi
        if ($request->tipe_akun) {
            $maxKode = Coa::where('tipe_akun', $request->tipe_akun)->max('kode_akun');
            $request->merge([
                'kode_akun' => $maxKode ? $maxKode + 1 : $this->defaultKode($request->tipe_akun)
            ]);
        }

        $validated = $request->validate([
            'kode_akun' => 'required|unique:coas,kode_akun',
            'nama_akun' => 'required|string|max:255',
            'tipe_akun' => 'required|in:Asset,Liability,Equity,Revenue,Expense',
        ]);

        Coa::create($validated);

        return redirect()->route('master-data.coa.index')->with('success', 'COA berhasil ditambahkan.');
    }

    public function generateKode(Request $request)
    {
        $tipe = $request->tipe;
        $maxKode = Coa::where('tipe_akun', $tipe)->max('kode_akun');
        $kode = $maxKode ? $maxKode + 1 : $this->defaultKode($tipe);

        return response()->json(['kode_akun' => $kode]);
    }

    private function defaultKode($tipe)
    {
        return match($tipe) {
            'Asset' => 101,
            'Liability' => 201,
            'Equity' => 301,
            'Revenue' => 401,
            'Expense' => 501,
            default => 100,
        };
    }
}
