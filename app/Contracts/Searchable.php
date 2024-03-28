<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Searchable
{
    public function search($query): Builder;
}
