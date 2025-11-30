<?php

namespace App\Domain\Metrics;

use App\Domain\Element;
use Illuminate\Support\Str;
use App\Domain\Ranges\AllTime;
use \App\Domain\Ranges\LastYear;
use \App\Domain\Ranges\LastMonth;
use \App\Domain\Ranges\CurrentYear;
use \App\Domain\Ranges\CurrentMonth;

abstract class Metric extends Element
{
    protected $component;
    protected $width = '1/2';
    protected $helpText;
    protected $ranges = [];
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

    public function ranges()
    {
        return [
            new CurrentMonth,
            new LastMonth,
            new CurrentYear,
            new LastYear,
            new AllTime,
        ];
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
            'ranges' => $this->ranges(),
            'api_endpoint' => $this->apiEndpoint(),
            'show_currency' => $this->showCurrency,
        ]);
    }
}