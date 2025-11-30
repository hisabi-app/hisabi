<?php

namespace App\Domain\Metrics;

use App\Domain\Element;
use Illuminate\Support\Str;

abstract class Metric extends Element
{
    protected $component;
    protected $width = '1/2';
    protected $helpText;
    protected $apiEndpoint;
    protected $showCurrency = true;

    public function component()
    {
        return $this->component;
    }

    public function width()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    public function help($helpText)
    {
        $this->helpText = $helpText;

        return $this;
    }

    public function apiEndpoint()
    {
        return $this->apiEndpoint ?: Str::kebab(class_basename(get_class($this)));
    }

    public function jsonSerialize(): mixed
    {
        return array_merge(parent::jsonSerialize(), [
            'component' => $this->component(),
            'width' => $this->width(),
            'helpText' => $this->helpText,
            'api_endpoint' => $this->apiEndpoint(),
            'show_currency' => $this->showCurrency,
        ]);
    }
}