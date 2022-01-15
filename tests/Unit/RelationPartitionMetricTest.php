<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Metrics\RelationPartitionMetric;

class RelationPartitionMetricTest extends TestCase
{
    /** @test */
    public function it_returns_relation_data()
    {
        $sut = new FakeRelationPartitionMetric;

        $relation = $sut->jsonSerialize()['relation'];

        $this->assertEquals('some-query', $relation['graphql_query']);
        $this->assertEquals('some-name', $relation['display_using']);
        $this->assertEquals('some-key', $relation['foreign_key']);
    }
}

class FakeRelationPartitionMetric extends RelationPartitionMetric
{
    protected $relationGraphqlQuery = 'some-query';
    protected $relationDisplayUsing = 'some-name';
    protected $relationForeignKey = 'some-key';
}
