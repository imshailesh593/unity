<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Auth;

#[Signature('app:check-firebase-connection')]
#[Description('Diagnostic: confirms the Firebase Admin SDK resolves with the configured credentials — ops use, no arguments needed.')]
class CheckFirebaseConnection extends Command
{
    public function handle(): int
    {
        try {
            app(Auth::class);
            $this->info('Firebase Admin SDK resolved OK.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Firebase Admin SDK failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
