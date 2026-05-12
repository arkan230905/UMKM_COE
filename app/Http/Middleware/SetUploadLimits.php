<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUploadLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set upload limits before processing request
        ini_set('upload_max_filesize', '8M');
        ini_set('post_max_size', '10M');
        ini_set('max_execution_time', '300');
        ini_set('memory_limit', '256M');
        
        // Try alternative methods
        @ini_alter('upload_max_filesize', '8M');
        @ini_alter('post_max_size', '10M');
        
        return $next($request);
    }
}
