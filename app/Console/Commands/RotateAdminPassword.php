<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:rotate-admin-password {email} {password} {--new-email=}')]
#[Description('Set a user\'s email/password by current email — for ops use when shell access is unavailable.')]
class RotateAdminPassword extends Command
{
    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("No user found with email {$this->argument('email')}");

            return self::FAILURE;
        }

        if ($newEmail = $this->option('new-email')) {
            $user->email = $newEmail;
        }

        $user->password = $this->argument('password');
        $user->save();

        $this->info("Credentials updated for {$user->email}.");

        return self::SUCCESS;
    }
}
