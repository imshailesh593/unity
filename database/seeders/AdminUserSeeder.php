<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

        $admin->syncPermissions(Permission::all());

        $editor->syncPermissions(
            Permission::query()
                ->where(function ($query) {
                    $query->where('name', 'like', '%_blog')
                        ->orWhere('name', 'like', '%_category')
                        ->orWhere('name', 'like', '%_banner');
                })
                ->get()
        );

        // Keyed on "does any admin exist" rather than a fixed email/phone —
        // those get rotated in ops (see RotateAdminPassword), so re-running
        // this seeder must never try to recreate the default admin once a
        // real one is in place.
        if (User::role('admin')->doesntExist()) {
            $adminUser = User::create([
                'name' => 'Unity Admin',
                'email' => 'admin@unityapp.in',
                'phone' => '9999999999',
                'password' => 'password',
                'status' => 'active',
                'has_paid' => true,
                'email_verified_at' => now(),
            ]);

            $adminUser->assignRole('admin');
        }
    }
}
