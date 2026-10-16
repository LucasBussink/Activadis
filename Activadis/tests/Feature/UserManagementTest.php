<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_the_user_list(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Piet Puk']);

        $this->actingAs($admin)->get(route('users.index'))
            ->assertOk()
            ->assertSee('Piet Puk');
    }

    public function test_normal_user_cannot_open_user_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('users.create'))->assertForbidden();
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_creating_a_user_sends_an_invite(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Nieuwe Collega',
            'email' => 'Nieuw@Voorbeeld.nl',
        ])->assertRedirect(route('users.index'));

        $user = User::where('email', 'nieuw@voorbeeld.nl')->sole();
        $this->assertFalse($user->is_admin);

        Notification::assertSentTo($user, SetPasswordNotification::class);
        $this->assertDatabaseHas('user_invite_tokens', ['email' => 'nieuw@voorbeeld.nl']);
    }

    public function test_email_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'bezet@voorbeeld.nl']);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Dubbel',
            'email' => 'bezet@voorbeeld.nl',
        ])->assertSessionHasErrors('email');
    }

    public function test_invited_user_can_set_a_password_as_guest(): void
    {
        $user = User::factory()->create();
        $token = Password::broker('invites')->createToken($user);

        $this->get(route('password.set', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertDontSee('ongeldig of verlopen');

        $this->post(route('password.set.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Nieuw-wachtwoord-123',
            'password_confirmation' => 'Nieuw-wachtwoord-123',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('Nieuw-wachtwoord-123', $user->fresh()->password));
        $this->assertDatabaseMissing('user_invite_tokens', ['email' => $user->email]);
    }

    public function test_invalid_token_shows_a_message_and_does_not_change_the_password(): void
    {
        $user = User::factory()->create();
        $oldHash = $user->password;

        $this->get(route('password.set', ['token' => 'nep', 'email' => $user->email]))
            ->assertOk()
            ->assertSee('ongeldig of verlopen');

        $this->post(route('password.set.store'), [
            'token' => 'nep',
            'email' => $user->email,
            'password' => 'Nieuw-wachtwoord-123',
            'password_confirmation' => 'Nieuw-wachtwoord-123',
        ])->assertSessionHasErrors('password');

        $this->assertSame($oldHash, $user->fresh()->password);
    }

    public function test_resend_invite_only_works_with_an_open_invite(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $pending = User::factory()->create();
        $done = User::factory()->create();

        Password::broker('invites')->createToken($pending);

        $this->actingAs($admin)->post(route('users.invite', $pending))->assertSessionHas('status');
        Notification::assertSentTo($pending, SetPasswordNotification::class);

        $this->actingAs($admin)->post(route('users.invite', $done))->assertSessionHas('error');
        Notification::assertNotSentTo($done, SetPasswordNotification::class);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('users.destroy', $admin))
            ->assertSessionHas('error');

        $this->assertModelExists($admin);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();

        $this->actingAs($admin)->delete(route('users.destroy', $other))
            ->assertRedirect(route('users.index'));

        $this->assertModelMissing($other);
    }

    public function test_normal_user_cannot_delete_users(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->delete(route('users.destroy', $other))->assertForbidden();
        $this->assertModelExists($other);
    }

    public function test_list_shows_invite_status(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = User::factory()->create();
        $expired = User::factory()->create();

        Password::broker('invites')->createToken($pending);
        Password::broker('invites')->createToken($expired);
        DB::table('user_invite_tokens')
            ->where('email', $expired->email)
            ->update(['created_at' => now()->subDays(40)]);

        $this->actingAs($admin)->get(route('users.index'))
            ->assertSee('Uitnodiging open')
            ->assertSee('Uitnodiging verlopen');
    }

    public function test_invite_mail_mentions_validity_and_button(): void
    {
        $user = User::factory()->create();

        $html = (string) (new SetPasswordNotification('abc'))->toMail($user)->render();

        $this->assertStringContainsString('28 dagen', $html);
        $this->assertStringContainsString('Wachtwoord instellen', $html);
    }
}
