<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
use App\Helpers\PerusahaanHelper;

class FallbackController extends Controller
{
    /**
     * Redirect dari /pelanggan/dashboard ke /pt-arkan-trans-jaya/pelanggan/dashboard
     */
    public function dashboard()
    {
        $perusahaan = Perusahaan::first();
        if (!$perusahaan) {
            return response('Perusahaan tidak ditemukan', 404);
        }
        $slug = PerusahaanHelper::getSlug($perusahaan);
        return redirect("/{$slug}/pelanggan/dashboard");
    }

    /**
     * Redirect dari /pelanggan/login ke /pt-arkan-trans-jaya/pelanggan/login
     */
    public function login()
    {
        $perusahaan = Perusahaan::first();
        if (!$perusahaan) {
            return response('Perusahaan tidak ditemukan', 404);
        }
        $slug = PerusahaanHelper::getSlug($perusahaan);
        return redirect("/{$slug}/pelanggan/login");
    }

    /**
     * Redirect dari /pelanggan/cart ke /pt-arkan-trans-jaya/pelanggan/cart
     */
    public function cart()
    {
        $perusahaan = Perusahaan::first();
        if (!$perusahaan) {
            return response('Perusahaan tidak ditemukan', 404);
        }
        $slug = PerusahaanHelper::getSlug($perusahaan);
        return redirect("/{$slug}/pelanggan/cart");
    }

    /**
     * Catch-all untuk redirect ke perusahaan pertama
     */
    public function catchAll($path = '')
    {
        $perusahaan = Perusahaan::first();
        if (!$perusahaan) {
            return response('Perusahaan tidak ditemukan', 404);
        }
        $slug = PerusahaanHelper::getSlug($perusahaan);
        $newPath = $path ? "/{$slug}/pelanggan/{$path}" : "/{$slug}/pelanggan/dashboard";
        return redirect($newPath);
    }
}
