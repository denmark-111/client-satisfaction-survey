<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateClientService
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $this->extractApiKey($request);

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated client service. Missing API key in X-API-Key or Authorization header.',
            ], 401);
        }

        $client = ApiClient::findByPlainKey($apiKey);

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated client service. Invalid API key.',
            ], 401);
        }

        if (! $client->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Client service is inactive or unauthorized.',
            ], 403);
        }

        // Record last used timestamp
        $client->recordUsage();

        // Bind authenticated client model to request attributes
        $request->attributes->set('client_service', $client);

        return $next($request);
    }

    /**
     * Extract the API key from request headers.
     */
    protected function extractApiKey(Request $request): ?string
    {
        if ($request->hasHeader('X-API-Key')) {
            $key = $request->header('X-API-Key');
            if (is_string($key) && trim($key) !== '') {
                return trim($key);
            }
        }

        $authHeader = $request->header('Authorization');
        if (is_string($authHeader) && preg_match('/^Bearer\s+(.+)$/i', trim($authHeader), $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
