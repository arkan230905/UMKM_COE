<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Perusahaan;

class SetPerusahaanFromUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\ResponseFactory)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\ResponseFactory
     */
    public function handle(Request $request, Closure $next)
    {
        // Get perusahaan_slug from URL parameter
        $perusahaanSlug = $request->route('perusahaan_slug');
        
        if (!$perusahaanSlug) {
            return response('Perusahaan tidak ditemukan', 404);
        }
        
        // Find perusahaan by slug, kode, or nama
        $perusahaan = Perusahaan::where('slug', strtolower($perusahaanSlug))
            ->orWhere('kode', strtoupper($perusahaanSlug))
            ->orWhere('nama', 'like', '%' . str_replace('-', ' ', $perusahaanSlug) . '%')
            ->first();
        
        if (!$perusahaan) {
            return response('Perusahaan tidak ditemukan', 404);
        }
        
        // Store perusahaan in request for use in controllers
        $request->attributes->add(['perusahaan' => $perusahaan]);
        
        // Store in session for easy access
        session(['perusahaan_id' => $perusahaan->id, 'perusahaan' => $perusahaan]);
        
        return $next($request);
    }
}
