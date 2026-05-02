<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Database\Seeders\DefaultCoaSeederBaru;
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
        // Create default COA for new user (Jasuke format - 50 accounts)
        $coaSeeder = new DefaultCoaSeederBaru();
        $coaSeeder->run($event->user->id);
        
        // Create default Satuan for new user (16 units)
        $satuanSeeder = new DefaultSatuanSeeder();
        $satuanSeeder->run($event->user->id);
    }
}
