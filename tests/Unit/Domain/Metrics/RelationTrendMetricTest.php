<?php

namespace Tests\Unit\Domain\Ranges\Metrics\Metrics;

use App\Domain\Metrics\RelationTrendMetric;
use PHPUnit\Framework\TestCase;

class RelationTrendMetricTest extends TestCase
{
    /** @test */
    public function it_returns_relation_data()
    {
        $sut = new FakeRelationTrendMetric;

        $relation = $sut->jsonSerialize()['relation'];

        $this->assertEquals('some-query', $relation['graphql_query']);
        $this->assertEquals('some-name', $relation['display_using']);
        $this->assertEquals('some-key', $relation['foreign_key']);
    }
}

class FakeRelationTrendMetric extends RelationTrendMetric
{
    protected $relationGraphqlQuery = 'some-query';
    protected $relationDisplayUsing = 'some-name';
    protected $relationForeignKey = 'some-key';
}

