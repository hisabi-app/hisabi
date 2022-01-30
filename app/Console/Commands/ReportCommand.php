<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Notifications\FinanceMonthlyReportNotification;

class ReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report the finance data for the current month';

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
        User::first()->notify(new FinanceMonthlyReportNotification);

        return 0;
    }
}
