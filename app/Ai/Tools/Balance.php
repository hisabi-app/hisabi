<?php

namespace App\Ai\Tools;

use App\Ai\AnswerCards;
use App\Domains\Assistant\Services\LookupService;
use App\Domains\Brand\Models\Brand;
use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class Balance extends LookupTool
{
    public const LABELS = [
        'net_worth' => 'Net worth',
        'cash' => 'Cash',
        'savings' => 'Savings',
        'investment' => 'Investments',
    ];

    public function description(): Stringable|string
    {
        return 'What there is in total right now, all time rather than for a period: net worth (income less spending), cash (also less savings and investment), '
            . 'savings, investments, or everything one category or brand adds up to (e.g. a savings account). With the change since the month started '
            . 'and where it stood at the end of each of the last 12 months. Stages a card with the balance and its line.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'kind' => $schema->string()->enum(array_keys(LookupService::BALANCES))->description('Which balance; ignored when a category or brand is given.'),
            'category' => $schema->string()->description('A category name.'),
            'brand' => $schema->string()->description('A brand name.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $category = $this->category($request);
        $brand = $this->brand($request);

        foreach ([$category, $brand] as $problem) {
            if (is_string($problem)) {
                return $problem;
            }
        }

        $kind = array_key_exists($request['kind'] ?? '', LookupService::BALANCES) ? $request['kind'] : 'net_worth';
        return $this->json(self::stage($this->lookup, $this->cards, $kind, $category, $brand));
    }

    /**
     * Work out a balance and stage its card (the glance prepares savings and investments this way too).
     *
     * @return array{card: ?string, balance: float, change_this_month: ?string, month_ends: array<int, array{label: string, value: float}>}
     */
    public static function stage(LookupService $lookup, AnswerCards $cards, string $kind, ?Category $category = null, ?Brand $brand = null): array
    {
        $balance = $lookup->balance($kind, $category, $brand);
        $subject = $brand ?? $category;
        $type = $brand?->category?->type ?? $category?->type;

        $label = match (true) {
            $subject === null => self::LABELS[$kind],
            $type === Category::EXPENSES => 'Spent on ' . $subject->name . ', all time',
            $type === Category::INCOME => $subject->name . ', all time',
            default => $subject->name,
        };
        // How it moved this month means something for money kept, not for spending that only ever adds up
        $kept = $subject === null || in_array($type, [Category::SAVINGS, Category::INVESTMENT], true);
        $comparison = $kept ? $lookup->comparison($balance['total'], $balance['month_start'], 'than at the start of ' . now()->format('F')) : null;

        $card = $balance['total'] != 0 || $balance['month_start'] != 0
            ? $cards->add(AnswerCards::TREND, [
                'label' => $label,
                'total' => $balance['total'],
                'comparison' => $comparison,
                'points' => $balance['points'],
                'running' => true,
            ])
            : null;

        return [
            'card' => $card,
            'balance' => $balance['total'],
            'change_this_month' => $lookup->describe($comparison),
            'month_ends' => $balance['points'],
        ];
    }
}
