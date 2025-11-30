<?php

namespace App\Providers;

use App\BusinessLogic\SmsParser;
use Illuminate\Support\ServiceProvider;
use App\BusinessLogic\SmsTemplateDetector;
use App\BusinessLogic\ReportManager;
use App\BusinessLogic\SmsTransactionProcessor;
use App\Contracts\SmsParser as SmsParserContract;
use App\Contracts\SmsTemplateDetector as SmsTemplateDetectorContract;
use App\Contracts\ReportManager as ReportManagerContract;
use App\Contracts\SmsTransactionProcessor as SmsTransactionProcessorContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(SmsParserContract::class, SmsParser::class);
        $this->app->bind(SmsTemplateDetectorContract::class, SmsTemplateDetector::class);
        $this->app->bind(SmsTransactionProcessorContract::class, SmsTransactionProcessor::class);
        $this->app->bind(ReportManagerContract::class, ReportManager::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
