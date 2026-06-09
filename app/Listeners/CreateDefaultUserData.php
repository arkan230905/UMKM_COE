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
        
        // NOTE: Bahan Baku dan Bahan Pendukung TIDAK di-seed otomatis
        // User harus membuat Bahan Baku/Pendukung sendiri sesuai kebutuhan bisnis mereka
        // Saat create, Observer akan auto-assign COA berdasarkan nama item
        
        // NOTE: Jabatan TIDAK di-seed otomatis
        // User harus membuat Jabatan sendiri sesuai kebutuhan bisnis mereka
        // Uncomment baris di bawah jika ingin auto-seed Jabatan:
        // $jabatanSeeder = new \Database\Seeders\DefaultJabatanSeeder();
        // $jabatanSeeder->run($event->user->id);
    }
}
