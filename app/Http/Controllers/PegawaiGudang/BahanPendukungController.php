<?php

namespace App\Http\Controllers\PegawaiGudang;

use App\Http\Controllers\Controller;
use App\Models\BahanPendukung;
use Illuminate\Http\Request;

class BahanPendukungController extends Controller
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
     * Display a listing of bahan pendukung
     */
    public function index()
    {
        $bahanPendukungs = BahanPendukung::with('kategoriBahanPendukung')->orderBy('nama_bahan')->paginate(20);
        
        return view('pegawai-gudang.bahan-pendukung.index', [
            'title' => 'Bahan Pendukung',
            'bahanPendukungs' => $bahanPendukungs
        ]);
    }
}