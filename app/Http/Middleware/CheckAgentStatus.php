<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
class CheckAgentStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('agent')->check()) {
            $user = agent();
            if ($user->status  && $user->ev  && $user->sv  && $user->tv) {
                return $next($request);
            } else {
                return redirect()->route('agent.authorization');
            }
        }
        abort(403);
    }
}
