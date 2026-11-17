<?php

namespace App\Ai\Tools;

use App\Ai\AnswerCards;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class Transactions extends LookupTool
{
    private const MAX_ROWS = 8;

    public function description(): Stringable|string
    {
        return 'The latest or largest transactions in a period (spending by default), optionally for one category or brand, '
            . 'with how many there were and their total. Stages a card with the rows.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            ...$this->dateSchema($schema),
            'sort' => $schema->string()->enum(['latest', 'largest'])->required(),
            'type' => $schema->string()->enum(['expenses', 'income', 'savings', 'investment'])->description('Ignored when a category or brand is given.'),
            'category' => $schema->string()->description('A category name.'),
            'brand' => $schema->string()->description('A brand (merchant) name.'),
            'limit' => $schema->integer()->min(1)->max(self::MAX_ROWS)->description('How many rows, at most ' . self::MAX_ROWS . '.'),
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
        $sort = $request['sort'] === 'largest' ? 'largest' : 'latest';
        $limit = max(1, min(self::MAX_ROWS, (int) ($request['limit'] ?? 5)));
        $result = $this->lookup->transactionList($from, $to, $this->type($request), $category, $brand, $sort, $limit);

        $card = $result['rows']->isNotEmpty()
            ? $this->cards->add(AnswerCards::TRANSACTIONS, [
                'label' => $sort === 'largest' ? 'Biggest' : 'Latest',
                'rows' => $result['rows']->all(),
            ])
            : null;

        return $this->json([
            'card' => $card,
            'count' => $result['count'],
            'total' => $result['total'],
            'shown' => $result['rows']->map(fn (array $row) => collect($row)->only('date', 'brand', 'amount')->all())->all(),
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
        ]);
    }
}
