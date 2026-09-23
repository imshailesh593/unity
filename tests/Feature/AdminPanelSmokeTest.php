<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Artisan::call('shield:generate', ['--all' => true, '--panel' => 'admin', '--no-interaction' => true]);

    $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $role->syncPermissions(Permission::all());

    $this->admin = User::factory()->create(['status' => 'active', 'has_paid' => true]);
    $this->admin->assignRole('admin');
});

it('lets an admin reach the dashboard', function () {
    $this->actingAs($this->admin)
        ->get('/admin')
        ->assertOk();
});

it('renders every core admin resource index page', function () {
    $routes = [
        '/admin/users',
        '/admin/payments',
        '/admin/referrals',
        '/admin/blogs',
        '/admin/categories',
        '/admin/banners',
        '/admin/settings',
        '/admin/shield/roles',
        '/admin/broadcast-notification',
        '/admin/causes',
        '/admin/sos-alerts',
    ];

    foreach ($routes as $route) {
        $this->actingAs($this->admin)->get($route)->assertOk();
    }
});

it('blocks a non-admin user from the panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});
