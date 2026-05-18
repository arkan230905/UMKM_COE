<?php

namespace App\Http\Controllers\Pelanggan\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pelanggan;

class LoginController extends Controller
{
    /**
     * Show the customer login form.
     */
    public function showLoginForm(Request $request)
    {
        // Get perusahaan from middleware
        $perusahaan = $request->attributes->get('perusahaan');
        
        if (!$perusahaan) {
            return redirect('/')->with('error', 'Perusahaan tidak ditemukan');
        }

        // Hanya redirect ke dashboard jika sudah login sebagai pelanggan
        if (Auth::guard('pelanggan')->check()) {
            return redirect()->route('pelanggan.dashboard', ['perusahaan_slug' => perusahaan_slug($perusahaan)]);
        }

        // Get redirect URL and product info from query parameters
        $redirect = $request->get('redirect', 'pelanggan.dashboard');
        $productId = $request->get('product');

        return view('pelanggan.auth.login-register', compact('redirect', 'productId', 'perusahaan'));
    }

    /**
     * Handle a customer login request.
     */
    public function login(Request $request)
    {
        // Get perusahaan from middleware
        $perusahaan = $request->attributes->get('perusahaan');
        
        if (!$perusahaan) {
            return redirect('/')->with('error', 'Perusahaan tidak ditemukan');
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt login dengan guard 'pelanggan'
        if (Auth::guard('pelanggan')->attempt($credentials)) {
            $request->session()->regenerate();

            // Get redirect URL from request or default to dashboard
            $redirect = $request->input('redirect', 'pelanggan.dashboard');
            
            // If coming from catalog with product, add to cart then redirect to cart
            if ($redirect === 'catalog' && $request->input('product')) {
                // Add product to cart
                $cartController = new \App\Http\Controllers\Pelanggan\CartController();
                $cartRequest = new \Illuminate\Http\Request();
                $cartRequest->merge([
                    'produk_id' => $request->input('product'),
                    'qty' => 1
                ]);
                $cartController->store($cartRequest);
                
                return redirect()->route('pelanggan.cart', ['perusahaan_slug' => perusahaan_slug($perusahaan)])
                    ->with('success', 'Login berhasil! Produk telah ditambahkan ke keranjang.');
            }

            return redirect()->route($redirect, ['perusahaan_slug' => perusahaan_slug($perusahaan)]);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email'));
    }

    /**
     * Log the customer out of the application.
     */
    public function logout(Request $request)
    {
        // Get perusahaan before logout
        $perusahaan = $request->attributes->get('perusahaan');
        $perusahaanSlug = $perusahaan ? perusahaan_slug($perusahaan) : 'default';

        Auth::guard('pelanggan')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pelanggan.login', ['perusahaan_slug' => $perusahaanSlug])
            ->with('success', 'Anda telah logout.');
    }

    /**
     * Handle customer registration.
     */
    public function register(Request $request)
    {
        // Get perusahaan from middleware
        $perusahaan = $request->attributes->get('perusahaan');
        
        if (!$perusahaan) {
            return redirect('/')->with('error', 'Perusahaan tidak ditemukan');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            // Create new pelanggan user
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => bcrypt($validated['password']),
                'plain_password' => $validated['password'], // CRITICAL: Store plain password for display
                'role' => 'pelanggan',
                'email_verified_at' => now(),
                // CRITICAL: Set user_id to NULL for pelanggan (they don't belong to any owner initially)
                // They will be visible to all owners in master data
                'user_id' => null,
            ]);

            // Auto-login after registration
            Auth::guard('pelanggan')->login($user);

            return redirect()->route('pelanggan.dashboard', ['perusahaan_slug' => perusahaan_slug($perusahaan)])
                ->with('success', 'Registrasi berhasil! Selamat datang di toko kami.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal melakukan registrasi: ' . $e->getMessage())
                ->withInput($request->only('name', 'email', 'phone'));
        }
    }
}
