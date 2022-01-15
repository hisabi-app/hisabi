<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Domain\Metrics\RelationTrendMetric;

class RelationTrendMetricTest extends TestCase
{
    /** @test */
    public function it_returns_relation_data()
    {
        $sut = new FakeRelationTrenMetric;

        $relation = $sut->jsonSerialize()['relation'];

        $this->assertEquals('some-query', $relation['graphql_query']);
        $this->assertEquals('some-name', $relation['display_using']);
        $this->assertEquals('some-key', $relation['foreign_key']);
    }
}

class FakeRelationTrenMetric extends RelationTrendMetric
{
    protected $relationGraphqlQuery = 'some-query';
    protected $relationDisplayUsing = 'some-name';
    protected $relationForeignKey = 'some-key';
}

