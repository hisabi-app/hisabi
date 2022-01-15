<?php

namespace App\Domain\Ranges;

class LastTwelveMonths extends Range
{
    public function start()
    {
        return now()->subMonths(12)->format("Y-m-d");
    }
    
    public function end()
    {
        return now()->format("Y-m-d");
    }
}