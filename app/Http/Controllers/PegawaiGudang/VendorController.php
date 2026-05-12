<?php

namespace App\Http\Controllers\PegawaiGudang;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
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
     * Display a listing of vendors
     */
    public function index()
    {
        $vendors = Vendor::orderBy('nama_vendor')->paginate(20);
        
        return view('pegawai-gudang.vendor.index', [
            'title' => 'Vendor',
            'vendors' => $vendors
        ]);
    }
}