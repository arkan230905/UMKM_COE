<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pastikan user sudah login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Untuk request yang memiliki ID parameter, pastikan data milik user
        if ($request->route() && $request->route()->hasParameter('id')) {
            $resourceId = $request->route()->parameter('id');
            $resourceName = $this->getResourceNameFromRoute($request->route()->getName());
            
            if ($resourceName && $resourceId) {
                $this->ensureUserOwnsResource($resourceName, $resourceId);
            }
        }

        return $next($request);
    }

    /**
     * Get resource name from route name
     */
    private function getResourceNameFromRoute($routeName): ?string
    {
        $routeMap = [
            'master-data.coa' => 'coa',
            'master-data.satuan' => 'satuan',
            'master-data.bahan-baku' => 'bahan_baku',
            'master-data.produk' => 'produk',
            'master-data.pegawai' => 'pegawai',
            'master-data.vendor' => 'vendor',
            'master-data.aset' => 'aset',
            'transaksi.pembelian' => 'pembelian',
            'transaksi.penjualan' => 'penjualan',
            'transaksi.produksi' => 'produksi',
            'transaksi.penggajian' => 'penggajian',
        ];

        foreach ($routeMap as $pattern => $resource) {
            if (str_contains($routeName, $pattern)) {
                return $resource;
            }
        }

        return null;
    }

    /**
     * Ensure user owns the resource
     */
    private function ensureUserOwnsResource($resourceName, $resourceId): void
    {
        $modelClass = $this->getModelClass($resourceName);
        
        if ($modelClass) {
            $resource = $modelClass::find($resourceId);
            
            if ($resource && isset($resource->user_id) && $resource->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access to this resource.');
            }
        }
    }

    /**
     * Get model class from resource name
     */
    private function getModelClass($resourceName): ?string
    {
        $modelMap = [
            'coa' => \App\Models\Coa::class,
            'satuan' => \App\Models\Satuan::class,
            'bahan_baku' => \App\Models\BahanBaku::class,
            'produk' => \App\Models\Produk::class,
            'pegawai' => \App\Models\Pegawai::class,
            'vendor' => \App\Models\Vendor::class,
            'aset' => \App\Models\Aset::class,
            'pembelian' => \App\Models\Pembelian::class,
            'penjualan' => \App\Models\Penjualan::class,
            'produksi' => \App\Models\Produksi::class,
            'penggajian' => \App\Models\Penggajian::class,
        ];

        return $modelMap[$resourceName] ?? null;
    }
}
