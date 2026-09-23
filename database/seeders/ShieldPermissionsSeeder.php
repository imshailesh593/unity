<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ShieldPermissionsSeeder extends Seeder
{
    /**
     * Regenerates Filament Shield permissions/policies for every resource, page, and
     * widget on the admin panel. Required after every migrate:fresh — shield:generate
     * writes straight to the DB rather than via migrations, so nothing else repopulates it.
     */
    public function run(): void
    {
        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--no-interaction' => true,
        ]);
    }
}
