<?php

namespace Tests\Feature\Kiosk;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class KioskPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/kiosk')->assertRedirect('/login');
    }

    public function test_it_renders_the_kiosk_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/kiosk')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Kiosk'));
    }
}
