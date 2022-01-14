<?php

namespace App\Domain\Ranges;

class LastYear extends Range
{
    public function start()
    {
        return now()->subYear()->startOfYear()->format("Y-m-d");
    }
    
    public function end()
    {
        return now()->subYear()->endOfYear()->format("Y-m-d");
    }
}