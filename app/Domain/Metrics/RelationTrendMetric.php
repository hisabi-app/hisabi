<?php

namespace App\Domain\Metrics;

abstract class RelationTrendMetric extends TrendMetric
{
    protected $relationGraphqlQuery;
    protected $relationDisplayUsing;
    protected $relationForeignKey;

    public function jsonSerialize()
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