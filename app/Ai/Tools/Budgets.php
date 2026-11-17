<?php

namespace App\Ai\Tools;

use App\Ai\AnswerCards;
use App\Domains\Budget\Models\Budget;
use App\Http\Resources\BudgetResource;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class Budgets extends LookupTool
{
    private const STATUS = [
        'on_track' => 'on track',
        'ahead' => 'ahead of pace',
        'used' => 'fully used',
        'over' => 'over budget',
    ];

    public function description(): Stringable|string
    {
        return 'The current budgets: amount, spent, left and status for the current window. Give a name for one budget. Stages a card with their rings.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('A budget name, to see only that one.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $budgets = $this->lookup->budgets($request['name'] ?? null);

        if ($budgets->isEmpty()) {
            return $this->json(['card' => null, 'budgets' => [], 'note' => 'No current budget matches.']);
        }

        $card = $this->cards->add(AnswerCards::BUDGETS, [
            'budgets' => BudgetResource::collection($budgets)->resolve(),
        ]);

        return $this->json([
            'card' => $card,
            'budgets' => $budgets->map(fn (Budget $budget) => [
                'name' => $budget->name,
                'amount' => (float) $budget->amount,
                $budget->is_saving ? 'saved' : 'spent' => round((float) $budget->total_transactions_amount, 2),
                'left' => round((float) $budget->remaining_to_spend, 2),
                'status' => self::STATUS[$budget->status()] ?? $budget->status(),
                'window' => $budget->start_at_date . ' to ' . $budget->end_at_date,
            ])->all(),
        ]);
    }
}
