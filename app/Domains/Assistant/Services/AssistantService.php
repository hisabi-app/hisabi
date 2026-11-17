<?php

namespace App\Domains\Assistant\Services;

use App\Ai\Agents\FinanceAssistant;
use App\Ai\AnswerCards;
use App\Domains\Brand\Models\Brand;
use App\Domains\Budget\Models\Budget;
use App\Domains\Home\Services\HomeService;
use App\Domains\Transaction\Services\TransactionService;
use App\Http\Resources\BudgetResource;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Ai\Audio;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Transcription;

/**
 * "Ask Claude" on the kiosk: a spoken question becomes text, Claude answers from the lookups,
 * and the answer's sentence can be read out. Follow-ups are remembered for a few minutes only.
 */
class AssistantService
{
    // How long "Ask again" still continues the conversation
    private const FOLLOW_UP_MINUTES = 3;
    private const FOLLOW_UP_TURNS = 3;
    // How long the answer's sentence waits to be read out
    private const SPEECH_MINUTES = 10;
    private const MAX_SPOKEN_CHARACTERS = 300;

    public function __construct(
        private readonly LookupService $lookup,
        private readonly HomeService $homeService,
        private readonly TransactionService $transactionService
    ) {}

    /**
     * Whether Claude can answer at all (an Anthropic key is set).
     */
    public function enabled(): bool
    {
        return filled(config('ai.providers.anthropic.key'));
    }

    /**
     * Whether questions can be spoken and answers read out (an OpenAI key is set).
     */
    public function hasVoice(): bool
    {
        return filled(config('ai.providers.openai.key'));
    }

    /**
     * The words of a recorded question. The audio itself is never stored.
     */
    public function transcribe(UploadedFile $audio): string
    {
        // Browsers send "audio/webm;codecs=opus"; the provider wants the bare type to name the file
        $mimeType = Str::of($audio->getClientMimeType())->before(';')->trim()->replace('video/', 'audio/')->toString();

        $transcript = Transcription::fromBase64(base64_encode($audio->getContent()), $mimeType)
            // The names it should expect, so merchants and categories come out spelled the way they're stored
            ->withProviderOptions(['prompt' => $this->vocabulary()])
            ->timeout(20)
            ->generate();

        return trim((string) $transcript);
    }

    /**
     * Claude's answer: a short title, the sentence to say, and the cards to show (built from the data).
     *
     * @return array{question: string, title: string, say: string, cards: array<int, array>, conversation_id: string, speech_id: ?string}
     */
    public function ask(string $question, ?string $conversationId = null): array
    {
        $turns = $conversationId ? Cache::get($this->conversationKey($conversationId), []) : [];
        $conversationId = $turns ? $conversationId : (string) Str::uuid();

        $cards = new AnswerCards;
        $glance = $this->prepareGlance($cards);
        $cards->prepared();

        $agent = (new FinanceAssistant($this->lookup, $cards, $glance))->withMessages(
            collect($turns)->flatMap(fn (array $turn) => [
                new UserMessage($turn['question']),
                new AssistantMessage(json_encode(['title' => $turn['title'], 'say' => $turn['say'], 'cards' => []])),
            ])->all()
        );

        $answer = $agent->prompt($question)->toArray();
        $title = trim((string) ($answer['title'] ?? ''));
        $say = trim((string) ($answer['say'] ?? ''));

        // Out of steps without an answer: show what was looked up last, and say nothing
        if ($title === '' || $say === '') {
            $lastLookedUp = $cards->lastLookedUp();

            return [
                'question' => $question,
                'title' => 'Not sure',
                'say' => 'I couldn’t work that out. Try asking another way.',
                'cards' => [$lastLookedUp ?? ['type' => AnswerCards::SENTENCE, 'text' => 'I couldn’t work that out. Try asking another way.']],
                'conversation_id' => $conversationId,
                'speech_id' => null,
            ];
        }

        $shown = $cards->pick((array) ($answer['cards'] ?? []));

        Cache::put(
            $this->conversationKey($conversationId),
            array_slice([...$turns, compact('question', 'title', 'say')], -self::FOLLOW_UP_TURNS),
            now()->addMinutes(self::FOLLOW_UP_MINUTES)
        );

        $speechId = (string) Str::uuid();
        Cache::put($this->speechKey($speechId), Str::limit($say, self::MAX_SPOKEN_CHARACTERS), now()->addMinutes(self::SPEECH_MINUTES));

        return [
            'question' => $question,
            'title' => $title,
            'say' => $say,
            'cards' => $shown ?: [['type' => AnswerCards::SENTENCE, 'text' => $say]],
            'conversation_id' => $conversationId,
            'speech_id' => $speechId,
        ];
    }

    /**
     * The answer's sentence read out, as mp3, or null once it's gone.
     */
    public function speech(string $speechId): ?string
    {
        $say = Cache::get($this->speechKey($speechId));

        if (! is_string($say)) {
            return null;
        }

        return (string) Audio::of($say)
            ->voice(config('hisabi.assistant.voice'))
            ->instructions('Calm, warm and unhurried, like someone at home reading out a number.')
            ->timeout(20)
            ->generate();
    }

    /**
     * Up to four questions worth one tap, from this month's numbers.
     *
     * @return array<int, string>
     */
    public function suggestions(): array
    {
        $now = now();
        // The day-to-day category people keep coming back to, rather than rent
        $topCategory = $this->lookup->breakdown($now->copy()->startOfMonth(), $now, 'category', 'expenses', null, 50)->sortByDesc('count')->first();
        [$lastMonthStart] = $this->homeService->sameTimeLastMonth($now);
        $lastMonthSpent = $this->transactionService->getExpensesTotal($lastMonthStart, $lastMonthStart->copy()->endOfMonth());

        return array_values(array_filter([
            Budget::query()->exists() ? 'How are the budgets?' : null,
            $topCategory ? "{$topCategory['name']} so far" : null,
            $topCategory ? 'Biggest spends this week' : null,
            $lastMonthSpent > 0 ? 'What’s different from ' . $lastMonthStart->format('F') . '?' : null,
        ]));
    }

    /**
     * Stage this month's cards (spending, categories, budgets: what the wall already shows) before Claude
     * starts, and describe them for its instructions, so most questions need no lookup at all.
     *
     * @return array<int, string>
     */
    private function prepareGlance(AnswerCards $cards): array
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $lines = [];

        $month = $this->lookup->total($monthStart, $now, 'expenses');

        if ($month['total'] > 0) {
            $id = $cards->add(AnswerCards::AMOUNT, [
                'label' => 'Spent so far',
                'total' => $month['total'],
                'comparison' => $month['comparison'],
                'daily' => $month['daily'],
                'previous_daily' => $month['previous_daily'],
            ]);
            $comparison = $month['comparison']
                ? " ({$this->number($month['comparison']['difference'])} {$month['comparison']['direction']} {$month['comparison']['label']})"
                : '';
            $lines[] = "{$id}: spent so far this month {$this->number($month['total'])}{$comparison}";
        }

        $categories = $this->lookup->breakdown($monthStart, $now, 'category', 'expenses', null, 50);

        if ($categories->isNotEmpty()) {
            $id = $cards->add(AnswerCards::BARS, [
                'label' => 'By category',
                'rows' => $categories->take(8)->map(fn (array $row) => collect($row)->except('count')->all())->values()->all(),
            ]);
            $lines[] = "{$id}: spending by category this month: "
                . $categories->map(fn (array $row) => "{$row['name']} {$this->number($row['total'])}")->implode(', ');
        }

        $budgets = $this->lookup->budgets();

        if ($budgets->isNotEmpty()) {
            $id = $cards->add(AnswerCards::BUDGETS, ['budgets' => BudgetResource::collection($budgets)->resolve()]);
            $lines[] = "{$id}: budgets: " . $budgets->map(fn (Budget $budget) => sprintf(
                '%s %s of %s, %s left (%s)',
                $budget->name,
                $this->number($budget->total_transactions_amount),
                $this->number($budget->amount),
                $this->number($budget->remaining_to_spend),
                str_replace('_', ' ', $budget->status())
            ))->implode('; ');
        }

        return $lines;
    }

    /**
     * Category names and the most used brands, as a hint for transcription.
     */
    private function vocabulary(): string
    {
        $names = Category::query()->pluck('name')
            ->merge(Brand::query()->withCount('transactions')->orderByDesc('transactions_count')->limit(60)->pluck('name'));

        return Str::limit('Household money: ' . $names->unique()->implode(', '), 800, '');
    }

    private function number(mixed $value): string
    {
        return number_format((float) $value);
    }

    private function conversationKey(string $id): string
    {
        return "kiosk-ask:conversation:{$id}";
    }

    private function speechKey(string $id): string
    {
        return "kiosk-ask:speech:{$id}";
    }
}
