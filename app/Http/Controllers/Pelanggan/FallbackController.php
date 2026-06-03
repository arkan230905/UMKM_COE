<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
use App\Helpers\PerusahaanHelper;

class FallbackController extends Controller
{
    /**
     * Get redirect slug based on logged-in user or session
     */
    private function getRedirectSlug()
    {
        $perusahaan = null;
        
        // 1. Check logged in customer
        if (auth('pelanggan')->check()) {
            $user = auth('pelanggan')->user();
            if ($user && $user->perusahaan_id) {
                $perusahaan = Perusahaan::find($user->perusahaan_id);
            }
        }
        
        // 2. Check logged in owner/admin
        if (!$perusahaan && auth('web')->check()) {
            $user = auth('web')->user();
            if ($user && $user->perusahaan_id) {
                $perusahaan = Perusahaan::find($user->perusahaan_id);
            }
        }
        
        // 3. Check session
        if (!$perusahaan && session()->has('perusahaan_id')) {
            $perusahaan = Perusahaan::find(session('perusahaan_id'));
        }
        
        // 4. Fallback to first perusahaan
        if (!$perusahaan) {
            $perusahaan = Perusahaan::first();
        }
        
        if (!$perusahaan) {
            return null;
        }
        
        return PerusahaanHelper::getSlug($perusahaan);
    }

    /**
     * Redirect dari /pelanggan/dashboard ke /{company_slug}/pelanggan/dashboard
     */
    public function dashboard()
    {
        $slug = $this->getRedirectSlug();
        if (!$slug) {
            return response('Perusahaan tidak ditemukan', 404);
        }
        return redirect("/{$slug}/pelanggan/dashboard");
    }

    /**
     * Redirect dari /pelanggan/login ke /{company_slug}/pelanggan/login
     */
    public function login()
    {
        $slug = $this->getRedirectSlug();
        if (!$slug) {
            return response('Perusahaan tidak ditemukan', 404);
        }
        return redirect("/{$slug}/pelanggan/login");
    }

    /**
     * Redirect dari /pelanggan/cart ke /{company_slug}/pelanggan/cart
     */
    public function cart()
    {
        $slug = $this->getRedirectSlug();
        if (!$slug) {
            return response('Perusahaan tidak ditemukan', 404);
        }
        return redirect("/{$slug}/pelanggan/cart");
    }

    /**
     * Catch-all untuk redirect ke perusahaan yang sesuai
     */
    public function catchAll($path = '')
    {
        $slug = $this->getRedirectSlug();
        if (!$slug) {
            return response('Perusahaan tidak ditemukan', 404);
        }
        $newPath = $path ? "/{$slug}/pelanggan/{$path}" : "/{$slug}/pelanggan/dashboard";
        return redirect($newPath);
    }
}
