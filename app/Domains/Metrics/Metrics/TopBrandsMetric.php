<?php

namespace App\Domains\Metrics\Metrics;

use App\Domains\Metrics\Metric;
use App\Domains\Transaction\Models\Transaction;

/**
 * The brands with the most spending in the range.
 */
class TopBrandsMetric extends Metric
{
    private const LIMIT = 5;

    public function calculate(): array
    {
        $query = Transaction::query()
            ->expenses()
            ->selectRaw('brand_id, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('brand_id')
            ->orderByDesc('total')
            ->limit(self::LIMIT)
            ->with('brand.category');

        if ($range = $this->getDayRange()) {
            $query->whereBetween('created_at', $range);
        }

        return $query->get()
            ->map(fn (Transaction $row) => [
                'id' => $row->brand_id,
                'name' => $row->brand?->name,
                'total' => (float) $row->total,
                'count' => (int) $row->count,
                'category' => $row->brand?->category ? [
                    'id' => $row->brand->category->id,
                    'name' => $row->brand->category->name,
                    'color' => $row->brand->category->color,
                    'icon' => $row->brand->category->icon,
                ] : null,
            ])
            ->all();
    }
}
