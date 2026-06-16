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
        
        // Check if this is a customer portal route (not admin routes)
        $isCustomerPortal = $request->routeIs('pelanggan.*') || str_contains($request->getPathInfo(), '/pelanggan/');
        
        // If logged in as owner/admin under web guard, force redirect to their own company's slug
        // BUT: Don't redirect for customer portal - allow preview of customer store
        if (!$isCustomerPortal && auth('web')->check()) {
            $user = auth('web')->user();
            if ($user && $user->perusahaan_id && $user->perusahaan_id != $perusahaan->id) {
                $ownPerusahaan = Perusahaan::find($user->perusahaan_id);
                if ($ownPerusahaan) {
                    $slug = \App\Helpers\PerusahaanHelper::getSlug($ownPerusahaan);
                    $currentPath = $request->getPathInfo();
                    $segments = explode('/', ltrim($currentPath, '/'));
                    if (count($segments) > 0) {
                        $segments[0] = $slug;
                        $newPath = '/' . implode('/', $segments);
                        $queryString = $request->getQueryString();
                        if ($queryString) {
                            $newPath .= '?' . $queryString;
                        }
                        return redirect($newPath);
                    }
                }
            }
        }
        
        // Store perusahaan in request for use in controllers
        $request->attributes->add(['perusahaan' => $perusahaan]);
        
        // Store in session for easy access
        session(['perusahaan_id' => $perusahaan->id, 'perusahaan' => $perusahaan]);
        
        return $next($request);
    }
}
