<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ExternalSignup;
use App\Models\User;
use App\Notifications\ExternalSignupConfirmation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ExternalSignupTest extends TestCase
{
    use RefreshDatabase;

    private function externalActivity(array $attributes = []): Activity
    {
        return Activity::factory()->create(array_merge([
            'title' => 'Zomerbarbecue',
            'status' => Activity::STATUS_PUBLISHED,
            'is_external' => true,
            'starts_at' => now()->addDays(10),
            'ends_at' => now()->addDays(10)->addHours(3),
            'signup_deadline' => now()->addDays(5),
            'max_participants' => 10,
            'allow_partner' => true,
            'includes_food' => true,
        ], $attributes));
    }

    private function signupData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Els Extern',
            'email' => 'Els@Voorbeeld.nl',
        ], $overrides);
    }

    public function test_a_guest_sees_only_published_external_activities(): void
    {
        $this->externalActivity(['title' => 'Zomerbarbecue']);
        $this->externalActivity(['title' => 'Alleen concept', 'status' => Activity::STATUS_DRAFT]);
        $this->externalActivity(['title' => 'Intern uitje', 'is_external' => false]);

        $this->get(route('external.index'))
            ->assertOk()
            ->assertSee('Zomerbarbecue')
            ->assertDontSee('Alleen concept')
            ->assertDontSee('Intern uitje');
    }

    public function test_a_guest_cannot_open_internal_or_draft_activities(): void
    {
        $internal = $this->externalActivity(['is_external' => false]);
        $draft = $this->externalActivity(['status' => Activity::STATUS_DRAFT]);

        $this->get(route('external.show', $internal))->assertNotFound();
        $this->get(route('external.show', $draft))->assertNotFound();
    }

    public function test_the_internal_overview_still_needs_a_login(): void
    {
        $this->get(route('activities.index'))->assertRedirect(route('login'));
    }

    public function test_a_guest_can_sign_up_and_gets_a_confirmation_mail(): void
    {
        Notification::fake();
        $activity = $this->externalActivity();

        $this->post(route('external.store', $activity), $this->signupData([
            'brings_partner' => '1',
            'dietary_notes' => 'Vegetarisch',
        ]))->assertRedirect(route('external.show', $activity));

        $signup = ExternalSignup::sole();
        $this->assertSame('els@voorbeeld.nl', $signup->email);
        $this->assertTrue($signup->brings_partner);
        $this->assertSame('Vegetarisch', $signup->dietary_notes);

        Notification::assertSentTo($signup, ExternalSignupConfirmation::class);
    }

    public function test_the_confirmation_mail_contains_the_cancel_link(): void
    {
        $activity = $this->externalActivity();
        $signup = $activity->addExternal('Els', 'els@voorbeeld.nl');

        $html = (string) (new ExternalSignupConfirmation($signup))->toMail($signup)->render();

        $this->assertStringContainsString('Zomerbarbecue', $html);
        $this->assertStringContainsString(route('external.cancel', $signup->token), $html);
    }

    public function test_external_signups_count_towards_the_maximum(): void
    {
        Notification::fake();
        $activity = $this->externalActivity(['max_participants' => 2]);
        $activity->addUser(User::factory()->create());

        $this->post(route('external.store', $activity), $this->signupData());
        $this->assertSame(2, $activity->fresh()->getSignedUpUserCount());
        $this->assertTrue($activity->fresh()->isFull());

        $this->post(route('external.store', $activity), $this->signupData(['email' => 'ander@voorbeeld.nl']))
            ->assertSessionHas('error');
        $this->assertSame(1, ExternalSignup::count());
    }

    public function test_the_same_email_cannot_sign_up_twice(): void
    {
        Notification::fake();
        $activity = $this->externalActivity();

        $this->post(route('external.store', $activity), $this->signupData());
        $this->post(route('external.store', $activity), $this->signupData(['email' => 'ELS@voorbeeld.nl']))
            ->assertSessionHasErrors('email');

        $this->assertSame(1, ExternalSignup::count());
    }

    public function test_name_and_a_valid_email_are_required(): void
    {
        $activity = $this->externalActivity();

        $this->post(route('external.store', $activity), ['name' => '', 'email' => 'geen-email'])
            ->assertSessionHasErrors(['name', 'email']);

        $this->assertSame(0, ExternalSignup::count());
    }

    public function test_the_hidden_bot_field_blocks_the_signup(): void
    {
        $activity = $this->externalActivity();

        $this->post(route('external.store', $activity), $this->signupData(['website' => 'spam']))
            ->assertSessionHasErrors('website');

        $this->assertSame(0, ExternalSignup::count());
    }

    public function test_signing_up_is_not_possible_after_the_deadline(): void
    {
        $activity = $this->externalActivity(['signup_deadline' => now()->subDay()]);

        $this->post(route('external.store', $activity), $this->signupData())
            ->assertSessionHas('error');

        $this->assertSame(0, ExternalSignup::count());
    }

    public function test_partner_is_ignored_when_the_activity_does_not_allow_it(): void
    {
        Notification::fake();
        $activity = $this->externalActivity(['allow_partner' => false]);

        $this->post(route('external.store', $activity), $this->signupData(['brings_partner' => '1']));

        $this->assertFalse(ExternalSignup::sole()->brings_partner);
    }

    public function test_a_signup_can_be_cancelled_with_the_link(): void
    {
        $activity = $this->externalActivity();
        $signup = $activity->addExternal('Els', 'els@voorbeeld.nl');

        // De link alleen openen meldt nog niemand af
        $this->get(route('external.cancel', $signup->token))->assertOk()->assertSee('Ja, meld me af');
        $this->assertModelExists($signup);

        $this->delete(route('external.destroy', $signup->token))->assertOk()->assertSee('Je bent afgemeld');
        $this->assertModelMissing($signup);
    }

    public function test_an_unknown_cancel_link_shows_a_message(): void
    {
        $this->get(route('external.cancel', 'bestaat-niet'))
            ->assertOk()
            ->assertSee('Aanmelding niet gevonden');
    }

    public function test_cancelling_is_not_possible_after_the_deadline(): void
    {
        $activity = $this->externalActivity();
        $signup = $activity->addExternal('Els', 'els@voorbeeld.nl');
        $activity->update(['signup_deadline' => now()->subDay()]);

        $this->delete(route('external.destroy', $signup->token))->assertSee('Afmelden kan niet meer');
        $this->assertModelExists($signup);
    }

    public function test_the_admin_sees_external_signups_in_the_participant_list(): void
    {
        $activity = $this->externalActivity();
        $activity->addExternal('Els Extern', 'els@voorbeeld.nl', true, 'Notenallergie');

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('activities.show', $activity))
            ->assertOk()
            ->assertSee('Els Extern')
            ->assertSee('els@voorbeeld.nl')
            ->assertSee('Extern')
            ->assertSee('Notenallergie');
    }

    public function test_the_admin_sees_the_link_for_externals(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('activities.index'))
            ->assertOk()
            ->assertSee(route('external.index'));
    }

    public function test_the_login_page_links_to_the_external_overview(): void
    {
        $this->get(route('login'))->assertOk()->assertSee(route('external.index'));
    }
}
