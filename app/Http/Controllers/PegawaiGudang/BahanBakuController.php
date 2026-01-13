<?php

namespace App\Http\Controllers\PegawaiGudang;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    public function __construct()
    {
        // Check if user is logged in as gudang staff
        $this->middleware(function ($request, $next) {
            if (!session('gudang_id')) {
                return redirect()->route('gudang.login')->withErrors(['error' => 'Silakan login terlebih dahulu']);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of bahan baku
     */
    public function index()
    {
        $bahanBakus = BahanBaku::with('satuanRelation')->orderBy('nama_bahan')->paginate(20);
        
        return view('pegawai-gudang.bahan-baku.index', [
            'title' => 'Bahan Baku',
            'bahanBakus' => $bahanBakus
        ]);
    }
}