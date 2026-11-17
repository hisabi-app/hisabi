<?php

namespace App\Ai\Tools;

use App\Ai\AnswerCards;
use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class Spending extends LookupTool
{
    private const LABELS = [
        'expenses' => 'Spent',
        'income' => 'Income',
        'savings' => 'Saved',
        'investment' => 'Invested',
    ];

    public function description(): Stringable|string
    {
        return 'The total for a period (spending by default, or income, savings or investment), optionally for one category or one brand, '
            . 'compared with the period before: this month so far with the same point last month, whole months with the months before, '
            . 'other periods with as many days just before. Stages an amount card.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            ...$this->dateSchema($schema),
            'type' => $schema->string()->enum(['expenses', 'income', 'savings', 'investment'])->description('Ignored when a category or brand is given.'),
            'category' => $schema->string()->description('A category name.'),
            'brand' => $schema->string()->description('A brand (merchant) name.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $range = $this->range($request);
        $category = $this->category($request);
        $brand = $this->brand($request);

        foreach ([$range, $category, $brand] as $problem) {
            if (is_string($problem)) {
                return $problem;
            }
        }

        [$from, $to] = $range;
        $type = $this->type($request);
        $result = $this->lookup->total($from, $to, $type, $category, $brand);
        $comparison = $result['comparison'];

        $subject = $brand ?? $category;
        $label = match (true) {
            $subject === null => self::LABELS[$type],
            ($brand?->category?->type ?? $category?->type) === Category::EXPENSES => ($brand ? 'Spent at ' : 'Spent on ') . $subject->name,
            default => $subject->name,
        };

        // Nothing to draw for nothing at all: Claude says so in words instead
        $card = $result['total'] > 0
            ? $this->cards->add(AnswerCards::AMOUNT, [
                'label' => $label,
                'total' => $result['total'],
                'comparison' => $comparison,
                'daily' => $result['daily'],
                'previous_daily' => $result['previous_daily'],
            ])
            : null;

        return $this->json([
            'card' => $card,
            'total' => $result['total'],
            'previous' => $result['previous'],
            'comparison' => $this->lookup->describe($comparison),
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
        ]);
    }
}
