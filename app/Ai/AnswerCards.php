<?php

namespace App\Ai;

/**
 * The cards one kiosk answer can show, staged by the tools as they look things up.
 * Claude only picks cards by id, so every number on the screen comes from the data,
 * never from Claude's wording.
 */
class AnswerCards
{
    public const AMOUNT = 'amount';
    public const BARS = 'bars';
    public const BUDGETS = 'budgets';
    public const TRANSACTIONS = 'transactions';
    public const SENTENCE = 'sentence';

    /** @var array<string, array> */
    private array $cards = [];

    // How many cards were prepared before Claude started (this month at a glance)
    private int $prepared = 0;

    /**
     * Stage a card and return the id Claude picks it by.
     */
    public function add(string $type, array $data): string
    {
        $id = 'c' . (count($this->cards) + 1);
        $this->cards[$id] = ['type' => $type, ...$data];

        return $id;
    }

    /**
     * The cards Claude chose, in its order, leaving out ids it made up or repeated.
     *
     * @param  array<int, mixed>  $ids
     * @return array<int, array>
     */
    public function pick(array $ids): array
    {
        return collect($ids)
            ->filter(fn ($id) => is_string($id) && isset($this->cards[$id]))
            ->unique()
            ->take(2)
            ->map(fn (string $id) => $this->cards[$id])
            ->values()
            ->all();
    }

    /**
     * Mark the cards staged so far as prepared ahead of the question.
     */
    public function prepared(): void
    {
        $this->prepared = count($this->cards);
    }

    /**
     * The last card a tool staged for this question, for when Claude ran out of steps before choosing.
     */
    public function lastLookedUp(): ?array
    {
        $lookedUp = array_slice($this->cards, $this->prepared);

        return $lookedUp ? end($lookedUp) : null;
    }
}
