<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_reach_the_registration_screen(): void
    {
        $this->get('/register')->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_create_accounts(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/register')->assertForbidden();
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_the_admin_can_create_a_regular_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/register')->assertOk();

        $this->actingAs($admin)->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('register'))->assertSessionHasNoErrors();

        $user = User::where('email', 'test@example.com')->sole();
        $this->assertFalse($user->is_admin);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertAuthenticatedAs($admin);
    }
}
