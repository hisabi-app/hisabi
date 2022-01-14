<?php

namespace App\Domain\Ranges;

class CurrentYear extends Range
{
    public function start()
    {
        return now()->startOfYear()->format("Y-m-d");
    }
    
    public function end()
    {
        return now()->endOfYear()->format("Y-m-d");
    }
}