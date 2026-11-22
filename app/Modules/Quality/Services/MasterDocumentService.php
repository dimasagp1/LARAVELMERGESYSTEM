<?php

namespace App\Modules\Quality\Services;

use App\Models\User;
use App\Modules\Quality\Models\DocumentFolder;
use App\Modules\Quality\Models\DocumentRevision;
use App\Modules\Quality\Models\MasterDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MasterDocumentService
{
    /**
     * Get paginated documents with filtering.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<MasterDocument>
     */
    public function getDocuments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = MasterDocument::with(['folder', 'revisions.createdByUser'])
            ->orderByDesc('id');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%")
                    ->orWhere('document_code', 'like', "%{$search}%")
                    ->orWhere('no_perubahan_cc', 'like', "%{$search}%")
                    ->orWhere('tempat_penyimpanan', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['level'])) {
            $query->where('level', (int) $filters['level']);
        }

        if (! empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (! empty($filters['scope'])) {
            $query->where('scope', $filters['scope']);
        }

        if (! empty($filters['folder_id'])) {
            $folder = DocumentFolder::find($filters['folder_id']);
            if ($folder) {
                $folderIds = $folder->getAllDescendantIds();
                $query->whereIn('folder_id', $folderIds);
            }
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get root folders with nested children for navigation tree.
     *
     * @return Collection<int, DocumentFolder>
     */
    public function getFoldersTree(): Collection
    {
        return DocumentFolder::with(['children.children', 'documents'])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Create a new Master Document and log the initial revision.
     *
     * @param  array<string, mixed>  $data
     */
    public function createDocument(array $data, ?User $user = null): MasterDocument
    {
        return DB::transaction(function () use ($data, $user) {
            $document = MasterDocument::create([
                'folder_id' => $data['folder_id'] ?? null,
                'level' => $data['level'] ?? 2,
                'document_code' => $data['document_code'] ?? 'SOP',
                'document_number' => $data['document_number'],
                'title' => $data['title'],
                'scope' => $data['scope'] ?? null,
                'department' => $data['department'] ?? 'QA',
                'document_links' => $data['document_links'] ?? [],
                'tempat_penyimpanan' => $data['tempat_penyimpanan'] ?? null,
                'no_perubahan_cc' => $data['no_perubahan_cc'] ?? null,
                'status' => $data['status'] ?? 'Active',
                'effective_date' => $data['effective_date'] ?? now()->toDateString(),
                'revision' => $data['revision'] ?? '00',
                'custom_attributes' => $data['custom_attributes'] ?? null,
            ]);

            // Create initial revision entry
            DocumentRevision::create([
                'master_document_id' => $document->id,
                'created_by_user_id' => $user?->id,
                'revision' => $document->revision,
                'title' => $document->title,
                'effective_date' => $document->effective_date ?? now()->toDateString(),
                'change_notes' => 'Penerbitan dokumen pertama kali.',
            ]);

            return $document->fresh(['folder', 'revisions']);
        });
    }

    /**
     * Update an existing document and optionally register a new revision.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateDocument(MasterDocument $document, array $data, ?User $user = null): MasterDocument
    {
        return DB::transaction(function () use ($document, $data, $user) {
            $oldRevision = $document->revision;
            $newRevision = $data['revision'] ?? $oldRevision;
            $hasRevisionChanged = $newRevision !== $oldRevision;

            $document->update([
                'folder_id' => $data['folder_id'] ?? $document->folder_id,
                'level' => $data['level'] ?? $document->level,
                'document_code' => $data['document_code'] ?? $document->document_code,
                'document_number' => $data['document_number'] ?? $document->document_number,
                'title' => $data['title'] ?? $document->title,
                'scope' => $data['scope'] ?? $document->scope,
                'department' => $data['department'] ?? $document->department,
                'document_links' => $data['document_links'] ?? $document->document_links,
                'tempat_penyimpanan' => $data['tempat_penyimpanan'] ?? $document->tempat_penyimpanan,
                'no_perubahan_cc' => $data['no_perubahan_cc'] ?? $document->no_perubahan_cc,
                'status' => $data['status'] ?? $document->status,
                'effective_date' => $data['effective_date'] ?? $document->effective_date,
                'revision' => $newRevision,
                'custom_attributes' => $data['custom_attributes'] ?? $document->custom_attributes,
            ]);

            if ($hasRevisionChanged || ! empty($data['change_notes'])) {
                DocumentRevision::create([
                    'master_document_id' => $document->id,
                    'created_by_user_id' => $user?->id,
                    'revision' => $newRevision,
                    'title' => $document->title,
                    'effective_date' => $document->effective_date ?? now()->toDateString(),
                    'change_notes' => $data['change_notes'] ?? "Pembaruan ke revisi {$newRevision}",
                ]);
            }

            return $document->fresh(['folder', 'revisions']);
        });
    }

    /**
     * Move document to a target folder.
     */
    public function moveDocument(MasterDocument $document, ?int $folderId): MasterDocument
    {
        $document->update(['folder_id' => $folderId]);

        return $document->fresh('folder');
    }

    /**
     * Delete document.
     */
    public function deleteDocument(MasterDocument $document): bool
    {
        return (bool) $document->delete();
    }

    /**
     * Create document folder.
     *
     * @param  array<string, mixed>  $data
     */
    public function createFolder(array $data): DocumentFolder
    {
        return DocumentFolder::create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'color' => $data['color'] ?? '#0d9488',
            'icon' => $data['icon'] ?? 'Folder',
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }
}
