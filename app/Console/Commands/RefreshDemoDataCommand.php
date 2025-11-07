<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RefreshDemoDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hisabi:refresh-demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh demo data by running fresh migrations and seeding demo account';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Check if demo mode is enabled
        if (!config('hisabi.demo.enabled')) {
            $this->warn('Demo mode is not enabled. Skipping demo data refresh.');
            Log::info('Demo refresh skipped: Demo mode is not enabled');
            return 0;
        }

        $this->info('Starting demo data refresh...');
        Log::info('Demo data refresh started');

        try {
            // Run fresh migrations (drops all tables and recreates them)
            $this->info('Running fresh migrations...');
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info('Migrations completed.');

            // Seed the demo account data
            $this->info('Seeding demo account data...');
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\DemoAccountSeeder',
                '--force' => true
            ]);
            $this->info('Demo account seeded successfully.');

            $this->info('✓ Demo data refresh completed successfully!');
            Log::info('Demo data refresh completed successfully');

            return 0;
        } catch (\Exception $e) {
            $this->error('Error refreshing demo data: ' . $e->getMessage());
            Log::error('Demo data refresh failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}
