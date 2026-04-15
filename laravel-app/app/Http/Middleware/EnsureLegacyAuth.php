<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureLegacyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionUserId = (int) $request->session()->get('legacy_user_id', 0);
        if ($sessionUserId <= 0) {
            return redirect('/login.php');
        }

        try {
            $userExists = DB::table('users')
                ->where('id', $sessionUserId)
                ->exists();
        } catch (QueryException) {
            $request->session()->flush();
            return redirect('/login.php?error=db_unavailable');
        }

        if (!$userExists) {
            $request->session()->flush();
            return redirect('/login.php?error=invalid_credentials');
        }

        return $next($request);
    }
}
