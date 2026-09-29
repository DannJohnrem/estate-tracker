<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    // Route access is enforced by the "permission:" middleware in routes/web.php.
    // Success/error toasts are triggered client-side (vue-sonner), so no flash data here.

    // Extensions treated as images (everything else is shown in the PDF viewer)
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function index(): Response
    {
        $documents = Document::with('uploader:id,name')
            ->latest()
            ->paginate(12)
            ->through(fn (Document $document) => $this->transform($document));

        // NOTE: the folder on disk is "Documents" (capital D), so the render path must match
        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'can' => [
                'create' => Gate::allows('documents.create'),
                'edit' => Gate::allows('documents.edit'),
                'delete' => Gate::allows('documents.delete'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Documents/Create');
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $file = $request->file('file');

        Document::create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'file_path' => $file->store('documents', 'local'),
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return to_route('documents.index');
    }

    public function show(Document $document): Response
    {
        return Inertia::render('Documents/Show', [
            'document' => $this->transform($document->load('uploader:id,name')),
            'can' => [
                'edit' => Gate::allows('documents.edit'),
                'delete' => Gate::allows('documents.delete'),
            ],
        ]);
    }

    public function edit(Document $document): Response
    {
        return Inertia::render('Documents/Edit', [
            'document' => $this->transform($document),
        ]);
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $data = [
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
        ];

        // Replace the file only when a new one is uploaded
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Remove the old file from storage before saving the new one
            Storage::disk('local')->delete($document->file_path);

            $data['file_path'] = $file->store('documents', 'local');
            $data['original_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
        }

        $document->update($data);

        return to_route('documents.index');
    }

    public function destroy(Document $document): RedirectResponse
    {
        // Delete the file from storage, then the database record
        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return to_route('documents.index');
    }

    /**
     * Stream the file inline (PDF viewer iframe or image preview).
     * Pass ?download=1 to force a download instead.
     */
    public function file(Document $document): StreamedResponse
    {
        $disk = Storage::disk('local');

        abort_unless($disk->exists($document->file_path), 404);

        // Detect the real content type (application/pdf, image/jpeg, image/png)
        $mime = $disk->mimeType($document->file_path) ?: 'application/octet-stream';

        if (request()->boolean('download')) {
            return $disk->download($document->file_path, $document->original_name, [
                'Content-Type' => $mime,
            ]);
        }

        // Private browser cache for 1 day so PHP only streams the file on the first open.
        // The ?v= query in the Vue pages busts the cache when the file is replaced.
        return $disk->response($document->file_path, $document->original_name, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=86400',
        ], 'inline');
    }

    /**
     * Shape a Document for the frontend.
     */
    private function transform(Document $document): array
    {
        // The stored file keeps its extension, so the type can be read from the path
        $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));

        return [
            'id' => $document->id,
            'name' => $document->name,
            'description' => $document->description,
            'original_name' => $document->original_name,
            'file_size' => $document->file_size,
            'extension' => $extension,
            'is_image' => in_array($extension, self::IMAGE_EXTENSIONS, true),
            'uploaded_by' => $document->uploader?->name,
            'uploaded_at' => $document->created_at?->format('M d, Y'),
            // Changes whenever the document is updated (used for cache busting)
            'version' => $document->updated_at?->timestamp,
        ];
    }
}
