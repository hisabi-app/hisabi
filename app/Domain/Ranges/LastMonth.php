<?php

namespace App\Domain\Ranges;

class LastMonth extends Range
{
    public function start()
    {
        return now()->subMonth()->startOfMonth()->format("Y-m-d");
    }
    
    public function end()
    {
        return now()->subMonth()->endOfMonth()->format("Y-m-d");
    }
}