<?php

namespace Tests\Feature\Api\V1;

use App\Domains\Sms\Models\Sms;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/sms', [
            'body' => 'someBody'
        ]);

        $response->assertStatus(401);
    }

    public function test_it_creates_a_model(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sms', [
                'body' => 'someBody'
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    [
                        'id' => 1,
                        'body' => 'someBody',
                    ]
                ],
            ]);

        $this->assertCount(1, Sms::all());
    }

    public function test_it_processes_created_at_if_provided(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sms', [
                'body' => 'Payment of AED 38.7 to someBrand with Credit Card ending 9048. Avl Cr. Limit is AED 53,750.64.',
                'created_at' => '2022-05-01'
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    [
                        'id' => 1,
                        'body' => 'Payment of AED 38.7 to someBrand with Credit Card ending 9048. Avl Cr. Limit is AED 53,750.64.',
                        'transaction_id' => 1,
                    ]
                ],
            ]);

        $this->assertEquals('2022-05-01', Sms::first()->transaction->created_at->format('Y-m-d'));
    }

    public function test_it_validates_required_body(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sms', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    }

    public function test_it_validates_body_must_be_string(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sms', [
                'body' => 12345
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    }

    public function test_it_validates_created_at_must_be_date(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sms', [
                'body' => 'Test SMS',
                'created_at' => 'invalid-date'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['created_at']);
    }

    public function test_it_accepts_null_created_at(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sms', [
                'body' => 'Test SMS',
                'created_at' => null
            ]);

        $response->assertStatus(201);
        $this->assertCount(1, Sms::all());
    }

    public function test_it_processes_multiple_sms_lines(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sms', [
                'body' => "First SMS\nSecond SMS\nThird SMS"
            ]);

        $response->assertStatus(201);

        $this->assertCount(3, Sms::all());
    }
}

