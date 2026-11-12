<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            ActivityLog::create([
                'method' => $request->method(),
                'path' => $request->path(),
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'meta' => [
                    'status' => $response->getStatusCode() ?? null,
                    'query' => $request->query(),
                ],
            ]);
        } catch (\Throwable $e) {
            // silently ignore logging errors
        }

        return $response;
    }
}
