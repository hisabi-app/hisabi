<?php

namespace App\Domains\Metrics;

use App\Contracts\HasPreviousRange;
use Illuminate\Support\Facades\DB;

abstract class Metric
{
    protected ?string $range = null;

    public function __construct(?string $range = null)
    {
        $this->range = $range;
    }

    abstract public function calculate(): array;

    protected function getRange(): mixed
    {
        if (!$this->range) {
            return null;
        }
        return app('findRangeByKey', ["key" => $this->range]);
    }

    protected function getDateFormat(string $format): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $sqliteFormat = str_replace(['%Y', '%m', '%d'], ['%Y', '%m', '%d'], $format);
            return "strftime('{$sqliteFormat}', created_at)";
        }
        return "date_format(created_at, '{$format}')";
    }

    protected function hasPreviousRange($rangeData): bool
    {
        return is_a($rangeData, HasPreviousRange::class);
    }
}
