<?php

use App\Models\Banner;
use App\Models\Blog;
use App\Models\Category;

it('lists only published blogs', function () {
    Blog::factory()->create(['status' => 'published', 'slug' => 'live-post', 'title' => 'Live']);
    Blog::factory()->create(['status' => 'draft', 'slug' => 'draft-post', 'title' => 'Draft']);

    $response = $this->getJson('/api/v1/blogs');

    $response->assertOk();
    $slugs = collect($response->json('data'))->pluck('slug');
    expect($slugs)->toContain('live-post')->not->toContain('draft-post');
});

it('shows a single published blog by slug', function () {
    Blog::factory()->create(['status' => 'published', 'slug' => 'hello-world', 'title' => 'Hello World']);

    $this->getJson('/api/v1/blogs/hello-world')
        ->assertOk()
        ->assertJsonPath('data.title', 'Hello World');
});

it('lists categories', function () {
    Category::factory()->create(['name' => 'Impact Stories', 'slug' => 'impact-stories']);

    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonFragment(['slug' => 'impact-stories']);
});

it('only returns active banners within their schedule window', function () {
    Banner::factory()->create(['active' => true, 'title' => 'Live banner']);
    Banner::factory()->create(['active' => false, 'title' => 'Inactive banner']);
    Banner::factory()->create([
        'active' => true,
        'title' => 'Expired banner',
        'starts_at' => now()->subDays(10),
        'ends_at' => now()->subDay(),
    ]);

    $response = $this->getJson('/api/v1/banners');

    $titles = collect($response->json('data'))->pluck('title');
    expect($titles)->toContain('Live banner')
        ->not->toContain('Inactive banner')
        ->not->toContain('Expired banner');
});

it('exposes public activation settings', function () {
    $this->getJson('/api/v1/settings/public')
        ->assertOk()
        ->assertJson(['activation_fee' => 199, 'required_referrals' => 2]);
});
