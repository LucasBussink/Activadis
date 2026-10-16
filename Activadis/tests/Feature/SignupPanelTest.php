<?php

namespace Tests\Feature;

use App\Livewire\SignupPanel;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SignupPanelTest extends TestCase
{
    use RefreshDatabase;

    private function openActivity(array $attributes = []): Activity
    {
        return Activity::factory()->create([
            'status' => Activity::STATUS_PUBLISHED,
            'signup_deadline' => now()->addDays(3),
            'max_participants' => 10,
            ...$attributes,
        ]);
    }

    public function test_the_detail_page_contains_the_signup_panel(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity();

        $this->actingAs($user)
            ->get(route('activities.show', $activity))
            ->assertSeeLivewire(SignupPanel::class);
    }

    public function test_a_user_can_sign_up_without_a_page_reload(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity();

        Livewire::actingAs($user)
            ->test(SignupPanel::class, ['activity' => $activity])
            ->call('signUp')
            ->assertSet('notice', 'Je bent ingeschreven!')
            ->assertDispatched('signup-changed', signedUp: true, full: false);

        $this->assertTrue($activity->isUserSignedUp($user));
    }

    public function test_a_user_can_sign_up_with_a_partner_and_dietary_notes(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity(['allow_partner' => true, 'includes_food' => true]);

        Livewire::actingAs($user)
            ->test(SignupPanel::class, ['activity' => $activity])
            ->set('bringsPartner', true)
            ->set('dietaryNotes', 'Notenallergie')
            ->call('signUp');

        $signup = $activity->signups()->first();
        $this->assertTrue($signup->brings_partner);
        $this->assertSame('Notenallergie', $signup->dietary_notes);
    }

    public function test_a_partner_is_refused_when_the_activity_does_not_allow_it(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity(['allow_partner' => false]);

        Livewire::actingAs($user)
            ->test(SignupPanel::class, ['activity' => $activity])
            ->set('bringsPartner', true)
            ->call('signUp')
            ->assertSet('noticeIsError', true);

        $this->assertFalse($activity->isUserSignedUp($user));
    }

    public function test_a_user_cannot_sign_up_for_a_full_activity(): void
    {
        $activity = $this->openActivity(['max_participants' => 1]);
        $activity->addUser(User::factory()->create());

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SignupPanel::class, ['activity' => $activity])
            ->call('signUp')
            ->assertSet('noticeIsError', true);

        $this->assertFalse($activity->isUserSignedUp($user));
    }

    public function test_the_partner_and_dietary_notes_are_saved_automatically_after_signing_up(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity(['allow_partner' => true, 'includes_food' => true]);
        $activity->addUser($user);

        Livewire::actingAs($user)
            ->test(SignupPanel::class, ['activity' => $activity])
            ->set('bringsPartner', true)
            ->set('dietaryNotes', 'Vegetarisch');

        $signup = $activity->signups()->first();
        $this->assertTrue($signup->brings_partner);
        $this->assertSame('Vegetarisch', $signup->dietary_notes);
    }

    public function test_dietary_notes_cannot_be_longer_than_500_characters(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity(['includes_food' => true]);
        $activity->addUser($user, false, 'Vegetarisch');

        Livewire::actingAs($user)
            ->test(SignupPanel::class, ['activity' => $activity])
            ->set('dietaryNotes', str_repeat('a', 501))
            ->assertHasErrors(['dietaryNotes' => 'max']);

        $this->assertSame('Vegetarisch', $activity->signups()->first()->dietary_notes);
    }

    public function test_a_user_can_sign_off(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity(['allow_partner' => true]);
        $activity->addUser($user, true);

        Livewire::actingAs($user)
            ->test(SignupPanel::class, ['activity' => $activity])
            ->call('signOff')
            ->assertSet('notice', 'Je bent uitgeschreven.')
            ->assertSet('bringsPartner', false)
            ->assertDispatched('signup-changed', signedUp: false, full: false);

        $this->assertFalse($activity->isUserSignedUp($user));
    }

    public function test_nothing_can_be_changed_after_the_deadline(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity(['allow_partner' => true]);
        $activity->addUser($user);
        $activity->update(['signup_deadline' => now()->subDay()]);

        $component = Livewire::actingAs($user)
            ->test(SignupPanel::class, ['activity' => $activity])
            ->set('bringsPartner', true)
            ->assertSet('noticeIsError', true)
            ->call('signOff')
            ->assertSet('noticeIsError', true);

        $this->assertTrue($activity->isUserSignedUp($user));
        $this->assertFalse($activity->signups()->first()->brings_partner);
    }

    public function test_a_draft_is_not_available_for_regular_users(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create(['status' => Activity::STATUS_DRAFT]);

        Livewire::actingAs($user)
            ->test(SignupPanel::class, ['activity' => $activity])
            ->assertStatus(404);
    }
}
