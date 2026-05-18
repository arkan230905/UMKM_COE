<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Perusahaan;

class PerusahaanComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Get perusahaan from request attributes (set by middleware)
        $perusahaan = request()->attributes->get('perusahaan');
        
        // If not in request, try to get from session
        if (!$perusahaan) {
            $perusahaan = session('perusahaan');
        }
        
        // If still not found, try to get from route parameter
        if (!$perusahaan) {
            $slug = request()->route('perusahaan_slug');
            if ($slug) {
                $perusahaan = Perusahaan::where('slug', strtolower($slug))
                    ->orWhere('kode', strtoupper($slug))
                    ->orWhere('nama', 'like', '%' . str_replace('-', ' ', $slug) . '%')
                    ->first();
            }
        }
        
        // Share perusahaan with the view
        if ($perusahaan) {
            $view->with('perusahaan', $perusahaan);
        }
    }
}
