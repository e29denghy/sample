<?php

namespace Tests\Feature;

use App\Mail\RegistrationConfirmation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LegacyApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_unactivated_user_and_sends_confirmation_mail(): void
    {
        Mail::fake();

        $response = $this->post(route('users.store'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'activated' => 0,
        ]);
        $this->assertNotNull(User::where('email', 'new@example.com')->value('activation_token'));
        Mail::assertSent(RegistrationConfirmation::class, function (RegistrationConfirmation $mail): bool {
            return $mail->hasTo('new@example.com');
        });
    }

    public function test_unactivated_users_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'activated' => false,
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('warning');
        $this->assertGuest();
    }

    public function test_password_reset_form_and_public_user_list_are_available(): void
    {
        User::factory()->count(2)->create();

        $this->get(route('password.request'))->assertOk();
        $this->get(route('users.index'))->assertOk();
    }
}
