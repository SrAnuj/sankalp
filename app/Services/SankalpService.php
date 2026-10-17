<?php

namespace App\Services;

use App\Models\NaamLibrary;
use App\Models\Sankalp;
use App\Models\SankalpProgressLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SankalpService
{
    /*
    |--------------------------------------------------------------------------
    | User App Methods
    |--------------------------------------------------------------------------
    */

    public function createForUser(int $userId, array $data): Sankalp
    {
        $naam = NaamLibrary::query()
            ->active()
            ->findOrFail($data['naam_library_id']);

        return Sankalp::create([
            'user_id' => $userId,
            'naam_library_id' => $naam->id,
            'target_count' => $data['target_count'],
            'completed_count' => 0,
            'status' => 'active',
            'started_at' => now(),
        ])->load(['user', 'naam']);
    }

    public function addProgress(
        int $userId,
        int $sankalpId,
        int $count
    ): Sankalp {
        return DB::transaction(function () use (
            $userId,
            $sankalpId,
            $count
        ) {
            $sankalp = Sankalp::query()
                ->where('user_id', $userId)
                ->whereKey($sankalpId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($sankalp->status !== 'active') {
                throw ValidationException::withMessages([
                    'sankalp' => 'Progress can only be added to an active Sankalp.',
                ]);
            }

            $newTotal = (int) $sankalp->completed_count + $count;

            if ($newTotal > (int) $sankalp->target_count) {
                throw ValidationException::withMessages([
                    'count' => 'Progress cannot exceed the Sankalp target.',
                ]);
            }

            $today = now()->toDateString();

            $log = SankalpProgressLog::query()->firstOrCreate(
                [
                    'sankalp_id' => $sankalp->id,
                    'progress_date' => $today,
                ],
                ['count' => 0]
            );

            $log->increment('count', $count);

            $sankalp->completed_count = $newTotal;

            if ($newTotal === (int) $sankalp->target_count) {
                $sankalp->status = 'completed';
                $sankalp->completed_at = now();
            }

            $sankalp->save();

            return $sankalp->refresh()->load(['user', 'naam']);
        });
    }

    public function getUserSankalps(
        int $userId,
        int $perPage = 15
    ): LengthAwarePaginator {
        return Sankalp::query()
            ->with(['naam'])
            ->where('user_id', $userId)
            ->latest('id')
            ->paginate($perPage);
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Listing & Details
    |--------------------------------------------------------------------------
    */

    public function paginateForAdmin(
        array $filters
    ): LengthAwarePaginator {
        return Sankalp::query()
            ->with(['user', 'naam'])
            ->when(
                $filters['user_id'] ?? null,
                fn ($query, $userId) => $query->where('user_id', $userId)
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($searchQuery) use ($search) {
                        $searchQuery
                            ->whereHas('user', function ($userQuery) use ($search) {
                                $userQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            })
                            ->orWhereHas('naam', function ($naamQuery) use ($search) {
                                $naamQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('name_hindi', 'like', "%{$search}%")
                                    ->orWhere('name_punjabi', 'like', "%{$search}%");
                            });
                    });
                }
            )
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    public function findForAdmin(int $id): Sankalp
    {
        return Sankalp::query()
            ->with(['user', 'naam', 'progressLogs'])
            ->findOrFail($id);
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Create
    |--------------------------------------------------------------------------
    */

    public function create(array $data): Sankalp
    {
       
        return DB::transaction(function () use ($data) {
            User::query()->findOrFail($data['user_id']);

            $naam = NaamLibrary::query()
                ->active()
                ->findOrFail($data['naam_library_id']);

            $sankalp = Sankalp::create([
                'user_id' => $data['user_id'],
                'naam_library_id' => $naam->id,
                'target_count' => $data['target_count'],
                'completed_count' => 0,
                'status' => 'active',
                'started_at' => now(),
            ]);

            return $sankalp->load(['user', 'naam']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Update
    |--------------------------------------------------------------------------
    */

    public function update(int $id, array $data): Sankalp
    {
        return DB::transaction(function () use ($id, $data) {
            $sankalp = Sankalp::query()
                ->lockForUpdate()
                ->findOrFail($id);

            $hasProgress = (int) $sankalp->completed_count > 0
                || $sankalp->progressLogs()->exists();

            if (
                $hasProgress
                && (
                    array_key_exists('user_id', $data)
                    || array_key_exists('naam_library_id', $data)
                )
            ) {
                throw ValidationException::withMessages([
                    'sankalp' => 'User and Naam cannot be changed after progress has been recorded.',
                ]);
            }

            if (array_key_exists('user_id', $data)) {
                User::query()->findOrFail($data['user_id']);
                $sankalp->user_id = $data['user_id'];
            }

            if (array_key_exists('naam_library_id', $data)) {
                $naam = NaamLibrary::query()
                    ->active()
                    ->findOrFail($data['naam_library_id']);

                $sankalp->naam_library_id = $naam->id;
            }

            if (array_key_exists('target_count', $data)) {
                $target = (int) $data['target_count'];
                $completed = (int) $sankalp->completed_count;

                if ($target < $completed) {
                    throw ValidationException::withMessages([
                        'target_count' => 'Target cannot be less than completed progress.',
                    ]);
                }

                $sankalp->target_count = $target;

                if ($target === $completed) {
                    $sankalp->status = 'completed';
                    $sankalp->completed_at ??= now();
                } elseif ($sankalp->status === 'completed') {
                    throw ValidationException::withMessages([
                        'target_count' => 'A completed Sankalp cannot be reopened by changing its target.',
                    ]);
                }
            }

            $sankalp->save();

            return $sankalp->refresh()->load(['user', 'naam']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Status Update
    |--------------------------------------------------------------------------
    */

    public function updateStatus(int $id, string $status): Sankalp
    {
        return DB::transaction(function () use ($id, $status) {
            $sankalp = Sankalp::query()
                ->lockForUpdate()
                ->findOrFail($id);

            if ($sankalp->status === 'completed') {
                throw ValidationException::withMessages([
                    'status' => 'A completed Sankalp cannot be changed.',
                ]);
            }

            $sankalp->status = $status;

            if ($status === 'completed') {
                if (
                    (int) $sankalp->completed_count
                    < (int) $sankalp->target_count
                ) {
                    throw ValidationException::withMessages([
                        'status' => 'Sankalp cannot be completed before reaching its target.',
                    ]);
                }

                $sankalp->completed_at ??= now();
            }

            $sankalp->save();

            return $sankalp->refresh()->load(['user', 'naam']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Delete
    |--------------------------------------------------------------------------
    */

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $sankalp = Sankalp::query()
                ->lockForUpdate()
                ->findOrFail($id);

            // Remove dependent daily progress logs first.
            $sankalp->progressLogs()->delete();

            $sankalp->delete();
        });
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $sankalps = Sankalp::query()
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get();

            $deletedCount = 0;

            foreach ($sankalps as $sankalp) {
                $sankalp->progressLogs()->delete();

                if ($sankalp->delete()) {
                    $deletedCount++;
                }
            }

            return $deletedCount;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Summary
    |--------------------------------------------------------------------------
    */

    public function summary(): array
    {
        $counts = Sankalp::query()
            ->selectRaw('COUNT(*) as total_sankalps')
            ->selectRaw(
                "SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_sankalps"
            )
            ->selectRaw(
                "SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_sankalps"
            )
            ->selectRaw(
                "SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_sankalps"
            )
            ->first();

        return [
            'total_sankalps' => (int) $counts->total_sankalps,
            'active_sankalps' => (int) $counts->active_sankalps,
            'completed_sankalps' => (int) $counts->completed_sankalps,
            'cancelled_sankalps' => (int) $counts->cancelled_sankalps,
        ];
    }
}
