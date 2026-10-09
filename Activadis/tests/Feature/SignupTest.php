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
}
