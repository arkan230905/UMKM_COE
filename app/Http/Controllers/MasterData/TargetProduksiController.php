<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TargetProduksiController extends Controller
{
    /**
     * Redirect to Filament Target Produksi resource
     */
    public function index()
    {
        // Redirect ke halaman Filament admin panel Target Produksi
        return redirect('/admin/target-produksis');
    }
}
