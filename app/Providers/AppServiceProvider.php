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
use App\Domain\Ranges\CurrentMonth;
use App\Domain\Ranges\CurrentYear;
use App\Domain\Ranges\LastMonth;
use App\Domain\Ranges\LastTwelveMonths;
use App\Domain\Ranges\LastYear;

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

        // TODO: find a better way to load classes based on base class Range
        $this->app->bind('findRangeByKey', function($_, $params) {
            $key = $params['key'];

            return collect([
                new CurrentMonth,
                new LastMonth,
                new CurrentYear,
                new LastYear,
                new LastTwelveMonths,
            ])->first(function ($range) use($key) {
                return $key === $range->key();
            }) ?: null;
            
        });
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
