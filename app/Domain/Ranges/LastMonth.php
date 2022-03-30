<?php

namespace App\Domain\Ranges;

class LastMonth extends Range
{
    public function start()
    {
        return now()->subMonthNoOverflow()->startOfMonth()->format("Y-m-d");
    }
    
    public function end()
    {
        return now()->subMonthNoOverflow()->endOfMonth()->format("Y-m-d");
    }
}