<?php

namespace App\GraphQL\Queries;

use Illuminate\Support\Facades\DB;
use App\Domain\Metrics\CirclePackMetric;

class FinanceVisualizationCirclePackMetric extends CirclePackMetric
{
    protected $name = 'Finance Visualization';

    protected $colors = [
        'red' => '#ef4444',
        'blue' => '#3b82f6',
        'green' => '#22c55e',
        'orange' => '#f97316',
        'purple' => '#A754F7',
        'pink' => '#ec4899',
        'indigo' => '#6366f1',
        'gray' => '#94A4B8'
    ];

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO: refactor this complex logic :p
        $rangeData = app('findRangeByKey', ["key" => $args['range']]);

        $transactions = DB::table('transactions')
            ->join('brands', 'transactions.brand_id', '=', 'brands.id')
            ->select('brands.name as brand_name', 'brands.category_id', DB::raw('sum(amount) as value'))
            ->groupBy(['brand_name', 'brands.category_id']);

        if($rangeData) {
            $transactions->whereBetween('transactions.created_at', [$rangeData->start(), $rangeData->end()]);
        }

        $categories = DB::table('categories')
            ->joinSub($transactions, 'children', function ($join) {
                $join->on('categories.id', '=', 'children.category_id');
            });

        $rootLevel = ["children" => []];
        foreach($categories->get()->groupBy(['type', 'name']) as $key => $value) {
            $innerChildren = [];
            foreach ($value as $innerKey => $innerValue) {
                $innerChildren[] = [
                    "label" => $innerKey,
                    "children" => $innerValue->map(function ($item){
                        return [
                            "label" => $item->brand_name,
                            "value" => $item->value,
                            "color" => $this->colors[$item->color] ?? 'white'
                        ];
                    })
                ];
            }
            $rootLevel["children"][] = [
                "label" => $key,
                "children" => $innerChildren
            ];
        }

        return $rootLevel;
    }
}
