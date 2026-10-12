<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Streams a user's uploaded document to an admin reviewing it. The file
 * lives on the private 'local' disk — this is the only way to view it, kept
 * behind a short-lived signed URL (generated only from inside the Filament
 * table action) plus an explicit admin/editor role check.
 */
class DocumentFileController extends Controller
{
    public function show(Request $request, UserDocument $document)
    {
        abort_unless($request->user()?->hasRole('admin') || $request->user()?->hasRole('editor'), 403);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->response($document->file_path);
    }
}
