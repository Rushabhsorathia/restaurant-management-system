<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $services = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
        ];

        $allOk = ! in_array(false, array_column($services, 'ok'), true);

        return response()->json([
            'status' => $allOk ? 'ok' : 'degraded',
            'service' => 'rms-api',
            'version' => $this->appVersion(),
            'environment' => app()->environment(),
            'time' => Carbon::now()->toIso8601String(),
            'checks' => $services,
        ], $allOk ? 200 : 503);
    }

    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['ok' => true, 'driver' => DB::connection()->getDriverName()];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function checkRedis(): array
    {
        try {
            $pong = Redis::connection()->ping();
            $ok = $pong === true || $pong === 'PONG' || $pong === '+PONG' || $pong === 1;

            return ['ok' => $ok, 'response' => is_string($pong) ? $pong : 'PONG'];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function appVersion(): string
    {
        $versionFile = base_path('VERSION');

        if (is_file($versionFile)) {
            return trim((string) file_get_contents($versionFile));
        }

        return '0.1.0';
    }
}
