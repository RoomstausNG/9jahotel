<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseOptimizationService;
use App\Services\CacheService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OptimizeApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize 
                            {--clear-cache : Clear all application cache}
                            {--optimize-db : Apply database optimizations}
                            {--reset-cache : Clear and rebuild application cache}
                            {--full : Run all optimization steps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the application for better performance';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseOptimizationService $dbService, CacheService $cacheService)
    {
        $this->info('Starting application optimization...');
        
        $clearCache = $this->option('clear-cache') || $this->option('full');
        $optimizeDb = $this->option('optimize-db') || $this->option('full');
        $resetCache = $this->option('reset-cache') || $this->option('full');
        
        if ($clearCache || $resetCache) {
            $this->clearApplicationCache();
        }
        
        if ($optimizeDb) {
            $this->optimizeDatabase($dbService);
        }
        
        if ($resetCache) {
            $this->rebuildApplicationCache();
        }
        
        $this->info('Application optimization completed successfully!');
        
        return Command::SUCCESS;
    }
    
    /**
     * Clear all application cache
     */
    protected function clearApplicationCache()
    {
        $this->info('Clearing application cache...');
        
        Artisan::call('cache:clear');
        $this->line('Application cache cleared');
        
        Artisan::call('config:clear');
        $this->line('Configuration cache cleared');
        
        Artisan::call('route:clear');
        $this->line('Route cache cleared');
        
        Artisan::call('view:clear');
        $this->line('View cache cleared');
        
        Artisan::call('optimize:clear');
        $this->line('Compiled classes cleared');
        
        $this->info('All application cache cleared successfully!');
    }
    
    /**
     * Optimize database structure and indexes
     */
    protected function optimizeDatabase(DatabaseOptimizationService $dbService)
    {
        $this->info('Optimizing database...');
        
        // Apply optimized indexes
        $results = $dbService->applyOptimizedIndexes();
        foreach ($results as $result) {
            $this->line('- ' . $result);
        }
        
        // Optimize tables
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            if ($dbService->optimizeTable($table)) {
                $this->line("- Table '{$table}' optimized");
            }
        }
        
        $this->info('Database optimization completed!');
    }
    
    /**
     * Rebuild application cache
     */
    protected function rebuildApplicationCache()
    {
        $this->info('Rebuilding application cache...');
        
        Artisan::call('config:cache');
        $this->line('Configuration cache rebuilt');
        
        Artisan::call('route:cache');
        $this->line('Route cache rebuilt');
        
        Artisan::call('view:cache');
        $this->line('View cache rebuilt');
        
        Artisan::call('optimize');
        $this->line('Application optimized');
        
        $this->info('Application cache rebuilt successfully!');
    }
}
