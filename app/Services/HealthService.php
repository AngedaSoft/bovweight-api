<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

class HealthService
{
    /**
     * Verifica el estado del servidor y sus dependencias criticas.
     *
     * @return array{status: string, app: string, database: string, timestamp: string, version: string}
     */
    public function check(): array
    {
        $databaseStatus = $this->checkDatabase();

        $overallStatus = $databaseStatus === 'ok' ? 'ok' : 'degraded';

        return [
            'status' => $overallStatus,
            'app' => 'bovweight-api',
            'environment' => app()->environment(),
            'database' => $databaseStatus,
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '0.1.0'),
        ];
    }

    private function checkDatabase(): string
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            return 'ok';
        } catch (Throwable $e) {
            report($e);
            return 'down';
        }
    }
}
