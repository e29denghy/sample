<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_activated_admin_with_a_hidden_password(): void
    {
        $this->artisan('admin:create', [
            'email' => 'admin@example.com',
            '--name' => 'Site Admin',
        ])
            ->expectsQuestion('Password (minimum 12 characters)', 'Strong-admin-password-2026')
            ->expectsQuestion('Confirm password', 'Strong-admin-password-2026')
            ->expectsOutputToContain('Administrator ready: admin@example.com')
            ->assertSuccessful();

        $user = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertSame('Site Admin', $user->name);
        $this->assertTrue($user->is_admin);
        $this->assertTrue($user->activated);
        $this->assertNull($user->activation_token);
        $this->assertTrue(Hash::check('Strong-admin-password-2026', $user->password));
    }

    public function test_it_can_generate_a_password_without_exposing_it_to_the_command_line(): void
    {
        $this->artisan('admin:create', [
            'email' => 'generated@example.com',
            '--name' => 'Generated Admin',
            '--generate' => true,
        ])
            ->expectsOutputToContain('Administrator ready: generated@example.com')
            ->expectsOutputToContain('Generated password (shown once):')
            ->assertSuccessful();

        $user = User::where('email', 'generated@example.com')->firstOrFail();

        $this->assertTrue($user->is_admin);
        $this->assertTrue($user->activated);
        $this->assertNotSame('', $user->password);
    }

    public function test_it_refuses_to_overwrite_an_existing_user_without_update(): void
    {
        $user = User::factory()->create(['email' => 'existing@example.com']);
        $originalPassword = $user->password;

        $this->artisan('admin:create', [
            'email' => 'existing@example.com',
            '--generate' => true,
        ])
            ->expectsOutputToContain('A user with this email already exists.')
            ->assertFailed();

        $user->refresh();

        $this->assertFalse($user->is_admin);
        $this->assertSame($originalPassword, $user->password);
    }
}
