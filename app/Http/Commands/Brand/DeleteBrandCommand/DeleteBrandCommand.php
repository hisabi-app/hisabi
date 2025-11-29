<?php

namespace App\Http\Commands\Brand\DeleteBrandCommand;

readonly class DeleteBrandCommand
{
    public function __construct(
        public int $id
    ) {}
}
