<?php

namespace App\Domain\Ranges;

use JsonSerializable;
use Illuminate\Support\Str;

abstract class Range implements JsonSerializable
{
    protected $name;
    protected $start;
    protected $end;

    public function humanizedName()
    {
        return Str::title(Str::snake(class_basename(get_class($this)), ' '));
    }

    public function key()
    {
        return Str::slug($this->name(), '-', null);
    }

    public function name()
    {
        return $this->name ?: $this->humanizedName();
    }

    abstract public function start();
    
    abstract public function end();

    /**
     * Prepare the element for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'key' => $this->key(),
            'name' => $this->name(),
            'start' => $this->start(),
            'end' => $this->end(),
        ];
    }
}