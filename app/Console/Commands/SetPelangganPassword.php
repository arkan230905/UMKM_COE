<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetPelangganPassword extends Command
{
    protected $signature = 'set:pelanggan-password {email} {password}';
    protected $description = 'Set plain password for a pelanggan';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $pelanggan = User::where('email', $email)
            ->where('role', 'pelanggan')
            ->whereNull('user_id')
            ->first();
        
        if (!$pelanggan) {
            $this->error("Pelanggan dengan email {$email} tidak ditemukan");
            return;
        }
        
        // Verify password matches hash
        if (!Hash::check($password, $pelanggan->password)) {
            $this->error("Password tidak sesuai dengan hash yang tersimpan");
            return;
        }
        
        // Update plain password
        $pelanggan->update([
            'plain_password' => $password
        ]);
        
        $this->info("✓ Plain password untuk {$email} berhasil diupdate ke: {$password}");
    }
}
