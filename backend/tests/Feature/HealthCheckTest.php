<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_returns_ok_or_degraded(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertJsonStructure([
            'status',
            'service',
            'version',
            'environment',
            'time',
            'checks' => ['database', 'redis'],
        ]);

        $this->assertContains($response->json('status'), ['ok', 'degraded']);
        $this->assertSame('rms-api', $response->json('service'));
    }
}
