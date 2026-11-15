<?php

namespace Tests\Feature\Sms;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class SmsSyncTokenCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_token_that_can_only_sync_sms(): void
    {
        $user = User::factory()->create();

        $this->assertEquals(0, Artisan::call('hisabi:sms-sync-token'));

        $token = $this->printedToken();
        $stored = PersonalAccessToken::findToken($token);

        $this->assertEquals('mac', $stored->name);
        $this->assertTrue($stored->tokenable->is($user));
        $this->assertEquals(['sms:sync'], $stored->abilities);

        $this->withToken($token)
            ->postJson('/api/v1/sms/sync', ['messages' => [[
                'guid' => 'guid-1',
                'sender' => 'ExampleBank',
                'received_at' => '2026-11-15T13:30:00+04:00',
                'text' => 'some message',
            ]]])
            ->assertOk()
            ->assertJsonPath('data.0.status', 'created');
    }

    public function test_a_new_token_replaces_the_old_one_with_the_same_name(): void
    {
        User::factory()->create();

        Artisan::call('hisabi:sms-sync-token', ['name' => 'office-mac']);
        $first = $this->printedToken();

        Artisan::call('hisabi:sms-sync-token', ['name' => 'office-mac']);
        $second = $this->printedToken();

        $this->assertNull(PersonalAccessToken::findToken($first));
        $this->assertNotNull(PersonalAccessToken::findToken($second));
        $this->assertEquals(1, PersonalAccessToken::count());
    }

    public function test_it_revokes_a_devices_token(): void
    {
        User::factory()->create();

        Artisan::call('hisabi:sms-sync-token');
        $token = $this->printedToken();

        Artisan::call('hisabi:sms-sync-token', ['--revoke' => true]);

        $this->assertNull(PersonalAccessToken::findToken($token));
        $this->assertEquals(0, PersonalAccessToken::count());
    }

    public function test_it_needs_a_user(): void
    {
        $this->assertEquals(1, Artisan::call('hisabi:sms-sync-token'));
        $this->assertEquals(0, PersonalAccessToken::count());
    }

    private function printedToken(): string
    {
        preg_match('/^\d+\|\S+$/m', Artisan::output(), $matches);

        $this->assertNotEmpty($matches, 'No token was printed');

        return $matches[0];
    }
}
