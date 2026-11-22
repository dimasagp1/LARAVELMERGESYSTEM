<?php

namespace App\Modules\Quality\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Quality\Models\MasterDocument;
use App\Modules\Quality\Services\MasterDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MasterDocumentController extends Controller
{
    public function __construct(
        protected MasterDocumentService $documentService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'level', 'department', 'scope', 'folder_id', 'status']);
        $documents = $this->documentService->getDocuments($filters);
        $folders = $this->documentService->getFoldersTree();

        return Inertia::render('Modules/Quality/Documents/Index', [
            'module' => [
                'name' => 'Quality Management System',
                'slug' => 'quality',
                'color_theme' => 'teal',
                'menus' => $this->moduleManager->getModuleMenus('quality'),
            ],
            'documents' => $documents,
            'folders' => $folders,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        $folders = $this->documentService->getFoldersTree();

        return Inertia::render('Modules/Quality/Documents/Form', [
            'module' => [
                'name' => 'Quality Management System',
                'slug' => 'quality',
                'color_theme' => 'teal',
                'menus' => $this->moduleManager->getModuleMenus('quality'),
            ],
            'document' => null,
            'folders' => $folders,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'document_number' => ['required', 'string', 'unique:quality_master_documents,document_number'],
            'title' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'between:1,4'],
            'document_code' => ['required', 'string', 'max:20'],
            'scope' => ['nullable', 'string'],
            'department' => ['required', 'string'],
            'folder_id' => ['nullable', 'exists:quality_document_folders,id'],
            'tempat_penyimpanan' => ['nullable', 'string'],
            'no_perubahan_cc' => ['nullable', 'string'],
            'effective_date' => ['nullable', 'date'],
            'revision' => ['nullable', 'string', 'max:10'],
            'document_links' => ['nullable', 'array'],
        ]);

        $this->documentService->createDocument($validated, $request->user());

        return redirect()->route('quality.documents.index')
            ->with('success', 'Dokumen Mutu berhasil ditambahkan.');
    }

    public function edit(MasterDocument $document): Response
    {
        $document->load(['folder', 'revisions.createdByUser']);
        $folders = $this->documentService->getFoldersTree();

        return Inertia::render('Modules/Quality/Documents/Form', [
            'module' => [
                'name' => 'Quality Management System',
                'slug' => 'quality',
                'color_theme' => 'teal',
                'menus' => $this->moduleManager->getModuleMenus('quality'),
            ],
            'document' => $document,
            'folders' => $folders,
        ]);
    }

    public function update(Request $request, MasterDocument $document): RedirectResponse
    {
        $validated = $request->validate([
            'document_number' => ['required', 'string', 'unique:quality_master_documents,document_number,'.$document->id],
            'title' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'between:1,4'],
            'document_code' => ['required', 'string', 'max:20'],
            'scope' => ['nullable', 'string'],
            'department' => ['required', 'string'],
            'folder_id' => ['nullable', 'exists:quality_document_folders,id'],
            'tempat_penyimpanan' => ['nullable', 'string'],
            'no_perubahan_cc' => ['nullable', 'string'],
            'effective_date' => ['nullable', 'date'],
            'revision' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'string', 'in:Active,Obsolete'],
            'change_notes' => ['nullable', 'string'],
            'document_links' => ['nullable', 'array'],
        ]);

        $this->documentService->updateDocument($document, $validated, $request->user());

        return redirect()->route('quality.documents.index')
            ->with('success', 'Dokumen Mutu berhasil diperbarui.');
    }

    public function destroy(MasterDocument $document): RedirectResponse
    {
        $this->documentService->deleteDocument($document);

        return redirect()->route('quality.documents.index')
            ->with('success', 'Dokumen Mutu berhasil dihapus.');
    }

    public function move(Request $request, MasterDocument $document): RedirectResponse
    {
        $validated = $request->validate([
            'folder_id' => ['nullable', 'exists:quality_document_folders,id'],
        ]);

        $this->documentService->moveDocument($document, $validated['folder_id'] ?? null);

        return redirect()->back()->with('success', 'Dokumen berhasil dipindahkan.');
    }

    public function storeFolder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:quality_document_folders,id'],
            'color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $this->documentService->createFolder($validated);

        return redirect()->back()->with('success', 'Folder Dokumen berhasil dibuat.');
    }
}
