<?php

namespace App\Domain\Ranges;

class AllTime extends Range
{
    public function start()
    {
        return now()->format("Y-m-d");
    }
    
    public function end()
    {
        return now()->format("Y-m-d");
    }
}