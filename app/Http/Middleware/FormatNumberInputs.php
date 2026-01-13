<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FormatNumberInputs
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Format number inputs from formatted strings to raw numbers
        $numberFields = [
            'harga_perolehan',
            'biaya_perolehan', 
            'nilai_residu',
            'tarif_penyusutan'
        ];
        
        foreach ($numberFields as $field) {
            if ($request->has($field)) {
                $rawField = $field . '_raw';
                if ($request->has($rawField)) {
                    // Use raw value if available
                    $request->merge([$field => $request->input($rawField)]);
                } else {
                    // Parse formatted value
                    $value = $request->input($field);
                    $parsedValue = floatval(str_replace('.', '', $value));
                    $request->merge([$field => $parsedValue]);
                }
            }
        }
        
        return $next($request);
    }
}
