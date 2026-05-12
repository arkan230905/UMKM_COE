<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Cart;

class CartComposer
{
    public function compose(View $view)
    {
        if (auth()->check()) {
            $cartCount = Cart::where('user_id', auth()->id())->sum('qty');
            $view->with('cartCount', $cartCount);
        } else {
            $view->with('cartCount', 0);
        }
    }
}
