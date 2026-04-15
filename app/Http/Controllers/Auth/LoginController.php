<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\Kasir;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        // Clear any previous errors when showing fresh login form
        if (!request()->hasAny(['email', 'login_role'])) {
            session()->forget('errors');
        }
        
        return view('auth.login');
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    protected function redirectTo()
    {
        $user = auth()->user();

        if (! $user) {
            return '/dashboard';
        }

        switch ($user->role) {
            case 'owner':
            case 'admin':
                return '/dashboard';
            case 'pegawai':
                return route('pegawai.presensi.absen-wajah');
            case 'pegawai_pembelian':
                return route('pegawai-pembelian.dashboard');
            case 'kasir':
                return route('kasir.dashboard');
            default:
                return '/dashboard';
        }
    }

    /**
     * Handle custom login validation for pegawai and kasir (tanpa password)
     */
    public function login(Request $request)
    {
        // DEBUG: Log semua data yang diterima
        \Log::info('=== LOGIN REQUEST DEBUG ===');
        \Log::info('All Input:', $request->all());
        
        // Validasi role terlebih dahulu
        $request->validate([
            'login_role' => 'required|string|in:owner,admin,pegawai,pegawai_pembelian,kasir',
        ], [
            'login_role.required' => 'Silakan pilih role terlebih dahulu.',
            'login_role.in' => 'Role yang dipilih tidak valid.',
        ]);

        $role = $request->input('login_role');
        
        \Log::info('Role:', ['role' => $role]);

        // Validasi dinamis berdasarkan role
        $rules = [];
        $messages = [];

        $rules['email'] = 'required|email';
        $messages['email.required'] = 'Email wajib diisi.';
        $messages['email.email'] = 'Format email tidak valid.';

        // Untuk owner - password wajib
        if (in_array($role, ['owner'])) {
            $rules['password'] = 'required|string';
            $messages['password.required'] = 'Password wajib diisi.';
        }
        
        // Untuk role lainnya - kode perusahaan wajib
        if (in_array($role, ['admin', 'pegawai', 'pegawai_pembelian', 'kasir'])) {
            $rules['kode_perusahaan'] = 'required|string';
            $messages['kode_perusahaan.required'] = 'Kode perusahaan wajib diisi.';
        }

        \Log::info('Validation Rules:', $rules);

        // Validasi dengan rules yang sudah dibuat
        $credentials = $request->validate($rules, $messages);
        
        \Log::info('Validation Passed. Credentials:', $credentials);

        // Ambil data dari request
        $role = $request->input('login_role');
        $email = $request->input('email');
        $password = $request->input('password');
        $kodePerusahaan = $request->input('kode_perusahaan');

        // Login dengan kode perusahaan untuk admin
        if ($role === 'admin') {
            // Validasi kode perusahaan
            $perusahaan = \App\Models\Company::where('kode_perusahaan', $kodePerusahaan)->first();
            if (!$perusahaan) {
                return back()->withInput()->withErrors(['kode_perusahaan' => 'Kode perusahaan tidak valid.']);
            }
            
            $user = User::where('email', $email)->where('company_id', $perusahaan->id)->first();
            
            if (!$user) {
                return back()->withInput()->withErrors(['email' => 'Data admin tidak ditemukan di perusahaan ini. Email: ' . $email]);
            }
            
            // Cek apakah user role sesuai
            if ($user->role !== 'admin') {
                return back()->withInput()->withErrors(['email' => 'Role user tidak sesuai dengan Admin. Role user saat ini: ' . $user->role]);
            }
            
            // Login otomatis dengan kode perusahaan untuk admin
            auth()->login($user);
            $request->session()->regenerate();
            
            return redirect()->intended('/dashboard');
        }

        // Login dengan kode perusahaan untuk pegawai
        if ($role === 'pegawai') {
            // Validasi kode perusahaan
            $perusahaan = \App\Models\Company::where('kode_perusahaan', $kodePerusahaan)->first();
            if (!$perusahaan) {
                return back()->withInput()->withErrors(['kode_perusahaan' => 'Kode perusahaan tidak valid.']);
            }
            
            $pegawai = Pegawai::where('email', $email)->where('perusahaan_id', $perusahaan->id)->first();
            
            if (!$pegawai) {
                return back()->withInput()->withErrors(['email' => 'Data pegawai tidak ditemukan di perusahaan ini. Email: ' . $email]);
            }
            
            // Cek apakah user sudah ada untuk pegawai ini
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $pegawai->nama,
                    'email' => $pegawai->email,
                    'password' => Hash::make(Str::random(32)),
                    'role' => User::ROLE_PEGAWAI,
                    'pegawai_id' => $pegawai->id,
                    'company_id' => $perusahaan->id,
                    'email_verified_at' => now(),
                ]);
            }

            // Pastikan user terhubung ke pegawai dan perusahaan
            if (!$user->pegawai_id || !$user->company_id) {
                $user->update([
                    'pegawai_id' => $pegawai->id,
                    'company_id' => $perusahaan->id
                ]);
            }
            
            if ($user->role !== 'pegawai') {
                return back()->withInput()->withErrors(['email' => 'Role user tidak sesuai dengan pegawai. Role user saat ini: ' . $user->role]);
            }
            
            // Login otomatis dengan kode perusahaan untuk pegawai
            auth()->login($user);
            $request->session()->regenerate();
            
            return redirect()->intended(route('pegawai.presensi.absen-wajah'));
        }

        // Login dengan kode perusahaan untuk pegawai pembelian/gudang
        if ($role === 'pegawai_pembelian') {
            // Validasi kode perusahaan
            $perusahaan = \App\Models\Company::where('kode_perusahaan', $kodePerusahaan)->first();
            if (!$perusahaan) {
                return back()->withInput()->withErrors(['kode_perusahaan' => 'Kode perusahaan tidak valid.']);
            }
            
            $pegawai = Pegawai::where('email', $email)->where('perusahaan_id', $perusahaan->id)->first();
            
            if (!$pegawai) {
                return back()->withInput()->withErrors(['email' => 'Data pegawai tidak ditemukan di perusahaan ini. Email: ' . $email]);
            }
            
            // Cek apakah jabatan pegawai sesuai (gudang atau pembelian)
            $jabatanLower = strtolower($pegawai->jabatan);
            if (!str_contains($jabatanLower, 'gudang') && !str_contains($jabatanLower, 'pembelian')) {
                return back()->withInput()->withErrors(['email' => 'Jabatan pegawai tidak sesuai. Jabatan saat ini: ' . $pegawai->jabatan . '. Jabatan yang diizinkan: Bagian Gudang atau Pembelian.']);
            }
            
            // Cek apakah user sudah ada untuk pegawai ini
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $pegawai->nama,
                    'email' => $pegawai->email,
                    'password' => Hash::make(Str::random(32)),
                    'role' => User::ROLE_PEGAWAI_PEMBELIAN,
                    'pegawai_id' => $pegawai->id,
                    'company_id' => $perusahaan->id,
                    'email_verified_at' => now(),
                ]);
            }

            // Pastikan user terhubung ke pegawai dan perusahaan
            if (!$user->pegawai_id || !$user->company_id) {
                $user->update([
                    'pegawai_id' => $pegawai->id,
                    'company_id' => $perusahaan->id
                ]);
            }
            
            if ($user->role !== 'pegawai_pembelian') {
                return back()->withInput()->withErrors(['email' => 'Role user tidak sesuai dengan pegawai pembelian. Role user saat ini: ' . $user->role]);
            }
            
            // Login otomatis dengan kode perusahaan untuk pegawai pembelian
            auth()->login($user);
            $request->session()->regenerate();
            
            return redirect()->intended(route('pegawai-pembelian.dashboard'));
        }

        // Login dengan kode perusahaan untuk kasir
        if ($role === 'kasir') {
            // Validasi kode perusahaan
            $perusahaan = \App\Models\Company::where('kode_perusahaan', $kodePerusahaan)->first();
            if (!$perusahaan) {
                return back()->withInput()->withErrors(['kode_perusahaan' => 'Kode perusahaan tidak valid.']);
            }
            
            $kasir = Kasir::where('email', $email)->where('perusahaan_id', $perusahaan->id)->first();
            
            if (!$kasir) {
                return back()->withInput()->withErrors(['email' => 'Data kasir tidak ditemukan di perusahaan ini. Email: ' . $email]);
            }
            
            // Cek apakah user sudah ada untuk kasir ini
            $user = User::where('email', $email)->first();
            if (!$user) {
                return back()->withInput()->withErrors(['email' => 'Akun user untuk kasir ini belum dibuat.']);
            }
            
            // Pastikan user terhubung ke perusahaan
            if (!$user->company_id) {
                $user->update(['company_id' => $perusahaan->id]);
            }
            
            if ($user->role !== 'kasir') {
                return back()->withInput()->withErrors(['email' => 'Role user tidak sesuai dengan kasir. Role user saat ini: ' . $user->role]);
            }
            
            // Login otomatis dengan kode perusahaan untuk kasir
            auth()->login($user);
            $request->session()->regenerate();
            
            return redirect()->intended(route('kasir.dashboard'));
        }

        // Login dengan password untuk owner
        if ($role === 'owner') {
            // Cek apakah user owner ada
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return back()->withInput()->withErrors(['email' => 'Data owner tidak ditemukan di sistem. Email: ' . $email]);
            }
            
            // Cek apakah user role sesuai
            if ($user->role !== 'owner') {
                return back()->withInput()->withErrors(['email' => 'Role user tidak sesuai dengan Owner. Role user saat ini: ' . $user->role]);
            }
            
            // Login dengan password untuk owner
            if (auth()->attempt(['email' => $email, 'password' => $password], $request->filled('remember'))) {
                $request->session()->regenerate();
                return redirect()->intended($this->redirectPath());
            }

            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ]);
        }

        return back()->withErrors([
            'email' => 'Terjadi kesalahan saat login.',
        ]);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
