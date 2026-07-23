<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IdempotencyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $idempotencyKey = $request->header('X-Idempotency-Key');

        if (!$idempotencyKey) {
            return $next($request);
        }

        $existingTransaction = Cache::get("X-Idempotency-Key:{$request->user()->id}");

        if ($existingTransaction) {
            return response()->json([
                'message' => 'please wait before next request'
            ], 200);
        }

        return $next($request);
    }
}
