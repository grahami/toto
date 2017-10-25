<?php
/**
 * Log all system access to a log file
 */

namespace App\Http\Middleware;

use Closure;
use Request;
use Route;
use App\Helpers\EventHelper;
use Log;

class Access
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $ipAddress = Request::ip();
        $route = Route::current()->uri();
        $method = $method = Request::method();
        $userAgent = $request->header('user-agent');
        $accept = $request->header('accept');
        $acceptEncoding = $request->header('accept-encoding');
        $acceptLanguage = $request->header('accept-language');

        EventHelper::log('ACCESS', 'AUDIT', ['ipAddress' => $ipAddress, 'route' => $route, 'method' => $method,
            'userAgent' => $userAgent, 'httpAccept' => $accept, 'acceptEncoding' => $acceptEncoding,
            'acceptLanguage' => $acceptLanguage], []);

        return $next($request);
    }
}
