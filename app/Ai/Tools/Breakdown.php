<?php

namespace App\Ai\Tools;

use App\Ai\AnswerCards;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class Breakdown extends LookupTool
{
    private const MAX_ROWS = 8;

    public function description(): Stringable|string
    {
        return 'Totals for a period per category or per brand, biggest first (spending by default, or income, savings or investment). '
            . 'Give a category to see the brands inside it. Stages a card with the ranked rows.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            ...$this->dateSchema($schema),
            'by' => $schema->string()->enum(['category', 'brand'])->required(),
            'type' => $schema->string()->enum(['expenses', 'income', 'savings', 'investment'])->description('Ignored when a category is given.'),
            'category' => $schema->string()->description('Only this category (useful with by=brand).'),
            'limit' => $schema->integer()->min(1)->max(self::MAX_ROWS)->description('How many rows, at most ' . self::MAX_ROWS . '.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $range = $this->range($request);
        $category = $this->category($request);

        foreach ([$range, $category] as $problem) {
            if (is_string($problem)) {
                return $problem;
            }
        }

        [$from, $to] = $range;
        $by = $request['by'] === 'brand' ? 'brand' : 'category';
        $limit = max(1, min(self::MAX_ROWS, (int) ($request['limit'] ?? 5)));
        $rows = $this->lookup->breakdown($from, $to, $by, $this->type($request), $category, $limit);

        $card = $rows->isNotEmpty()
            ? $this->cards->add(AnswerCards::BARS, [
                'label' => match (true) {
                    $category !== null => $category->name,
                    $by === 'brand' => 'By brand',
                    default => 'By category',
                },
                'rows' => $rows->map(fn (array $row) => collect($row)->except('count')->all())->all(),
            ])
            : null;

        return $this->json([
            'card' => $card,
            'rows' => $rows->map(fn (array $row) => collect($row)->only('name', 'total', 'count')->all())->all(),
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
        ]);
    }
}
