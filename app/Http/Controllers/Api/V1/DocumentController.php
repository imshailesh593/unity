<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UploadDocumentRequest;
use App\Http\Resources\UserDocumentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        return UserDocumentResource::collection($request->user()->documents);
    }

    /**
     * Uploading the same type again replaces the previous file and resets
     * review state — a user correcting a rejected document shouldn't need a
     * separate endpoint.
     */
    public function store(UploadDocumentRequest $request)
    {
        $user = $request->user();
        $type = $request->string('type')->toString();

        $existing = $user->documents()->where('type', $type)->first();
        if ($existing) {
            Storage::disk('local')->delete($existing->file_path);
        }

        $path = $request->file('file')->store("documents/{$user->id}", 'local');

        $document = $user->documents()->updateOrCreate(
            ['type' => $type],
            [
                'file_path' => $path,
                'status' => 'pending',
                'rejection_reason' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ],
        );

        return new UserDocumentResource($document);
    }
}
