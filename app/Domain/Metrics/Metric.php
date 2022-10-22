<?php

namespace App\Domain\Metrics;

use App\Domain\Element;
use Illuminate\Support\Str;
use \App\Domain\Ranges\LastYear;
use \App\Domain\Ranges\LastMonth;
use \App\Domain\Ranges\CurrentYear;
use \App\Domain\Ranges\CurrentMonth;

abstract class Metric extends Element
{
    protected $component;
    protected $width = '1/2';
    protected $ranges = [];
    protected $graphqlQuery;
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

    public function ranges()
    {
        return [
            new CurrentMonth,
            new LastMonth,
            new CurrentYear,
            new LastYear,
        ];
    }

    public function graphqlQuery()
    {
        return $this->graphqlQuery ?: Str::camel(class_basename(get_class($this)));
    }

    public function jsonSerialize(): mixed
    {
        return array_merge(parent::jsonSerialize(), [
            'component' => $this->component(),
            'width' => $this->width(),
            'ranges' => $this->ranges(),
            'graphql_query' => $this->graphqlQuery(),
            'show_currency' => $this->showCurrency,
        ]);
    }
}