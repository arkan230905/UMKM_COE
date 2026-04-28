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
    }
}
