<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MeasureRequestTiming
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = hrtime(true);
        $response = $next($request);
        $durationMs = (hrtime(true) - $startedAt) / 1_000_000;
        $formattedDuration = number_format($durationMs, 3, '.', '');

        $response->headers->set('Server-Timing', 'app;dur='.$formattedDuration);
        $response->headers->set('X-App-Time-Ms', $formattedDuration);
        $responseBytes = (string) strlen((string) $response->getContent());
        $response->headers->set('X-Response-Bytes', $responseBytes);
        $response->headers->set('Content-Length', $responseBytes);

        return $response;
    }
}