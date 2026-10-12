<ul class="space-y-2">
    @foreach ($documents as $document)
        <li>
            <a
                href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('admin.cause-documents.view', now()->addMinutes(10), ['causeDocument' => $document->id]) }}"
                target="_blank"
                rel="noopener"
                class="text-primary-600 hover:underline"
            >
                Document #{{ $loop->iteration }} — {{ $document->created_at->format('d M Y, h:i A') }}
            </a>
        </li>
    @endforeach
</ul>
