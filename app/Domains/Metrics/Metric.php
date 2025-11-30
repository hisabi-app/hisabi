<?php

namespace App\Domains\Metrics;

use App\Contracts\HasPreviousRange;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

abstract class Metric
{
    protected ?string $from = null;
    protected ?string $to = null;

    public function __construct(?string $from = null, ?string $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }

    abstract public function calculate(): array;

    protected function getStartDate(): ?string
    {
        return $this->from;
    }

    protected function getEndDate(): ?string
    {
        return $this->to;
    }

    protected function hasDateRange(): bool
    {
        return $this->from !== null && $this->to !== null;
    }

    protected function getPreviousRange(): ?array
    {
        if (!$this->hasDateRange()) {
            return null;
        }

        $from = Carbon::parse($this->from);
        $to = Carbon::parse($this->to);
        $daysDiff = $from->diffInDays($to) + 1;

        return [
            'start' => $from->copy()->subDays($daysDiff)->format('Y-m-d'),
            'end' => $from->copy()->subDay()->format('Y-m-d'),
        ];
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
}
