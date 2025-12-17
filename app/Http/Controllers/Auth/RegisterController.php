<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

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

        $rules = [
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
        ];

        // Cek apakah kolom kode ada di tabel perusahaan
        if (Schema::hasColumn('perusahaan', 'kode')) {
            $rules['kode_perusahaan'] = ['required_if:role,pegawai_pembelian,admin', 'string', 'exists:perusahaan,kode'];
        } else {
            // Jika kolom kode belum ada, gunakan validasi sederhana
            $rules['kode_perusahaan'] = ['required_if:role,pegawai_pembelian,admin', 'string'];
        }

        return Validator::make($data, $rules);
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
            $insertData = [
                'nama' => $data['company_nama'],
                'alamat' => $data['company_alamat'],
                'email' => $data['company_email'],
                'telepon' => $data['company_telepon'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Hanya tambahkan kode jika kolom ada
            if (Schema::hasColumn('perusahaan', 'kode')) {
                do {
                    $kode = 'PRS' . strtoupper(Str::random(6));
                    $exists = DB::table('perusahaan')->where('kode', $kode)->exists();
                } while ($exists);
                $insertData['kode'] = $kode;
            }

            $perusahaanId = DB::table('perusahaan')->insertGetId($insertData);
        } elseif (in_array($data['role'] ?? null, ['admin', 'pegawai_pembelian'], true)) {
            // Cek apakah kolom kode ada sebelum query
            if (Schema::hasColumn('perusahaan', 'kode')) {
                $perusahaan = DB::table('perusahaan')->where('kode', $data['kode_perusahaan'] ?? null)->first();
                $perusahaanId = $perusahaan?->id;
            }
        }

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];

        // Hanya tambahkan role jika kolom ada
        if (Schema::hasColumn('users', 'role')) {
            $userData['role'] = $data['role'] ?? 'pelanggan';
        }

        // Hanya tambahkan perusahaan_id jika kolom ada
        if (Schema::hasColumn('users', 'perusahaan_id')) {
            $userData['perusahaan_id'] = $perusahaanId;
        }

        return User::create($userData);
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
                return route('pelanggan.produk.index');
            default:
                return '/dashboard';
        }
    }
}
