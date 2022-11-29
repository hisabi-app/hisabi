<?php

namespace App\Domain;

class SectionDivider extends Element
{
    protected $component = "section-divider";
    protected $width = 'full';
    protected $title;

    /**
     * @return mixed|string
     */
    public function component()
    {
        return $this->component;
    }

    /**
     * @return mixed|string
     */
    public function width()
    {
        return $this->width;
    }

    /**
     * @return mixed
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * @param $title
     * @return $this
     */
    public function withTitle($title): SectionDivider
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'component' => $this->component(),
            'width' => $this->width(),
            'title' => $this->title(),
        ]);
    }
}
