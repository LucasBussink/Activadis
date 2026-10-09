<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_sees_the_details_of_a_published_activity(): void
    {
        $activity = Activity::factory()->create([
            'title' => 'Bowlingavond',
            'description' => 'Gezellig bowlen met het team.',
            'personal_contribution' => 12.5,
            'max_participants' => 10,
            'status' => Activity::STATUS_PUBLISHED,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('activities.show', $activity))
            ->assertOk()
            ->assertSee('Bowlingavond')
            ->assertSee('Gezellig bowlen met het team.')
            ->assertSee('€ 12,50')
            ->assertSee('10 plekken over');
    }

    public function test_a_user_sees_how_long_until_the_activity_and_the_deadline(): void
    {
        $this->travelTo(now()->startOfDay()->setTime(12, 0));

        $activity = Activity::factory()->create([
            'starts_at' => now()->addDays(10),
            'ends_at' => now()->addDays(10)->addHours(3),
            'signup_deadline' => now()->addDays(3),
            'status' => Activity::STATUS_PUBLISHED,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('activities.show', $activity))
            ->assertSee('Over 1 week')
            ->assertSee('Nog 3 dagen');
    }

    public function test_a_user_sees_that_they_are_signed_up(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create(['status' => Activity::STATUS_PUBLISHED]);
        $activity->addUser($user);

        $this->actingAs($user)
            ->get(route('activities.show', $activity))
            ->assertSee('Je bent ingeschreven');
    }

    public function test_only_the_admin_sees_who_is_signed_up(): void
    {
        $participant = User::factory()->create(['name' => 'Jan Deelnemer']);
        $activity = Activity::factory()->create(['status' => Activity::STATUS_PUBLISHED]);
        $activity->addUser($participant);

        $this->actingAs(User::factory()->create())
            ->get(route('activities.show', $activity))
            ->assertDontSee('Jan Deelnemer');

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('activities.show', $activity))
            ->assertSee('Jan Deelnemer');
    }

    public function test_an_empty_draft_can_be_shown_to_the_admin(): void
    {
        $draft = Activity::create(['status' => Activity::STATUS_DRAFT]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('activities.show', $draft))
            ->assertOk()
            ->assertSee('Naamloos concept')
            ->assertSee('Datum volgt');
    }
}
