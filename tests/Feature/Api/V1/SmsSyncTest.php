<?php

namespace Tests\Feature\Api\V1;

use App\Domains\Sms\Models\Sms;
use App\Domains\Transaction\Models\Transaction;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * POST /api/v1/sms/sync, with made-up banks, merchants, amounts and ids.
 */
class SmsSyncTest extends TestCase
{
    use RefreshDatabase;

    // No date of its own (like NBD's), so it is dated when it arrived
    private const PURCHASE = 'Purchase of AED 47.35 with Debit Card ending 4821 at CORNER BAKERY LL, DUBAI. Avl Balance is AED 3,210.40.';
    private const DATED_PURCHASE = 'Credit Card XX9076 was used for AED310.00 on 12/03/2026 18:42:05 at Sunrise Telecom (S, 800111-AE. Available limit  AED8400.00';
    private const OWN_ACCOUNT_TRANSFER = 'AED 1,850.00 has been drawn from A/C No. 002-100-456XXXX-02 as OWN ACCOUNT TRNSFER. Available balance is  18,670.00';
    private const MULTI_LINE_PURCHASE = "Credit Card Purchase\nCard Ending: 3307\nAt: Example Store, Dubai\nAmount: AED 61.85\nDate: 08/04/2026, 14:05\nAvailable Limit: AED 9,935.80";

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('mac', ['sms:sync'])->plainTextToken;
    }

    public function test_it_requires_a_token(): void
    {
        $this->postJson('/api/v1/sms/sync', ['messages' => [$this->message('guid-1', self::PURCHASE)]])
            ->assertStatus(401);

        $this->assertEquals(0, Sms::count());
    }

    public function test_it_rejects_a_token_without_the_sync_ability(): void
    {
        $otherToken = $this->user->createToken('other', ['something-else'])->plainTextToken;

        $this->withToken($otherToken)
            ->postJson('/api/v1/sms/sync', ['messages' => [$this->message('guid-1', self::PURCHASE)]])
            ->assertStatus(403);
    }

    public function test_it_creates_the_sms_and_its_transaction(): void
    {
        $this->sync([$this->message('guid-1', self::PURCHASE, sender: 'ExampleBank')])
            ->assertOk()
            ->assertExactJson(['data' => [
                ['guid' => 'guid-1', 'status' => 'created', 'transaction_id' => 1],
            ]]);

        $sms = Sms::sole();
        $this->assertEquals(self::PURCHASE, $sms->body);
        $this->assertEquals('guid-1', $sms->external_id);
        $this->assertEquals('ExampleBank', $sms->sender);
        $this->assertEquals(Sms::fingerprintOf(self::PURCHASE), $sms->fingerprint);
        $this->assertEquals('CORNER BAKERY LL', $sms->transaction->brand->name);
        $this->assertEquals(47.35, $sms->transaction->amount);
    }

    public function test_the_same_message_sent_twice_is_a_duplicate(): void
    {
        $this->sync([$this->message('guid-1', self::PURCHASE)])->assertJsonPath('data.0.status', 'created');

        $this->sync([$this->message('guid-1', self::PURCHASE)])
            ->assertOk()
            ->assertExactJson(['data' => [
                ['guid' => 'guid-1', 'status' => 'duplicate', 'transaction_id' => 1],
            ]]);

        $this->assertEquals(1, Sms::count());
        $this->assertEquals(1, Transaction::count());
    }

    public function test_a_known_guid_is_a_duplicate_even_if_its_text_changed(): void
    {
        $this->sync([$this->message('guid-1', self::PURCHASE)]);

        $this->sync([$this->message('guid-1', self::PURCHASE . ' ')])->assertJsonPath('data.0.status', 'duplicate');
        $this->sync([$this->message('guid-1', 'Some other text')])->assertJsonPath('data.0.status', 'duplicate');

        $this->assertEquals(1, Sms::count());
    }

    public function test_the_bank_sending_the_same_sms_twice_counts_once(): void
    {
        $this->sync([
            $this->message('guid-1', self::PURCHASE),
            $this->message('guid-2', self::PURCHASE),
        ])->assertJsonPath('data.0.status', 'created')->assertJsonPath('data.1.status', 'duplicate');

        $this->assertEquals(1, Transaction::count());
    }

    public function test_a_synced_message_pasted_later_is_already_added(): void
    {
        $this->sync([$this->message('guid-1', self::PURCHASE)]);

        $this->actingAs($this->user)
            ->postJson('/api/v1/sms', ['body' => '  ' . str_replace(' with ', "  with\u{00A0}", self::PURCHASE) . "\n"])
            ->assertStatus(201)
            ->assertJsonPath('data.0.already_added', true)
            ->assertJsonPath('data.0.transaction_id', 1);

        $this->assertEquals(1, Sms::count());
        $this->assertEquals(1, Transaction::count());
    }

    public function test_a_pasted_message_synced_later_is_a_duplicate(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/v1/sms', ['body' => self::PURCHASE])
            ->assertStatus(201)
            ->assertJsonMissingPath('data.0.already_added');

        $this->sync([$this->message('guid-1', self::PURCHASE)])
            ->assertExactJson(['data' => [
                ['guid' => 'guid-1', 'status' => 'duplicate', 'transaction_id' => 1],
            ]]);

        $this->assertEquals(1, Sms::count());
        $this->assertEquals(1, Transaction::count());
    }

    public function test_pasting_the_same_message_twice_adds_it_once(): void
    {
        $this->actingAs($this->user)->postJson('/api/v1/sms', ['body' => self::PURCHASE . "\n" . self::PURCHASE])
            ->assertJsonPath('data.0.transaction_id', 1)
            ->assertJsonPath('data.1.already_added', true);

        $this->actingAs($this->user)->postJson('/api/v1/sms', ['body' => self::PURCHASE . "\nsome other message"])
            ->assertJsonPath('data.0.already_added', true)
            ->assertJsonMissingPath('data.1.already_added');

        $this->assertEquals(2, Sms::count());
        $this->assertEquals(1, Transaction::count());
    }

    public function test_a_message_that_could_not_be_read_is_read_again_when_pasted_after_a_new_template(): void
    {
        $message = 'Card 0000 spent AED 18.00 at SAMPLE BOOKS on 15/11/2026.';

        $this->sync([$this->message('guid-1', $message)])
            ->assertExactJson(['data' => [['guid' => 'guid-1', 'status' => 'created', 'transaction_id' => null]]]);

        config(['hisabi.sms_templates' => [...config('hisabi.sms_templates'), 'Card {card} spent AED {amount} at {brand} on {date}.']]);

        $this->actingAs($this->user)->postJson('/api/v1/sms', ['body' => $message])
            ->assertJsonPath('data.0.transaction_id', 1)
            ->assertJsonMissingPath('data.0.already_added');

        $sms = Sms::sole();
        $this->assertEquals('guid-1', $sms->external_id);
        $this->assertEquals('SAMPLE BOOKS', $sms->transaction->brand->name);
        $this->assertEquals('2026-11-15', $sms->transaction->created_at->format('Y-m-d'));

        // Read once: pasting it again doesn't add a second transaction
        $this->actingAs($this->user)->postJson('/api/v1/sms', ['body' => $message])
            ->assertJsonPath('data.0.already_added', true);

        $this->assertEquals(1, Transaction::count());
    }

    public function test_a_multi_line_message_matches_across_sync_and_paste(): void
    {
        $this->sync([$this->message('guid-1', str_replace("\n", "\r\n", self::MULTI_LINE_PURCHASE))])
            ->assertJsonPath('data.0.status', 'created');

        $this->assertEquals(self::MULTI_LINE_PURCHASE, Sms::sole()->body);
        $this->assertEquals('Example Store', Sms::sole()->transaction->brand->name);

        $this->actingAs($this->user)->postJson('/api/v1/sms', ['body' => self::MULTI_LINE_PURCHASE])
            ->assertJsonPath('data.0.already_added', true);

        $this->assertEquals(1, Transaction::count());
    }

    public function test_a_message_pasted_line_by_line_is_recognised_when_synced_whole(): void
    {
        $message = "Dear Customer,\n" . self::PURCHASE;

        $this->actingAs($this->user)->postJson('/api/v1/sms', ['body' => $message]);
        $this->assertEquals(2, Sms::count());

        $this->sync([$this->message('guid-1', $message)])
            ->assertExactJson(['data' => [
                ['guid' => 'guid-1', 'status' => 'duplicate', 'transaction_id' => 1],
            ]]);

        $this->assertEquals(1, Transaction::count());
    }

    public function test_a_message_synced_whole_is_recognised_when_pasted_with_its_line_breaks(): void
    {
        $message = "Dear Customer,\n" . self::PURCHASE;

        $this->sync([$this->message('guid-1', $message)])->assertJsonPath('data.0.status', 'created');

        $this->actingAs($this->user)->postJson('/api/v1/sms', ['body' => $message])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.already_added', true);

        $this->assertEquals(1, Sms::count());
        $this->assertEquals(1, Transaction::count());
    }

    public function test_a_batch_reports_each_message(): void
    {
        $this->sync([$this->message('guid-old', self::DATED_PURCHASE)]);

        $this->sync([
            $this->message('guid-1', self::PURCHASE),
            $this->message('guid-2', self::OWN_ACCOUNT_TRANSFER),
            $this->message('guid-3', 'Your statement is ready. Thank you for banking with us.'),
            $this->message('guid-old', self::DATED_PURCHASE),
            $this->message('guid-1', self::PURCHASE),
        ])->assertOk()->assertExactJson(['data' => [
            ['guid' => 'guid-1', 'status' => 'created', 'transaction_id' => 2],
            ['guid' => 'guid-2', 'status' => 'created', 'transaction_id' => null],
            ['guid' => 'guid-3', 'status' => 'created', 'transaction_id' => null],
            ['guid' => 'guid-old', 'status' => 'duplicate', 'transaction_id' => 1],
            ['guid' => 'guid-1', 'status' => 'duplicate', 'transaction_id' => 2],
        ]]);

        $this->assertEquals(4, Sms::count());
        $this->assertEquals(2, Transaction::count());
    }

    public function test_a_recognised_message_that_is_not_spending_creates_no_transaction(): void
    {
        $this->sync([$this->message('guid-1', self::OWN_ACCOUNT_TRANSFER)])
            ->assertExactJson(['data' => [
                ['guid' => 'guid-1', 'status' => 'created', 'transaction_id' => null],
            ]]);

        $this->assertNotEmpty(Sms::sole()->meta);
        $this->assertEquals(0, Transaction::count());
    }

    public function test_a_message_without_a_date_is_dated_when_it_arrived(): void
    {
        $this->sync([$this->message('guid-1', self::PURCHASE, receivedAt: '2026-11-16T01:30:00+04:00')]);

        $sms = Sms::sole();
        // The phone's local time, not converted to the app's timezone
        $this->assertEquals('2026-11-16 01:30:00', $sms->transaction->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-11-16 01:30:00', $sms->received_at->format('Y-m-d H:i:s'));
    }

    public function test_a_message_with_its_own_date_keeps_it(): void
    {
        $this->sync([$this->message('guid-1', self::DATED_PURCHASE, receivedAt: '2026-03-14T09:00:00+04:00')]);

        $this->assertEquals('2026-03-12', Sms::sole()->transaction->created_at->format('Y-m-d'));
    }

    public function test_two_requests_racing_with_the_same_guid_store_it_once(): void
    {
        // The other request stores the message between this one's check and its insert
        Sms::creating(function (Sms $sms) {
            if ($sms->external_id === 'guid-1' && ! DB::table('sms')->where('external_id', 'guid-1')->exists()) {
                DB::table('sms')->insert(['external_id' => 'guid-1', 'body' => self::PURCHASE, 'created_at' => now(), 'updated_at' => now()]);
            }
        });

        $this->sync([
            $this->message('guid-1', self::PURCHASE),
            $this->message('guid-2', self::DATED_PURCHASE),
        ])->assertOk()
            ->assertJsonPath('data.0', ['guid' => 'guid-1', 'status' => 'duplicate', 'transaction_id' => null])
            ->assertJsonPath('data.1.status', 'created');

        // The losing message's transaction was rolled back with it
        $this->assertEquals(1, Transaction::count());
        $this->assertEquals('Sunrise Telecom (S', Transaction::sole()->brand->name);
    }

    public function test_the_database_refuses_a_second_row_with_the_same_guid(): void
    {
        Sms::create(['body' => 'one', 'external_id' => 'guid-1']);

        $this->expectException(UniqueConstraintViolationException::class);

        Sms::create(['body' => 'two', 'external_id' => 'guid-1']);
    }

    public function test_it_validates_the_batch(): void
    {
        $this->sync([])->assertStatus(422)->assertJsonValidationErrors(['messages']);

        $this->sync([['guid' => 'guid-1', 'text' => self::PURCHASE]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['messages.0.received_at']);

        $tooMany = collect(range(1, 101))->map(fn ($i) => $this->message("guid-{$i}", "message {$i}"))->all();
        $this->sync($tooMany)->assertStatus(422)->assertJsonValidationErrors(['messages']);

        $this->assertEquals(0, Sms::count());
    }

    private function sync(array $messages)
    {
        return $this->withToken($this->token)->postJson('/api/v1/sms/sync', ['messages' => $messages]);
    }

    private function message(string $guid, string $text, ?string $sender = 'ExampleBank', string $receivedAt = '2026-11-15T13:30:00+04:00'): array
    {
        return [
            'guid' => $guid,
            'sender' => $sender,
            'received_at' => $receivedAt,
            'text' => $text,
        ];
    }
}
