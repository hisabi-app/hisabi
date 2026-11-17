<?php

namespace App\Ai\Tools;

use App\Ai\AnswerCards;
use App\Domains\Assistant\Services\LookupService;
use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class Trend extends LookupTool
{
    private const SUBJECTS = [
        'expenses' => 'Spent',
        'income' => 'Income',
        'savings' => 'Saved',
        'investment' => 'Invested',
    ];

    public function description(): Stringable|string
    {
        return 'How a total moved over time: the total per day, week or month in a period (spending by default, or income, savings or investment), '
            . 'optionally for one category or one brand. Use it for trends ("groceries over the last year"). At most '
            . LookupService::MAX_POINTS . ' points. Stages a card with a line chart.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            ...$this->dateSchema($schema),
            'step' => $schema->string()->enum(LookupService::STEPS)->description('One point per day, week or month (default month).'),
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
        $step = in_array($request['step'] ?? null, LookupService::STEPS, true) ? $request['step'] : 'month';

        if ($this->lookup->countPeriods($from, $to, $step) > LookupService::MAX_POINTS) {
            return $this->json(['error' => "That's more than " . LookupService::MAX_POINTS . " points by {$step}: use a longer step or a shorter period."]);
        }

        $type = $this->type($request);
        $points = $this->lookup->trend($from, $to, $step, $type, $category, $brand);
        $total = round(array_sum(array_column($points, 'value')), 2);

        $subject = $brand ?? $category;
        $label = match (true) {
            $subject === null => self::SUBJECTS[$type],
            ($brand?->category?->type ?? $category?->type) === Category::EXPENSES => ($brand ? 'Spent at ' : 'Spent on ') . $subject->name,
            default => $subject->name,
        };
        $span = $step === 'month' ? $from->format('M Y') . ' – ' . $to->format('M Y') : $from->format('j M') . ' – ' . $to->format('j M');

        // A line of zeros says nothing: Claude says so in words instead
        $card = $total != 0
            ? $this->cards->add(AnswerCards::TREND, [
                'label' => "{$label}, {$span}",
                'total' => $total,
                'comparison' => null,
                'points' => array_map(fn (array $point) => ['label' => $point['label'], 'value' => $point['value']], $points),
                'running' => false,
            ])
            : null;

        return $this->json([
            'card' => $card,
            'total' => $total,
            'step' => $step,
            'points' => array_map(fn (array $point) => ['from' => $point['start'], 'to' => $point['end'], 'total' => $point['value']], $points),
        ]);
    }
}
