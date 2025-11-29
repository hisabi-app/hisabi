<?php

namespace App\Http\Commands\Brand\CreateBrandCommand;

readonly class CreateBrandCommand
{
    public function __construct(
        public array $data
    ) {}
}
