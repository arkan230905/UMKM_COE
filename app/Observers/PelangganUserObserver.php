<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Pelanggan;

class PelangganUserObserver
{
    public function created(User $user): void
    {
        if ($user->role !== 'pelanggan') return;

        Pelanggan::firstOrCreate(
            ['email' => $user->email],
            [
                'kode_pelanggan' => 'CUS' . now()->format('ym') . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'nama_pelanggan' => $user->name,
                'telepon'        => $user->phone ?? null,
                'email'          => $user->email,
            ]
        );
    }

    public function updated(User $user): void
    {
        if ($user->role !== 'pelanggan') return;

        Pelanggan::where('email', $user->getOriginal('email'))
            ->update([
                'nama_pelanggan' => $user->name,
                'telepon'        => $user->phone ?? null,
                'email'          => $user->email,
            ]);
    }
}
