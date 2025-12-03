<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordWaController extends Controller
{
    public function showRequestForm()
    {
        return view('auth.passwords.wa-request');
    }

    public function sendOtp(Request $request, FonnteService $fonnte)
    {
        $request->validate([
            'phone' => ['required', 'string', 'exists:users,phone'],
        ]);

        $otp = (string) random_int(100000, 999999);

        DB::table('password_otp_resets')->updateOrInsert(
            ['phone' => $request->phone],
            ['otp' => $otp, 'created_at' => now()]
        );

        $message = "Kode reset password UMKM Anda: {$otp}. Jangan berikan kode ini kepada siapa pun.";

        $sent = $fonnte->sendWhatsApp($request->phone, $message);

        if (! $sent) {
            return back()->withErrors(['phone' => 'Gagal mengirim kode ke WhatsApp. Coba lagi.']);
        }

        return redirect()->route('password.wa.verify.form')->with(['status' => 'Kode OTP telah dikirim ke WhatsApp Anda.', 'phone' => $request->phone]);
    }

    public function showVerifyForm(Request $request)
    {
        $phone = session('phone');
        return view('auth.passwords.wa-verify', compact('phone'));
    }

    public function resetWithOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'exists:users,phone'],
            'otp' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_otp_resets')
            ->where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->first();

        if (! $record) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid.']);
        }

        // Opsional: cek masa berlaku OTP (misal 10 menit)
        // if (now()->diffInMinutes($record->created_at) > 10) { ... }

        $user = User::where('phone', $request->phone)->first();

        if (! $user) {
            return back()->withErrors(['phone' => 'User tidak ditemukan.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_otp_resets')->where('phone', $request->phone)->delete();

        return redirect()->route('login')->with('status', 'Password berhasil direset. Silakan login kembali.');
    }
}
