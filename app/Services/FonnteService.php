<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FonnteService
{
    public function sendWhatsApp(string $phone, string $message): bool
    {
        $token = config('services.fonnte.token');
        $url = config('services.fonnte.url');

        if (! $token || ! $url) {
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->asForm()->post($url, [
            'target'  => $phone,
            'message' => $message,
        ]);

        return $response->successful();
    }
}
