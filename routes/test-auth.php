<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-auth-debug', function() {
    return response()->json([
        'auth_check' => auth()->check(),
        'user_id' => auth()->id(),
        'user_email' => auth()->user()->email ?? null,
        'user_name' => auth()->user()->name ?? null,
    ]);
});
