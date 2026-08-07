<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
        {email? : Email address for the administrator}
        {--name=admin : Display name for the administrator}
        {--generate : Generate a 24-character password and show it once}
        {--update : Update an existing user with the same email address}';

    protected $description = 'Create or update an activated administrator account';

    public function handle(): int
    {
        $email = (string) ($this->argument('email') ?: $this->ask('Admin email'));
        $name = (string) $this->option('name');
        $password = $this->option('generate')
            ? Str::password(24)
            : $this->askForPassword();

        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:12'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user && ! $this->option('update')) {
            $this->error('A user with this email already exists. Use --update to promote or reset it.');

            return self::FAILURE;
        }

        $user ??= new User;
        $user->fill([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
        $user->forceFill([
            'is_admin' => true,
            'activated' => true,
            'activation_token' => null,
        ]);
        $user->save();

        $this->info("Administrator ready: {$user->email}");

        if ($this->option('generate')) {
            $this->newLine();
            $this->warn('Generated password (shown once):');
            $this->line($password);
        }

        return self::SUCCESS;
    }

    private function askForPassword(): string
    {
        $password = (string) $this->secret('Password (minimum 12 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        if (! hash_equals($password, $confirmation)) {
            $this->error('The password confirmation does not match.');

            return '';
        }

        return $password;
    }
}
