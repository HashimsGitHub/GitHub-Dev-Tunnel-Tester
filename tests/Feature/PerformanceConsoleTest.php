<?php

namespace Tests\Feature;

use Tests\TestCase;

class PerformanceConsoleTest extends TestCase
{
    public function test_dashboard_shows_request_details_and_timing_headers(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Tunnel Gauge')
            ->assertSee('REQUEST SNAPSHOT')
            ->assertHeader('Server-Timing')
            ->assertHeader('X-App-Time-Ms')
            ->assertHeader('X-Response-Bytes')
            ->assertHeader('Content-Length');
    }

    public function test_health_endpoint_returns_runtime_and_request_diagnostics(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure([
                'app',
                'laravel',
                'php',
                'time',
                'request' => ['method', 'host', 'protocol', 'ip'],
            ]);
    }

    public function test_benchmark_bounds_requested_iterations(): void
    {
        $this->getJson('/benchmark?iterations=1')
            ->assertOk()
            ->assertJsonPath('iterations', 1000)
            ->assertJsonStructure(['durationMs', 'checksum', 'memoryMb']);
    }
}