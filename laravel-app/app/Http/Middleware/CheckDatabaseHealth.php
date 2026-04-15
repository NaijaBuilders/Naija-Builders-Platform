<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckDatabaseHealth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if database is accessible
        try {
            DB::connection()->getPdo();
        } catch (QueryException) {
            // Database is unavailable - flush any existing sessions
            if ($request->session()->has('legacy_user_id')) {
                $request->session()->flush();
            }

            // If requesting an API endpoint
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Database unavailable',
                    'message' => 'The application database is currently unavailable. Please check your database connection.',
                ], 503);
            }

            // For HTML requests, redirect with error
            return redirect('/login.php?error=db_unavailable')
                ->with('database_error', 'Database connection failed. Please try again later.');
        } catch (\Exception) {
            // Any other connection error
            if ($request->session()->has('legacy_user_id')) {
                $request->session()->flush();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Service unavailable',
                    'message' => 'The application is temporarily unavailable.',
                ], 503);
            }

            return redirect('/login.php?error=service_unavailable');
        }

        return $next($request);
    }
}
