<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Searchable
{
    public static function search($query): Builder;
}
