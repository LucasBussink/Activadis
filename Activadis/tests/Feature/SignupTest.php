<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignupTest extends TestCase
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

    public function test_a_user_can_sign_up(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity();

        $this->actingAs($user)
            ->from(route('activities.show', $activity))
            ->post(route('activities.signup', $activity))
            ->assertRedirect(route('activities.show', $activity))
            ->assertSessionHas('status', 'Je bent ingeschreven!');

        $this->assertTrue($activity->isUserSignedUp($user));
    }

    public function test_a_user_cannot_sign_up_twice(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity();
        $activity->addUser($user);

        $this->actingAs($user)->post(route('activities.signup', $activity));

        $this->assertSame(1, $activity->getSignedUpUserCount());
    }

    public function test_a_user_cannot_sign_up_for_a_full_activity(): void
    {
        $activity = $this->openActivity(['max_participants' => 1]);
        $activity->addUser(User::factory()->create());

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('activities.signup', $activity))
            ->assertSessionHas('error', 'Deze activiteit is helaas vol.');

        $this->assertFalse($activity->isUserSignedUp($user));
    }

    public function test_a_user_cannot_sign_up_after_the_deadline(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity(['signup_deadline' => now()->subDay()]);

        $this->actingAs($user)
            ->post(route('activities.signup', $activity))
            ->assertSessionHas('error');

        $this->assertFalse($activity->isUserSignedUp($user));
    }

    public function test_a_user_cannot_sign_up_for_a_draft(): void
    {
        $activity = $this->openActivity(['status' => Activity::STATUS_DRAFT]);

        $this->actingAs(User::factory()->create())
            ->post(route('activities.signup', $activity))
            ->assertNotFound();
    }

    public function test_a_user_can_sign_up_when_there_is_no_maximum(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity(['max_participants' => null]);

        $this->actingAs($user)->post(route('activities.signup', $activity));

        $this->assertTrue($activity->isUserSignedUp($user));
    }

    public function test_a_user_can_sign_off(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity();
        $activity->addUser($user);

        $this->actingAs($user)
            ->delete(route('activities.signoff', $activity))
            ->assertSessionHas('status', 'Je bent uitgeschreven.');

        $this->assertFalse($activity->isUserSignedUp($user));
    }

    public function test_a_user_cannot_sign_off_after_the_deadline(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity();
        $activity->addUser($user);
        $activity->update(['signup_deadline' => now()->subDay()]);

        $this->actingAs($user)
            ->delete(route('activities.signoff', $activity))
            ->assertSessionHas('error');

        $this->assertTrue($activity->isUserSignedUp($user));
    }

    public function test_the_overview_shows_which_activities_the_user_is_signed_up_for(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity();

        $this->actingAs($user)
            ->get(route('activities.index'))
            ->assertDontSee('Ingeschreven</span>', false);

        $activity->addUser($user);

        $this->actingAs($user)
            ->get(route('activities.index'))
            ->assertSee('Ingeschreven</span>', false);
    }

    public function test_a_user_can_sign_up_with_a_partner(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity(['allow_partner' => true]);

        $this->actingAs($user)
            ->post(route('activities.signup', $activity), ['brings_partner' => '1'])
            ->assertSessionHas('status');

        $this->assertTrue($activity->signups()->where('user_id', $user->id)->first()->brings_partner);
    }

    public function test_a_partner_is_not_allowed_when_the_activity_does_not_allow_it(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity(['allow_partner' => false]);

        $this->actingAs($user)
            ->post(route('activities.signup', $activity), ['brings_partner' => '1'])
            ->assertSessionHas('error');

        $this->assertFalse($activity->isUserSignedUp($user));
    }

    public function test_a_partner_does_not_count_towards_the_maximum(): void
    {
        $activity = $this->openActivity(['allow_partner' => true, 'max_participants' => 2]);
        $activity->addUser(User::factory()->create(), true);

        $this->assertFalse($activity->isFull());

        $activity->addUser(User::factory()->create(), true);

        $this->assertTrue($activity->isFull());
        $this->assertSame(2, $activity->getSignedUpUserCount());
    }

    public function test_a_user_can_add_and_remove_a_partner_after_signing_up(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity(['allow_partner' => true]);
        $activity->addUser($user);

        $this->actingAs($user)
            ->patch(route('activities.signup.update', $activity), ['brings_partner' => '1'])
            ->assertSessionHas('status');
        $this->assertTrue($activity->signups()->first()->brings_partner);

        $this->actingAs($user)
            ->patch(route('activities.signup.update', $activity))
            ->assertSessionHas('status');
        $this->assertFalse($activity->signups()->first()->brings_partner);
    }

    public function test_a_partner_cannot_be_changed_after_the_deadline(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity(['allow_partner' => true]);
        $activity->addUser($user);
        $activity->update(['signup_deadline' => now()->subDay()]);

        $this->actingAs($user)
            ->patch(route('activities.signup.update', $activity), ['brings_partner' => '1'])
            ->assertSessionHas('error');

        $this->assertFalse($activity->signups()->first()->brings_partner);
    }

    public function test_a_user_cannot_sign_up_after_the_activity_has_ended(): void
    {
        $user = User::factory()->create();
        $activity = $this->openActivity([
            'signup_deadline' => null,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->post(route('activities.signup', $activity))
            ->assertSessionHas('error');

        $this->assertFalse($activity->isUserSignedUp($user));
    }

    public function test_only_the_admin_sees_who_brings_a_partner(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $activity = $this->openActivity(['allow_partner' => true]);
        $activity->addUser($user, true);

        $this->actingAs($admin)
            ->get(route('activities.show', $activity))
            ->assertSee('partner-badge', false);

        $this->actingAs($user)
            ->get(route('activities.show', $activity))
            ->assertDontSee('partner-badge', false);
    }
}
