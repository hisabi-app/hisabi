<?php

namespace App\Contracts;

interface HasPreviousRange 
{
    public function previousRangeStart();
    public function previousRangeEnd();
}