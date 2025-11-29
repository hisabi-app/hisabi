<?php

namespace App\Http\Commands\Brand\UpdateBrandCommand;

readonly class UpdateBrandCommand
{
    public function __construct(
        public int $id,
        public array $data
    ) {}
}
