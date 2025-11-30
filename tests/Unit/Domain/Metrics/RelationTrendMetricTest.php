<?php

namespace Tests\Unit\Domain\Ranges\Metrics\Metrics;

use App\Domain\Metrics\RelationTrendMetric;
use PHPUnit\Framework\TestCase;

class RelationTrendMetricTest extends TestCase
{
    public function test_it_returns_relation_data()
    {
        $sut = new FakeRelationTrendMetric;

        $relation = $sut->jsonSerialize()['relation'];

        $this->assertEquals('some-endpoint', $relation['api_endpoint']);
        $this->assertEquals('some-name', $relation['display_using']);
        $this->assertEquals('some-key', $relation['foreign_key']);
    }
}

class FakeRelationTrendMetric extends RelationTrendMetric
{
    protected $relationApiEndpoint = 'some-endpoint';
    protected $relationDisplayUsing = 'some-name';
    protected $relationForeignKey = 'some-key';
}

