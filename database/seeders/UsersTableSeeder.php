<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SEED_ADMIN_EMAIL');
        $password = env('SEED_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->fill([
            'name' => env('SEED_ADMIN_NAME', 'admin'),
            'password' => $password,
        ]);
        $user->forceFill([
            'is_admin' => true,
            'activated' => true,
            'activation_token' => null,
        ]);
        $user->save();
    }
}
