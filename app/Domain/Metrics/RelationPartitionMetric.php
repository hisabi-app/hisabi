<?php

namespace App\Domain\Metrics;

abstract class RelationPartitionMetric extends PartitionMetric
{
    protected $relationGraphqlQuery;
    protected $relationDisplayUsing;
    protected $relationForeignKey;

    public function jsonSerialize(): mixed
    {
        return array_merge(parent::jsonSerialize(), [
            'relation' => [
                'graphql_query' => $this->relationGraphqlQuery,
                'display_using' => $this->relationDisplayUsing,
                'foreign_key' => $this->relationForeignKey,
            ]
        ]);
    }
}