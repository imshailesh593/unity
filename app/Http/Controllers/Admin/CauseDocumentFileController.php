<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CauseDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Streams a cause's supporting document (report, bill, estimate...) to an
 * admin reviewing it. Same private-disk + signed-URL + role-check pattern as
 * DocumentFileController for Aadhaar documents.
 */
class CauseDocumentFileController extends Controller
{
    public function show(Request $request, CauseDocument $causeDocument)
    {
        abort_unless($request->user()?->hasRole('admin') || $request->user()?->hasRole('editor'), 403);
        abort_unless(Storage::disk('local')->exists($causeDocument->file_path), 404);

        return Storage::disk('local')->response($causeDocument->file_path);
    }
}
