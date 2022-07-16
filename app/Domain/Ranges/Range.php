<?php

namespace App\Domain\Ranges;

use App\Domain\Element;
use Illuminate\Support\Str;

abstract class Range extends Element
{
    protected $start;
    protected $end;

    public function key()
    {
        return Str::slug($this->name(), '-', null);
    }

    abstract public function start();
    
    abstract public function end();

    /**
     * Prepare the element for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return array_merge(parent::jsonSerialize(), [
            'key' => $this->key(),
            'start' => $this->start(),
            'end' => $this->end(),
        ]);
    }
}