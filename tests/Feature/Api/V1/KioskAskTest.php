<?php

namespace Tests\Feature\Api\V1;

use App\Ai\Agents\FinanceAssistant;
use App\Domains\Brand\Models\Brand;
use App\Domains\Category\Models\Category;
use App\Domains\Transaction\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Laravel\Ai\Audio;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Prompts\AudioPrompt;
use Laravel\Ai\Prompts\TranscriptionPrompt;
use Laravel\Ai\Responses\Data\ToolCall;
use Laravel\Ai\Transcription;
use Tests\TestCase;

class KioskAskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-17 18:00:00');
        config(['ai.providers.anthropic.key' => 'test', 'ai.providers.openai.key' => 'test']);

        $this->user = User::factory()->create();

        $groceries = Category::factory()->create(['name' => 'Groceries', 'color' => 'green']);
        $carrefour = Brand::factory()->create(['name' => 'Carrefour', 'category_id' => $groceries->id]);
        Transaction::factory()->create(['brand_id' => $carrefour->id, 'amount' => 650, 'created_at' => '2026-11-10 10:00:00']);
        Transaction::factory()->create(['brand_id' => $carrefour->id, 'amount' => 700, 'created_at' => '2026-10-05 10:00:00']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_requires_authentication(): void
    {
        $this->postJson('/api/v1/kiosk/ask', ['question' => 'Hi'])->assertStatus(401);
        $this->postJson('/api/v1/kiosk/transcribe')->assertStatus(401);
        $this->getJson('/api/v1/kiosk/speech/' . fake()->uuid())->assertStatus(401);
    }

    public function test_it_shows_the_cards_claude_picked_from_what_the_tools_looked_up(): void
    {
        FinanceAssistant::fake([
            // This month's spending (c1) and categories (c2) are prepared ahead; the lookup stages c3
            new ToolCall('call-1', 'Spending', ['from' => '2026-11-01', 'to' => '2026-11-17', 'category' => 'Groceries']),
            ['title' => 'Groceries this month', 'say' => '650 on groceries so far, 50 less than this time in October.', 'cards' => ['c3', 'c99']],
        ])->preventStrayPrompts();

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/kiosk/ask', ['question' => 'How much on groceries this month?'])
            ->assertOk()
            ->assertJsonPath('data.question', 'How much on groceries this month?')
            ->assertJsonPath('data.title', 'Groceries this month')
            ->assertJsonCount(1, 'data.cards')
            ->assertJsonPath('data.cards.0.type', 'amount')
            ->assertJsonPath('data.cards.0.label', 'Spent on Groceries')
            ->assertJsonPath('data.cards.0.total', 650)
            ->assertJsonPath('data.cards.0.comparison.direction', 'less');

        $this->assertTrue(str()->isUuid($response->json('data.conversation_id')));
        $this->assertSame(
            '650 on groceries so far, 50 less than this time in October.',
            Cache::get('kiosk-ask:speech:' . $response->json('data.speech_id'))
        );

        FinanceAssistant::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('How much on groceries')
            && str_contains($prompt->agent->instructions(), 'c1: spent so far this month 650 (50 less than this time in October)')
            && str_contains($prompt->agent->instructions(), 'c2: spending by category this month: Groceries 650'));
    }

    public function test_a_trend_comes_back_as_a_line_and_the_savings_balance_is_ready(): void
    {
        $savings = Brand::factory()->create(['name' => 'General Saving', 'category_id' => Category::factory()->create(['name' => 'Savings', 'type' => Category::SAVINGS])->id]);
        Transaction::factory()->create(['brand_id' => $savings->id, 'amount' => 1500, 'created_at' => '2026-10-20 10:00:00']);

        FinanceAssistant::fake([
            // Spending (c1), categories (c2) and the savings balance (c3) are prepared ahead; the trend is c4
            new ToolCall('call-1', 'Trend', ['from' => '2026-09-01', 'to' => '2026-11-17', 'category' => 'Groceries']),
            ['title' => 'Groceries over time', 'say' => 'Groceries were 700 in October and 650 so far this month.', 'cards' => ['c4']],
        ])->preventStrayPrompts();

        $this->actingAs($this->user)
            ->postJson('/api/v1/kiosk/ask', ['question' => 'How have groceries gone lately?'])
            ->assertOk()
            ->assertJsonPath('data.cards.0.type', 'trend')
            ->assertJsonPath('data.cards.0.label', 'Spent on Groceries, Sep 2026 – Nov 2026')
            ->assertJsonPath('data.cards.0.points.1', ['label' => 'Oct', 'value' => 700])
            ->assertJsonPath('data.cards.0.running', false);

        FinanceAssistant::assertPrompted(fn (AgentPrompt $prompt) => str_contains(
            $prompt->agent->instructions(),
            'c3: savings balance now 1,500 (the same as at the start of November)'
        ));
    }

    public function test_an_answer_without_cards_shows_its_sentence(): void
    {
        FinanceAssistant::fake([['title' => 'Hello', 'say' => 'I can help with your spending, income, savings and budgets.', 'cards' => []]]);

        $this->actingAs($this->user)
            ->postJson('/api/v1/kiosk/ask', ['question' => 'What is the weather?'])
            ->assertOk()
            ->assertJsonPath('data.cards.0.type', 'sentence')
            ->assertJsonPath('data.cards.0.text', 'I can help with your spending, income, savings and budgets.');
    }

    public function test_an_empty_answer_is_not_spoken(): void
    {
        FinanceAssistant::fake([['title' => '', 'say' => '', 'cards' => []]]);

        $this->actingAs($this->user)
            ->postJson('/api/v1/kiosk/ask', ['question' => 'Something hard'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Not sure')
            ->assertJsonPath('data.speech_id', null)
            ->assertJsonPath('data.cards.0.type', 'sentence');
    }

    public function test_ask_again_continues_the_conversation_for_a_few_minutes(): void
    {
        FinanceAssistant::fake([
            ['title' => 'Groceries this month', 'say' => '650 on groceries.', 'cards' => []],
            ['title' => 'Restaurants this month', 'say' => 'Nothing on restaurants yet.', 'cards' => []],
            ['title' => 'Restaurants this month', 'say' => 'Nothing on restaurants yet.', 'cards' => []],
        ]);

        $first = $this->actingAs($this->user)->postJson('/api/v1/kiosk/ask', ['question' => 'Groceries?'])->json('data.conversation_id');

        $this->actingAs($this->user)
            ->postJson('/api/v1/kiosk/ask', ['question' => 'And restaurants?', 'conversation_id' => $first])
            ->assertJsonPath('data.conversation_id', $first);

        FinanceAssistant::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('And restaurants')
            && collect($prompt->messages)->pluck('content')->contains(fn ($content) => str_contains((string) $content, '650 on groceries.')));

        // Later, the same id starts afresh
        $this->travel(4)->minutes();

        $this->actingAs($this->user)
            ->postJson('/api/v1/kiosk/ask', ['question' => 'And restaurants?', 'conversation_id' => $first])
            ->assertJsonPath('data.conversation_id', fn ($id) => $id !== $first);
    }

    public function test_it_answers_one_question_at_a_time(): void
    {
        FinanceAssistant::fake();
        $lock = Cache::lock('kiosk-ask', 70);
        $lock->get();

        $this->actingAs($this->user)
            ->postJson('/api/v1/kiosk/ask', ['question' => 'Groceries?'])
            ->assertStatus(429);

        $lock->release();
    }

    public function test_it_validates_the_question(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/v1/kiosk/ask', ['question' => str_repeat('a', 301), 'conversation_id' => 'nope'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['question', 'conversation_id']);
    }

    public function test_it_refuses_when_claude_is_not_set_up(): void
    {
        config(['ai.providers.anthropic.key' => null]);
        FinanceAssistant::fake();

        $this->actingAs($this->user)
            ->postJson('/api/v1/kiosk/ask', ['question' => 'Groceries?'])
            ->assertStatus(503);

        FinanceAssistant::assertNeverPrompted();
    }

    public function test_it_transcribes_a_recorded_question_as_its_bare_audio_type(): void
    {
        Transcription::fake(['  How much on groceries?  ']);

        $this->actingAs($this->user)
            ->post('/api/v1/kiosk/transcribe', ['audio' => $this->recording('audio/webm;codecs=opus')], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.text', 'How much on groceries?');

        Transcription::assertGenerated(fn (TranscriptionPrompt $prompt) => $prompt->audio->mimeType() === 'audio/webm'
            && str_contains($prompt->providerOptions['prompt'], 'Carrefour'));
    }

    public function test_silence_is_not_a_question(): void
    {
        Transcription::fake(['']);

        $this->actingAs($this->user)
            ->post('/api/v1/kiosk/transcribe', ['audio' => $this->recording('audio/mp4')], ['Accept' => 'application/json'])
            ->assertStatus(422);
    }

    public function test_it_reads_the_answer_out_once_asked(): void
    {
        Audio::fake();
        Cache::put('kiosk-ask:speech:4b1a7c4e-3a52-4bd6-9bbf-0f5d4c3a2b10', '650 on groceries so far.', now()->addMinutes(10));

        $this->actingAs($this->user)
            ->get('/api/v1/kiosk/speech/4b1a7c4e-3a52-4bd6-9bbf-0f5d4c3a2b10')
            ->assertOk()
            ->assertHeader('Content-Type', 'audio/mpeg');

        Audio::assertGenerated(fn (AudioPrompt $prompt) => $prompt->text === '650 on groceries so far.');

        $this->actingAs($this->user)
            ->getJson('/api/v1/kiosk/speech/' . fake()->uuid())
            ->assertNotFound();
    }

    /**
     * A recorded question as the browser labels it. The content is a bare WAV header,
     * enough for the upload to read as audio.
     */
    private function recording(string $clientMimeType): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'question');
        file_put_contents($path, 'RIFF' . pack('V', 36) . 'WAVEfmt ' . pack('VvvVVvv', 16, 1, 1, 16000, 32000, 2, 16) . 'data' . pack('V', 0));

        return new UploadedFile($path, 'question', $clientMimeType, null, true);
    }
}
