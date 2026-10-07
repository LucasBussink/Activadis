<?php

namespace Tests\Feature;

use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreActivityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function completeData(): array
    {
        return [
            'title' => 'Bowlingavond',
            'description' => 'Een avondje bowlen.',
            'starts_at' => '2026-11-01 19:00',
            'ends_at' => '2026-11-01 22:00',
            'signup_deadline' => '2026-10-25 12:00',
            'personal_contribution' => '5',
            'min_participants' => '2',
            'max_participants' => '20',
        ];
    }

    public function test_a_draft_may_be_completely_empty(): void
    {
        $this->post(route('activities.store'), ['publish' => '0'])
            ->assertRedirect(route('activities.index'))
            ->assertSessionHasNoErrors();

        $activity = Activity::sole();
        $this->assertSame(Activity::STATUS_DRAFT, $activity->status);
        $this->assertNull($activity->title);
        $this->assertNull($activity->starts_at);
        $this->assertNull($activity->personal_contribution);
    }

    public function test_publishing_requires_all_fields(): void
    {
        $this->post(route('activities.store'), ['publish' => '1'])
            ->assertSessionHasErrors([
                'title' => 'Vul een geldige waarde in.',
                'starts_at',
                'ends_at',
                'signup_deadline',
                'max_participants',
            ]);

        $this->assertDatabaseEmpty('activities');
    }

    public function test_description_contribution_and_minimum_are_optional_when_publishing(): void
    {
        $data = $this->completeData();
        unset($data['description'], $data['personal_contribution'], $data['min_participants']);

        $this->post(route('activities.store'), [...$data, 'publish' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame(Activity::STATUS_PUBLISHED, Activity::sole()->status);
    }

    public function test_publishing_with_food_requires_a_food_description(): void
    {
        $this->post(route('activities.store'), [...$this->completeData(), 'includes_food' => '1', 'publish' => '1'])
            ->assertSessionHasErrors('food_description');
    }

    public function test_a_complete_activity_can_be_published(): void
    {
        $this->post(route('activities.store'), [...$this->completeData(), 'publish' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame(Activity::STATUS_PUBLISHED, Activity::sole()->status);
    }

    public function test_a_dash_as_personal_contribution_is_stored_as_zero(): void
    {
        $this->post(route('activities.store'), [...$this->completeData(), 'personal_contribution' => '-', 'publish' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertEquals(0, Activity::sole()->personal_contribution);
    }

    public function test_a_comma_may_be_used_as_decimal_separator(): void
    {
        $this->post(route('activities.store'), [...$this->completeData(), 'personal_contribution' => '12,50', 'publish' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertEquals(12.5, Activity::sole()->personal_contribution);
    }

    public function test_an_invalid_start_time_only_reports_one_error(): void
    {
        $this->post(route('activities.store'), [...$this->completeData(), 'starts_at' => 'geen-datum', 'publish' => '1'])
            ->assertSessionHasErrors('starts_at')
            ->assertSessionDoesntHaveErrors(['ends_at', 'signup_deadline']);
    }

    public function test_the_index_shows_a_dash_for_empty_draft_fields(): void
    {
        Activity::create(['status' => Activity::STATUS_DRAFT]);

        $this->get(route('activities.index'))
            ->assertOk()
            ->assertSee('Naamloos concept')
            ->assertSee('—')
            ->assertDontSee('Gratis');
    }
}
