<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Cart;

class CartComposer
{
    public function compose(View $view)
    {
        $cartCount = 0;
        if (auth('pelanggan')->check()) {
            $perusahaan = current_perusahaan();
            $query = Cart::where('user_id', auth('pelanggan')->id());
            
            if ($perusahaan) {
                $query->whereHas('produk', function ($q) use ($perusahaan) {
                    $q->withoutGlobalScopes()->where('user_id', $perusahaan->user_id);
                });
            }
            
            $cartCount = $query->sum('qty');
        }
        
        $view->with('cartCount', $cartCount);
    }
}
