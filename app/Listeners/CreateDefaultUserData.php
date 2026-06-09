<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Database\Seeders\DefaultCoaSeeder;
use Database\Seeders\DefaultSatuanSeeder;

class CreateDefaultUserData
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        // Create default COA for new user
        $coaSeeder = new DefaultCoaSeeder();
        $coaSeeder->run($event->user->id);
        
        // Create default Satuan for new user
        $satuanSeeder = new DefaultSatuanSeeder();
        $satuanSeeder->run($event->user->id);
        
        // Create default Bahan Baku with COA mapping
        $bahanBakuSeeder = new \Database\Seeders\DefaultBahanBakuSeeder();
        $bahanBakuSeeder->run($event->user->id);
        
        // Create default Bahan Pendukung with COA mapping
        $bahanPendukungSeeder = new \Database\Seeders\DefaultBahanPendukungSeeder();
        $bahanPendukungSeeder->run($event->user->id);
        
        // NOTE: Jabatan TIDAK di-seed otomatis
        // User harus membuat Jabatan sendiri sesuai kebutuhan bisnis mereka
        // Uncomment baris di bawah jika ingin auto-seed Jabatan:
        // $jabatanSeeder = new \Database\Seeders\DefaultJabatanSeeder();
        // $jabatanSeeder->run($event->user->id);
    }
}
