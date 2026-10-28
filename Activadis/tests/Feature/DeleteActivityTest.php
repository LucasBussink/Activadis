<?php

namespace Tests\Feature;

use App\Mail\ActivityDeleted;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DeleteActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_delete_an_activity_without_removing_it_from_the_database(): void
    {
        $activity = Activity::factory()->create(['status' => Activity::STATUS_PUBLISHED]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->delete(route('activities.destroy', $activity))
            ->assertRedirect(route('activities.show', $activity));

        $this->assertDatabaseHas('activities', ['id' => $activity->id, 'is_deleted' => true]);
    }

    public function test_signed_up_users_are_mailed_when_an_activity_is_deleted(): void
    {
        Mail::fake();

        $activity = Activity::factory()->create(['status' => Activity::STATUS_PUBLISHED]);
        $participant = User::factory()->create();
        $activity->addUser($participant);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->delete(route('activities.destroy', $activity));

        Mail::assertSent(ActivityDeleted::class, fn (ActivityDeleted $mail) => $mail->hasTo($participant->email));
        Mail::assertSentCount(1);
    }

    public function test_external_signups_are_mailed_when_an_activity_is_deleted(): void
    {
        Mail::fake();

        $activity = Activity::factory()->create(['status' => Activity::STATUS_PUBLISHED, 'is_external' => true]);
        $activity->addUser(User::factory()->create());
        $activity->addExternal('Els', 'els@voorbeeld.nl');

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->delete(route('activities.destroy', $activity));

        Mail::assertSent(ActivityDeleted::class, fn (ActivityDeleted $mail) => $mail->hasTo('els@voorbeeld.nl') && $mail->isExternal);
        Mail::assertSentCount(2);
    }

    public function test_a_regular_user_cannot_delete_an_activity(): void
    {
        $activity = Activity::factory()->create(['status' => Activity::STATUS_PUBLISHED]);

        $this->actingAs(User::factory()->create())
            ->delete(route('activities.destroy', $activity))
            ->assertForbidden();

        $this->assertFalse($activity->fresh()->is_deleted);
    }

    public function test_a_deleted_activity_is_hidden_for_users_but_visible_for_admins(): void
    {
        $activity = Activity::factory()->create([
            'title' => 'Bowlingavond',
            'status' => Activity::STATUS_PUBLISHED,
            'is_deleted' => true,
        ]);

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('activities.index'))->assertDontSee('Bowlingavond');
        $this->actingAs($user)->get(route('activities.show', $activity))->assertNotFound();

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get(route('activities.index'))->assertSee('Bowlingavond')->assertSee('Verwijderd');
        $this->actingAs($admin)->get(route('activities.show', $activity))->assertOk()->assertSee('Verwijderd');
    }

    public function test_users_cannot_sign_up_for_a_deleted_activity(): void
    {
        $activity = Activity::factory()->create(['status' => Activity::STATUS_PUBLISHED, 'is_deleted' => true]);

        $this->assertNull($activity->addUser(User::factory()->create()));
    }
}
