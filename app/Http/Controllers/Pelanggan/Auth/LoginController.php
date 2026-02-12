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
        // If already logged in as customer, redirect to dashboard
        if (Auth::guard('pelanggan')->check()) {
            return redirect()->route('pelanggan.dashboard');
        }

        // Get redirect URL and product info from query parameters
        $redirect = $request->get('redirect', 'pelanggan.dashboard');
        $productId = $request->get('product');

        return view('pelanggan.auth.login', compact('redirect', 'productId'));
    }

    /**
     * Handle a customer login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

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
                
                return redirect()->route('pelanggan.cart')
                    ->with('success', 'Login berhasil! Produk telah ditambahkan ke keranjang.');
            }

            return redirect()->route($redirect)->with('success', 'Login berhasil!');
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
        Auth::guard('pelanggan')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pelanggan.login')->with('success', 'Anda telah logout.');
    }
}
