<?php

namespace App\Services\Admin;

use App\Models\NaamLibrary;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NaamLibraryService
{

public function paginate(array $filters): LengthAwarePaginator
{
    return NaamLibrary::query()
        ->with([
            'parent:id,name,slug',
        ])
        ->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_hindi', 'like', "%{$search}%")
                    ->orWhere('name_punjabi', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        })
        ->when(
            array_key_exists('parent_id', $filters)
                && $filters['parent_id'] !== '',
            function ($query) use ($filters) {
                if ($filters['parent_id'] === null) {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $filters['parent_id']);
                }
            }
        )
        ->when(
            $filters['status'] ?? null,
            fn ($query, $status) => $query->where('status', $status)
        )
        ->orderBy('sort_order')
        ->orderByDesc('id')
        ->paginate((int) ($filters['per_page'] ?? 15))
        ->withQueryString();
}
    public function find(int $id): NaamLibrary
    {
        return NaamLibrary::query()->findOrFail($id);
    }

    public function create(array $data): NaamLibrary
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = !empty($data['slug'])
                ? $data['slug']
                : $this->generateUniqueSlug($data['name']);

            return NaamLibrary::create($data);
        });
    }

    public function update(int $id, array $data): NaamLibrary
    {
        return DB::transaction(function () use ($id, $data) {
            $naam = NaamLibrary::query()->findOrFail($id);

            $naam->fill($data);
            $naam->save();

            return $naam->refresh();
        });
    }

    public function updateStatus(int $id, string $status): NaamLibrary
    {
        $naam = NaamLibrary::query()->findOrFail($id);

        $naam->status = $status;
        $naam->save();

        return $naam->refresh();
    }

    public function updateSortOrder(int $id, int $sortOrder): NaamLibrary
    {
        $naam = NaamLibrary::query()->findOrFail($id);

        $naam->sort_order = $sortOrder;
        $naam->save();

        return $naam->refresh();
    }

    public function delete(int $id): void
    {
        $naam = NaamLibrary::query()->findOrFail($id);
        $naam->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $naams = NaamLibrary::query()
                ->whereIn('id', $ids)
                ->get();

            foreach ($naams as $naam) {
                $naam->delete();
            }

            return $naams->count();
        });
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'naam';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            NaamLibrary::withTrashed()->where('slug', $slug)->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
