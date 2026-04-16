<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Database\Seeders\CoaTemplateSeeder;
use Illuminate\Support\Facades\Log;

class SetupUserData
{
    /**
     * Handle the event.
     * Setup data awal untuk user baru yang registrasi
     */
    public function handle(UserRegistered $event): void
    {
        try {
            // Jika user adalah owner dan memiliki company_id, copy COA template
            if ($event->user->role === 'owner' && $event->companyId) {
                Log::info('Setting up COA for new user', [
                    'user_id' => $event->user->id,
                    'company_id' => $event->companyId,
                ]);

                // Copy COA template untuk company ini
                CoaTemplateSeeder::copyCoaTemplateForCompany($event->companyId);

                Log::info('COA setup completed for new user', [
                    'user_id' => $event->user->id,
                    'company_id' => $event->companyId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to setup user data', [
                'user_id' => $event->user->id,
                'company_id' => $event->companyId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
