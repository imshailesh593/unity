<?php

use App\Models\Cause;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('lists only published causes', function () {
    Cause::factory()->create(['status' => 'published', 'title' => 'Live Cause', 'slug' => 'live-cause']);
    Cause::factory()->create(['status' => 'draft', 'title' => 'Draft Cause', 'slug' => 'draft-cause']);

    $response = $this->getJson('/api/v1/causes');

    $response->assertOk();
    $slugs = collect($response->json('data'))->pluck('slug');
    expect($slugs)->toContain('live-cause')->not->toContain('draft-cause');
});

it('shows a single published cause with progress percent', function () {
    Cause::factory()->create([
        'status' => 'published',
        'slug' => 'roof-rebuild',
        'goal_amount' => 100000,
        'raised_amount' => 45000,
    ]);

    $this->getJson('/api/v1/causes/roof-rebuild')
        ->assertOk()
        ->assertJsonPath('data.progress_percent', 45);
});

it('lets any member — not just organizers — raise a help request, starting as pending_review', function () {
    $member = User::factory()->create();

    $response = $this->actingAs($member, 'sanctum')
        ->postJson('/api/v1/causes', [
            'title' => 'New Cause',
            'content' => 'Details of the cause',
            'goal_amount' => 50000,
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('causes', [
        'title' => 'New Cause',
        'organizer_id' => $member->id,
        'status' => 'pending_review',
    ]);
});

it('stores attached supporting documents on the private disk', function () {
    Storage::fake('local');
    $member = User::factory()->create();

    $response = $this->actingAs($member, 'sanctum')
        ->postJson('/api/v1/causes', [
            'title' => 'Medical help needed',
            'content' => 'Details of the cause',
            'documents' => [
                UploadedFile::fake()->image('bill.jpg'),
                UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
            ],
        ]);

    $response->assertCreated();
    $cause = Cause::query()->where('title', 'Medical help needed')->firstOrFail();
    expect($cause->documents()->count())->toBe(2);
    $cause->documents->each(fn ($document) => Storage::disk('local')->assertExists($document->file_path));
});

it('rejects more than 4 attached documents', function () {
    $member = User::factory()->create();

    $this->actingAs($member, 'sanctum')
        ->postJson('/api/v1/causes', [
            'title' => 'New Cause',
            'content' => 'Details',
            'documents' => array_fill(0, 5, UploadedFile::fake()->image('doc.jpg')),
        ])->assertUnprocessable();
});

it('has no contribution endpoint — cause money-collection is disabled', function () {
    Cause::factory()->create(['status' => 'published', 'slug' => 'help-anita']);
    $member = User::factory()->create();

    $this->actingAs($member, 'sanctum')
        ->postJson('/api/v1/causes/help-anita/contribute', ['amount' => 500])
        ->assertNotFound();
});

it('allows an organizer to create a cause without a goal amount', function () {
    $organizer = User::factory()->organizer()->create();

    $response = $this->actingAs($organizer, 'sanctum')
        ->postJson('/api/v1/causes', [
            'title' => 'New Cause',
            'content' => 'Details of the cause',
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('causes', [
        'title' => 'New Cause',
        'organizer_id' => $organizer->id,
        'goal_amount' => 0,
        'status' => 'pending_review',
    ]);
});
