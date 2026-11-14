<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;
use App\Models\Category;

/**
 * Spending per category in the range, biggest first, with what's needed to draw each one.
 */
class CategoryBreakdownMetric extends Metric
{
    public function calculate(): array
    {
        $query = Transaction::query()
            ->join('brands', 'brands.id', '=', 'transactions.brand_id')
            ->join('categories', 'categories.id', '=', 'brands.category_id')
            ->where('categories.type', Category::EXPENSES)
            ->groupBy('categories.id', 'categories.name', 'categories.color', 'categories.icon')
            ->selectRaw('categories.id, categories.name, categories.color, categories.icon, SUM(transactions.amount) as total, COUNT(*) as count')
            ->orderByDesc('total');

        if ($range = $this->getDayRange()) {
            $query->whereBetween('transactions.created_at', $range);
        }

        return $query->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'color' => $row->color,
                'icon' => $row->icon,
                'total' => (float) $row->total,
                'count' => (int) $row->count,
            ])
            ->all();
    }
}
