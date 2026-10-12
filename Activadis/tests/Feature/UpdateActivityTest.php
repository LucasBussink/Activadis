<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

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

    public function test_a_non_admin_cannot_view_the_edit_page(): void
    {
        $activity = Activity::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('activities.edit', $activity))
            ->assertForbidden();
    }

    public function test_an_admin_can_view_the_edit_page_prefilled(): void
    {
        $activity = Activity::factory()->create(['title' => 'Bowlingavond']);

        $this->get(route('activities.edit', $activity))
            ->assertOk()
            ->assertSee('Bowlingavond');
    }

    public function test_a_complete_activity_can_be_updated(): void
    {
        $activity = Activity::factory()->create();

        $this->put(route('activities.update', $activity), [...$this->completeData(), 'publish' => '1'])
            ->assertRedirect(route('activities.show', $activity))
            ->assertSessionHasNoErrors();

        $this->assertSame('Bowlingavond', $activity->refresh()->title);
        $this->assertSame(Activity::STATUS_PUBLISHED, $activity->status);
    }

    public function test_updating_to_published_requires_all_fields(): void
    {
        $activity = Activity::factory()->create(['title' => 'Oude titel']);

        $this->put(route('activities.update', $activity), ['title' => '', 'publish' => '1'])
            ->assertSessionHasErrors(['title', 'starts_at', 'ends_at', 'signup_deadline', 'max_participants']);

        $this->assertSame('Oude titel', $activity->refresh()->title);
    }

    public function test_a_draft_can_be_saved_with_empty_fields(): void
    {
        $activity = Activity::create(['status' => Activity::STATUS_DRAFT, 'title' => 'Concept']);

        $this->put(route('activities.update', $activity), ['title' => '', 'publish' => '0'])
            ->assertSessionHasNoErrors();

        $activity->refresh();
        $this->assertSame(Activity::STATUS_DRAFT, $activity->status);
        $this->assertNull($activity->title);
    }

    public function test_an_existing_photo_can_be_removed(): void
    {
        Storage::fake('public');

        $activity = Activity::factory()->create();
        $image = $activity->images()->create([
            'path' => UploadedFile::fake()->image('foto.jpg')->store('activities', 'public'),
            'position' => 0,
        ]);

        $this->put(route('activities.update', $activity), [
            ...$this->completeData(),
            'publish' => '1',
            'remove_images' => [$image->id],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('activity_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->path);
    }

    public function test_a_new_photo_can_be_added(): void
    {
        Storage::fake('public');

        $activity = Activity::factory()->create();

        $this->put(route('activities.update', $activity), [
            ...$this->completeData(),
            'publish' => '1',
            'images' => [UploadedFile::fake()->image('nieuw.jpg')],
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $activity->images()->count());
    }

    public function test_the_total_of_existing_and_new_photos_cannot_exceed_five(): void
    {
        Storage::fake('public');

        $activity = Activity::factory()->create();
        foreach (range(1, 4) as $position) {
            $activity->images()->create([
                'path' => UploadedFile::fake()->image("foto-{$position}.jpg")->store('activities', 'public'),
                'position' => $position,
            ]);
        }

        $this->put(route('activities.update', $activity), [
            ...$this->completeData(),
            'publish' => '1',
            'images' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ])->assertSessionHasErrors('images');

        $this->assertSame(4, $activity->images()->count());
    }
}
