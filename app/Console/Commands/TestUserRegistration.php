<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Perusahaan;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\Hash;

class TestUserRegistration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-registration {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user registration flow and data seeding';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        // Cek apakah user sudah ada
        if (User::where('email', $email)->exists()) {
            $this->error("User dengan email {$email} sudah ada!");
            return 1;
        }

        $this->info("Membuat user baru dengan email: {$email}");

        // Buat perusahaan dummy
        $perusahaan = Perusahaan::create([
            'nama' => 'Test Company ' . time(),
            'alamat' => 'Test Address',
            'telepon' => '08123456789',
            'email' => $email,
        ]);

        $this->info("Perusahaan dibuat dengan ID: {$perusahaan->id}");

        // Buat user
        $user = User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'owner',
            'perusahaan_id' => $perusahaan->id,
        ]);

        $this->info("User dibuat dengan ID: {$user->id}");

        // Trigger event
        $this->info("Triggering UserRegistered event...");
        event(new UserRegistered($user, $perusahaan->id));

        $this->info("Event triggered!");
        
        // Verifikasi data
        $this->info("\n=== VERIFIKASI DATA ===");
        
        $coaCount = \App\Models\Coa::where('company_id', $perusahaan->id)->count();
        $this->info("COA untuk perusahaan ini: {$coaCount}");
        
        $satuanCount = \App\Models\Satuan::count();
        $this->info("Total Satuan (global): {$satuanCount}");
        
        $jabatanCount = \App\Models\Jabatan::count();
        $this->info("Total Jabatan (global): {$jabatanCount}");
        
        $pegawaiCount = \App\Models\Pegawai::count();
        $this->info("Total Pegawai (global): {$pegawaiCount}");
        
        $this->info("\n✓ Test selesai!");
        
        return 0;
    }
}
