<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Create (or update) the demo beheerder and the demo gebruiker.
     */
    public function run(): void
    {
        $this->createUser('Beheerder', 'admin@admin.nl', 'admin', isAdmin: true);
        $this->createUser('Gebruiker', 'user@user.nl', 'user', isAdmin: false);
    }

    private function createUser(string $name, string $email, string $password, bool $isAdmin): void
    {
        $user = User::firstOrNew(['email' => $email]);

        $user->name = $name;
        $user->password = Hash::make($password);
        $user->email_verified_at ??= now();
        $user->is_admin = $isAdmin;
        $user->save();
    }
}
