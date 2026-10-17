<?php

namespace App\Services;

use App\Models\Sankalp;
use App\Models\SankalpProgressLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SankalpProgressService
{
    /**
     * Add progress for a specific date.
     *
     * Existing progress for the same Sankalp and date is incremented.
     */
    public function addProgress(
        int $userId,
        int $sankalpId,
        int $count,
        ?string $progressDate = null
    ): Sankalp {
        return DB::transaction(function () use (
            $userId,
            $sankalpId,
            $count,
            $progressDate
        ) {
            $sankalp = Sankalp::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->findOrFail($sankalpId);

            $date = $progressDate ?? now()->toDateString();

            $progressLog = SankalpProgressLog::query()
                ->where('sankalp_id', $sankalp->id)
                ->where('progress_date', $date)
                ->lockForUpdate()
                ->first();

            if ($progressLog) {
                $progressLog->count =
                    (int) $progressLog->count + $count;

                $progressLog->save();
            } else {
                $progressLog = SankalpProgressLog::create([
                    'sankalp_id' => $sankalp->id,
                    'progress_date' => $date,
                    'count' => $count,
                ]);
            }

            $sankalp->completed_count =
                (int) $sankalp->completed_count + $count;

            if (
                (int) $sankalp->target_count > 0
                && (int) $sankalp->completed_count
                    >= (int) $sankalp->target_count
            ) {
                $sankalp->status = 'completed';
                $sankalp->completed_at ??= now();
            }

            $sankalp->save();

            return $sankalp->load(['user', 'naam']);
        });
    }

    /**
     * Get all progress logs belonging to the user's Sankalps.
     *
     * @return Collection<int, SankalpProgressLog>
     */
    public function getProgressRecords(int $userId): Collection
    {
        $userSankalpIds = Sankalp::query()
            ->where('user_id', $userId)
            ->select('id');

        return SankalpProgressLog::query()
            ->whereIn('sankalp_id', $userSankalpIds)
            ->orderByDesc('progress_date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Get daily progress history for a user's Sankalp.
     *
     * @return Collection<int, SankalpProgressLog>
     */
    public function getDailyProgress(
        int $userId,
        int $sankalpId
    ): Collection {
        $sankalp = Sankalp::query()
            ->where('user_id', $userId)
            ->findOrFail($sankalpId);

        return SankalpProgressLog::query()
            ->where('sankalp_id', $sankalp->id)
            ->orderByDesc('progress_date')
            ->orderByDesc('id')
            ->get();
    }
}
