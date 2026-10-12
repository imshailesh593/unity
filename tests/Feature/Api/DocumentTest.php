<?php

use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('requires authentication to list or upload documents', function () {
    $this->getJson('/api/v1/documents')->assertUnauthorized();
    $this->postJson('/api/v1/documents', [])->assertUnauthorized();
});

it('rejects an unknown document type', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/documents', [
            'type' => 'passport',
            'file' => UploadedFile::fake()->image('doc.jpg'),
        ])->assertUnprocessable();
});

it('rejects a file that is too large or the wrong type', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/documents', [
            'type' => 'aadhar_front',
            'file' => UploadedFile::fake()->create('doc.exe', 10),
        ])->assertUnprocessable();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/documents', [
            'type' => 'aadhar_front',
            'file' => UploadedFile::fake()->image('big.jpg')->size(6000),
        ])->assertUnprocessable();
});

it('uploads a document and stores it on the private disk', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/documents', [
            'type' => 'aadhar_front',
            'file' => UploadedFile::fake()->image('front.jpg'),
        ]);

    $response->assertCreated()->assertJsonPath('data.type', 'aadhar_front')->assertJsonPath('data.status', 'pending');

    $document = UserDocument::query()->where('user_id', $user->id)->where('type', 'aadhar_front')->firstOrFail();
    Storage::disk('local')->assertExists($document->file_path);
});

it('replaces the previous file and resets review state on re-upload', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')->postJson('/api/v1/documents', [
        'type' => 'aadhar_front',
        'file' => UploadedFile::fake()->image('front.jpg'),
    ]);

    $document = UserDocument::query()->where('user_id', $user->id)->where('type', 'aadhar_front')->firstOrFail();
    $oldPath = $document->file_path;
    $document->update(['status' => 'rejected', 'rejection_reason' => 'Blurry photo']);

    $this->actingAs($user, 'sanctum')->postJson('/api/v1/documents', [
        'type' => 'aadhar_front',
        'file' => UploadedFile::fake()->image('front-retake.jpg'),
    ])->assertOk()->assertJsonPath('data.status', 'pending')->assertJsonPath('data.rejection_reason', null);

    Storage::disk('local')->assertMissing($oldPath);
    expect(UserDocument::query()->where('user_id', $user->id)->count())->toBe(1);
});

it('only lists the authenticated user\'s own documents', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    UserDocument::factory()->for($user)->create(['type' => 'aadhar_front']);
    UserDocument::factory()->for($otherUser)->create(['type' => 'aadhar_front']);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/documents');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
});
