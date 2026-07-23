<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IdempotencyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $idempotencyKey = $request->header('X-Idempotency-Key');

        if (blank($idempotencyKey)) {
            return response()->json([
                'message' => 'X-Idempotency-Key header is required.',
            ], 422);
        }

        $route = $request->route();

        $operation = $route?->getName()
            ?? $route?->uri()
            ?? $request->path();


        $key = sprintf(
            'idempotency:%s:%s:%s:%s',
            $request->user()->id,
            $request->method(),
            $operation,
            hash('sha256', $request->header('X-Idempotency-Key'))
        );

        if (! Cache::add($key, ['state' => 'processing'], now()->addMinutes(5))) {
            $record = Cache::get($key);

            if (($record['state'] ?? null) === 'completed') {
                return response($record['body'], $record['status'])
                    ->header('Content-Type', 'application/json');
            }

            return response()->json([
                'message' => 'A request with this idempotency key is already processing.',
            ], 409);
        }


        /** @var JsonResponse $response */
        $response = $next($request);

        if (!$response->isServerError()) {
            Cache::put($key, [
                'state' => 'completed',
                'status' => $response->getStatusCode(),
                'body' => $response->getContent(),
            ], now()->addDay());
        } else {
            Cache::forget($key);
        }

        return $response;

    }

}
