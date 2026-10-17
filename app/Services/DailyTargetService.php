<?php

namespace App\Services;

use App\Models\DailyTarget;
use App\Models\Sankalp;
use App\Models\SankalpProgressLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DailyTargetService
{
    public function getTargets(int $userId): Collection
    {
        return DailyTarget::query()
            ->whereHas('sankalp', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with('sankalp.naam')
            ->orderByDesc('effective_from')
            ->get();
    }

    public function getHistory(
        int $userId,
        int $sankalpId
    ): Collection {
        $this->getOwnedSankalp($userId, $sankalpId);

        return DailyTarget::query()
            ->where('sankalp_id', $sankalpId)
            ->orderByDesc('effective_from')
            ->get();
    }

    public function setTarget(
        int $userId,
        int $sankalpId,
        int $targetCount
    ): DailyTarget {
        return DB::transaction(function () use (
            $userId,
            $sankalpId,
            $targetCount
        ) {
            $sankalp = $this->getOwnedSankalp(
                $userId,
                $sankalpId,
                true
            );

            $today = now()->toDateString();

            $todayTarget = DailyTarget::query()
                ->where('sankalp_id', $sankalp->id)
                ->whereDate('effective_from', $today)
                ->lockForUpdate()
                ->first();

            // If today's target already exists, update it.
            if ($todayTarget) {
                $todayTarget->update([
                    'target_count' => $targetCount,
                    'effective_until' => null,
                ]);

                return $todayTarget->refresh();
            }

            // Close the previously active target.
            DailyTarget::query()
                ->where('sankalp_id', $sankalp->id)
                ->whereDate('effective_from', '<', $today)
                ->where(function ($query) use ($today) {
                    $query->whereNull('effective_until')
                        ->orWhereDate('effective_until', '>=', $today);
                })
                ->update([
                    'effective_until' => now()->subDay()->toDateString(),
                    'updated_at' => now(),
                ]);

            return DailyTarget::create([
                'sankalp_id' => $sankalp->id,
                'target_count' => $targetCount,
                'effective_from' => $today,
                'effective_until' => null,
            ]);
        });
    }

    public function getTodaySummary(
        int $userId,
        int $sankalpId
    ): array {
        $sankalp = $this->getOwnedSankalp($userId, $sankalpId);

        $today = now()->toDateString();

        $target = DailyTarget::query()
            ->where('sankalp_id', $sankalp->id)
            ->whereDate('effective_from', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('effective_until')
                    ->orWhereDate('effective_until', '>=', $today);
            })
            ->orderByDesc('effective_from')
            ->first();

        $completedToday = (int) SankalpProgressLog::query()
            ->where('sankalp_id', $sankalp->id)
            ->whereDate('progress_date', $today)
            ->sum('count');

        $dailyTarget = (int) ($target?->target_count ?? 0);

        return [
            'sankalp_id' => $sankalp->id,
            'date' => $today,
            'daily_target' => $target ? [
                'id' => $target->id,
                'target_count' => $dailyTarget,
                'effective_from' => $target->effective_from?->toDateString(),
                'effective_until' => $target->effective_until?->toDateString(),
            ] : null,
            'completed_today' => $completedToday,
            'remaining_today' => $target
                ? max(0, $dailyTarget - $completedToday)
                : null,
            'progress_percentage' => $dailyTarget > 0
                ? round(($completedToday / $dailyTarget) * 100, 2)
                : 0,
            'target_achieved' => $target
                ? $completedToday >= $dailyTarget
                : false,
        ];
    }

    private function getOwnedSankalp(
        int $userId,
        int $sankalpId,
        bool $lock = false
    ): Sankalp {
        $query = Sankalp::query()
            ->where('user_id', $userId);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($sankalpId);
    }


    
public function getSummaryForDate(
    int $userId,
    int $sankalpId,
    string $date
): array {
    $sankalp = $this->getOwnedSankalp($userId, $sankalpId);

    $target = DailyTarget::query()
        ->where('sankalp_id', $sankalp->id)
        ->whereDate('effective_from', '<=', $date)
        ->where(function ($query) use ($date) {
            $query->whereNull('effective_until')
                ->orWhereDate('effective_until', '>=', $date);
        })
        ->orderByDesc('effective_from')
        ->first();

    $completed = (int) SankalpProgressLog::query()
        ->where('sankalp_id', $sankalp->id)
        ->whereDate('progress_date', $date)
        ->sum('count');

    $targetCount = (int) ($target?->target_count ?? 0);

    return [
        'sankalp_id' => $sankalp->id,
        'date' => $date,
        'daily_target' => $target ? [
            'id' => $target->id,
            'target_count' => $targetCount,
            'effective_from' => $target->effective_from?->toDateString(),
            'effective_until' => $target->effective_until?->toDateString(),
        ] : null,
        'completed_today' => $completed,
        'remaining_today' => $target
            ? max(0, $targetCount - $completed)
            : null,
        'progress_percentage' => $targetCount > 0
            ? round(($completed / $targetCount) * 100, 2)
            : 0,
        'target_achieved' => $target
            ? $completed >= $targetCount
            : false,
    ];
}

}
