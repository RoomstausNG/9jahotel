<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait PerformanceMonitoring
{
    protected $queryStartTime;
    protected $queryCount = 0;

    /**
     * Start monitoring query performance
     */
    protected function startQueryMonitoring(): void
    {
        $this->queryStartTime = microtime(true);
        $this->queryCount = 0;

        DB::listen(function ($query) {
            $this->queryCount++;

            // Log slow queries (over 100ms)
            if ($query->time > 100) {
                Log::warning('Slow Query Detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms',
                    'component' => static::class
                ]);
            }
        });
    }

    /**
     * End monitoring and log performance metrics
     */
    protected function endQueryMonitoring(string $operation = 'render'): void
    {
        if ($this->queryStartTime) {
            $totalTime = (microtime(true) - $this->queryStartTime) * 1000; // Convert to milliseconds

            // Log performance metrics
            Log::info('Component Performance', [
                'component' => static::class,
                'operation' => $operation,
                'total_time' => round($totalTime, 2) . 'ms',
                'query_count' => $this->queryCount,
                'avg_query_time' => $this->queryCount > 0 ? round($totalTime / $this->queryCount, 2) . 'ms' : '0ms'
            ]);

            // Reset counters
            $this->queryStartTime = null;
            $this->queryCount = 0;
        }
    }

    /**
     * Execute a block of code with performance monitoring
     */
    protected function withPerformanceMonitoring(callable $callback, string $operation = 'operation')
    {
        $this->startQueryMonitoring();
        $result = $callback();
        $this->endQueryMonitoring($operation);

        return $result;
    }
}
