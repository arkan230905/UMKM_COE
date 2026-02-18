<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // Jika role owner, kita tidak membutuhkan kode_perusahaan sama sekali
        if (($data['role'] ?? null) === 'owner') {
            unset($data['kode_perusahaan']);
        }

        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['required', 'accepted'],
            'role' => ['required', 'in:owner,admin,pegawai_pembelian,pelanggan'],
            'company_nama' => ['required_if:role,owner', 'string', 'max:255'],
            'company_alamat' => ['required_if:role,owner', 'string'],
            'company_email' => ['required_if:role,owner', 'email', 'max:255'],
            'company_telepon' => ['required_if:role,owner', 'string', 'max:20'],
            'kode_perusahaan' => ['required_if:role,pegawai_pembelian,admin', 'string', 'exists:perusahaan,kode'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $perusahaanId = null;

        if (($data['role'] ?? null) === 'owner') {
            $kode = $this->generateCompanyCode();

            $perusahaanId = DB::table('perusahaan')->insertGetId([
                'nama' => $data['company_nama'],
                'alamat' => $data['company_alamat'],
                'email' => $data['company_email'],
                'telepon' => $data['company_telepon'],
                'kode' => $kode,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif (in_array($data['role'] ?? null, ['admin', 'pegawai_pembelian'], true)) {
            $perusahaan = DB::table('perusahaan')->where('kode', $data['kode_perusahaan'] ?? null)->first();
            $perusahaanId = $perusahaan?->id;
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'pelanggan',
            'perusahaan_id' => $perusahaanId,
        ]);
    }

    protected function generateCompanyCode(): string
    {
        return 'PR-' . strtoupper(uniqid());
    }

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
            case 'pegawai_pembelian':
                return route('transaksi.pembelian.index');
            case 'pelanggan':
                return route('pelanggan.dashboard');
            default:
                return '/dashboard';
        }
    }

    /**
     * Show the customer registration form.
     */
    public function showPelangganRegisterForm()
    {
        return view('pelanggan.auth.register');
    }

    /**
     * Handle a customer registration request.
     */
    public function registerPelanggan(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'no_telepon' => ['required', 'string', 'max:15'],
            'alamat' => ['required', 'string', 'max:500'],
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'pelanggan',
                'no_telepon' => $request->no_telepon,
                'alamat' => $request->alamat,
                'status' => 'aktif',
            ]);

            DB::commit();

            auth()->login($user);

            return redirect()->route('pelanggan.dashboard')
                ->with('success', 'Registrasi berhasil! Selamat datang di UMKM Desa Karangpakuan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Registrasi gagal. Silakan coba lagi.');
        }
    }
}
