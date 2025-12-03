<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Base validation
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:pelanggan,pegawai_pembelian,admin,owner'],
            'phone' => ['nullable', 'string', 'max:20'],
            'terms' => ['required', 'accepted'],
        ];

        // Conditional validation based on role
        if ($request->role === 'owner') {
            $rules['company_nama'] = ['required', 'string', 'max:255'];
            $rules['company_alamat'] = ['required', 'string'];
            $rules['company_email'] = ['required', 'email', 'max:255'];
            $rules['company_telepon'] = ['required', 'string', 'max:20'];
        } elseif (in_array($request->role, ['admin', 'pegawai_pembelian'])) {
            $rules['kode_perusahaan'] = ['required', 'string', 'exists:perusahaans,kode'];
        }
        // Pelanggan tidak perlu validasi company

        // Custom messages in Indonesian
        $messages = [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.string' => 'Nama lengkap harus berupa teks.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.max' => 'Username maksimal 255 karakter.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Peran wajib dipilih.',
            'role.in' => 'Peran yang dipilih tidak valid.',
            'phone.string' => 'Nomor telepon harus berupa teks.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'terms.required' => 'Anda harus menyetujui syarat dan ketentuan.',
            'terms.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
            'company_nama.required' => 'Nama perusahaan wajib diisi.',
            'company_nama.string' => 'Nama perusahaan harus berupa teks.',
            'company_nama.max' => 'Nama perusahaan maksimal 255 karakter.',
            'company_alamat.required' => 'Alamat perusahaan wajib diisi.',
            'company_alamat.string' => 'Alamat perusahaan harus berupa teks.',
            'company_email.required' => 'Email perusahaan wajib diisi.',
            'company_email.email' => 'Format email perusahaan tidak valid.',
            'company_email.max' => 'Email perusahaan maksimal 255 karakter.',
            'company_telepon.required' => 'Telepon perusahaan wajib diisi.',
            'company_telepon.string' => 'Telepon perusahaan harus berupa teks.',
            'company_telepon.max' => 'Telepon perusahaan maksimal 20 karakter.',
            'kode_perusahaan.required' => 'Kode perusahaan wajib diisi.',
            'kode_perusahaan.string' => 'Kode perusahaan harus berupa teks.',
            'kode_perusahaan.exists' => 'Kode perusahaan tidak ditemukan.',
        ];

        $validated = $request->validate($rules, $messages);

        // Create user
        $userData = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
        ];

        // Handle company data for owner
        if ($request->role === 'owner') {
            // Create company first
            $company = \App\Models\Company::create([
                'nama' => $validated['company_nama'],
                'alamat' => $validated['company_alamat'],
                'email' => $validated['company_email'],
                'telepon' => $validated['company_telepon'],
                'kode_perusahaan' => 'UMKM-' . strtoupper(substr(md5(time()), 0, 8)),
            ]);
            
            $userData['company_id'] = $company->id;
        }

        // Handle company association for admin/pegawai
        if (in_array($request->role, ['admin', 'pegawai_pembelian'])) {
            $company = \App\Models\Company::where('kode_perusahaan', $validated['kode_perusahaan'])->first();
            $userData['company_id'] = $company->id;
        }

        $user = User::create($userData);

        event(new Registered($user));

        Auth::login($user);

        // Redirect based on role
        if ($user->role === 'pelanggan') {
            return redirect()->route('pelanggan.dashboard');
        }

        return redirect(route('dashboard', absolute: false));
    }
}
