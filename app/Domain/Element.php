<?php

namespace App\Domain;

use JsonSerializable;
use Illuminate\Support\Str;

abstract class Element implements JsonSerializable
{
    protected $name;

    public function humanizedName()
    {
        return Str::title(Str::snake(class_basename(get_class($this)), ' '));
    }

    public function name()
    {
        return $this->name ?: $this->humanizedName();
    }

    public function jsonSerialize(): mixed
    {
        return [
            'name' => $this->name(),
        ];
    }
}