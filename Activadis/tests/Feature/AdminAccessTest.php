<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_seeded_admin_can_log_in_with_a_hashed_password(): void
    {
        $this->seed(UserSeeder::class);

        $admin = User::where('email', 'admin@admin.nl')->sole();
        $this->assertTrue($admin->is_admin);
        $this->assertNotSame('admin', $admin->password);
        $this->assertTrue(Hash::check('admin', $admin->password));

        $this->post('/login', [
            'email' => 'admin@admin.nl',
            'password' => 'admin',
        ])->assertRedirect(route('activities.index', absolute: false));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_the_seeded_user_can_log_in_and_is_no_admin(): void
    {
        $this->seed(UserSeeder::class);

        $user = User::where('email', 'user@user.nl')->sole();
        $this->assertFalse($user->is_admin);
        $this->assertTrue(Hash::check('user', $user->password));

        $this->post('/login', [
            'email' => 'user@user.nl',
            'password' => 'user',
        ])->assertRedirect(route('activities.index', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->get(route('activities.create'))->assertForbidden();
    }

    public function test_regular_users_cannot_create_activities(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('activities.create'))->assertForbidden();
        $this->post(route('activities.store'), ['title' => 'Bowlen'])->assertForbidden();

        $this->assertDatabaseEmpty('activities');
    }

    public function test_the_admin_can_open_the_create_form(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('activities.create'))
            ->assertOk();
    }

    public function test_regular_users_do_not_see_drafts(): void
    {
        $draft = Activity::factory()->create(['title' => 'Geheim concept', 'status' => Activity::STATUS_DRAFT]);
        Activity::factory()->create(['title' => 'Bowlingavond', 'status' => Activity::STATUS_PUBLISHED]);

        $this->actingAs(User::factory()->create());

        $this->get(route('activities.index'))
            ->assertOk()
            ->assertSee('Bowlingavond')
            ->assertDontSee('Geheim concept');

        $this->get(route('activities.show', $draft))->assertNotFound();
    }

    public function test_the_admin_sees_drafts(): void
    {
        $draft = Activity::factory()->create(['title' => 'Geheim concept', 'status' => Activity::STATUS_DRAFT]);

        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('activities.index'))->assertSee('Geheim concept');
        $this->get(route('activities.show', $draft))->assertOk();
    }
}
