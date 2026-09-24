<?php

use App\Models\Blog;
use App\Models\Setting;
use App\Models\User;

beforeEach(function () {
    Setting::set('activation_fee', 199);
    Setting::set('required_referrals', 2);
});

it('renders the home page', function () {
    $this->get('/')->assertOk()->assertSee('Unity');
});

it('renders the blog index with only published posts', function () {
    Blog::factory()->create(['status' => 'published', 'title' => 'Live Post', 'slug' => 'live-post']);
    Blog::factory()->create(['status' => 'draft', 'title' => 'Draft Post', 'slug' => 'draft-post']);

    $response = $this->get('/blog');

    $response->assertOk()->assertSee('Live Post')->assertDontSee('Draft Post');
});

it('renders a single published blog post', function () {
    Blog::factory()->create(['status' => 'published', 'title' => 'Hello World', 'slug' => 'hello-world']);

    $this->get('/blog/hello-world')->assertOk()->assertSee('Hello World');
});

it('404s for an unpublished or missing blog slug', function () {
    Blog::factory()->create(['status' => 'draft', 'slug' => 'draft-post']);

    $this->get('/blog/draft-post')->assertNotFound();
    $this->get('/blog/does-not-exist')->assertNotFound();
});

it('falls back to placeholder content when no about-unity post exists', function () {
    $this->get('/about')->assertOk()->assertSee('About Unity');
});

it('renders about-unity content when published', function () {
    Blog::factory()->create([
        'status' => 'published',
        'slug' => 'about-unity',
        'title' => 'About',
        'content' => 'Our mission is community growth.',
    ]);

    $this->get('/about')->assertOk()->assertSee('Our mission is community growth.', false);
});

it('redirects old referral links to the home page without showing referral content', function () {
    $referrer = User::factory()->create(['name' => 'Jane Doe']);

    $this->get('/r/'.$referrer->referral_code)->assertRedirect(route('home'));
    $this->get('/r/DOESNOTEXIST')->assertRedirect(route('home'));
});

it('does not mention the referral program on the home page', function () {
    $this->get('/')->assertOk()->assertDontSee('referral', false)->assertDontSee('Invite', false);
});
