<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    public function sendWhatsApp(string $phone, string $message): bool
    {
        $token = config('services.fonnte.token');
        $url = config('services.fonnte.url');

        if (! $token || ! $url) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])
                ->timeout(10)
                ->retry(2, 200)
                ->asForm()
                ->post($url, [
                    'target'  => $phone,
                    'message' => $message,
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Fonnte WhatsApp send failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
