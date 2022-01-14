<?php

namespace App\Domain\Ranges;

class CurrentMonth extends Range
{
    public function start()
    {
        return now()->startOfMonth()->format("Y-m-d");
    }
    
    public function end()
    {
        return now()->endOfMonth()->format("Y-m-d");
    }
}