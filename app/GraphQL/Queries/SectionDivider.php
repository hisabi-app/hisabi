<?php

namespace App\GraphQL\Queries;

use App\Domain\Element;

class SectionDivider extends Element
{
    protected $component = "section-divider";
    protected $width = 'full';
    protected $title;

    public function component()
    {
        return $this->component;
    }

    public function width()
    {
        return $this->width;
    }

    public function title()
    {
        return $this->title;
    }

    public function withTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return array_merge(parent::jsonSerialize(), [
            'component' => $this->component(),
            'width' => $this->width(),
            'title' => $this->title(),
        ]);
    }
}