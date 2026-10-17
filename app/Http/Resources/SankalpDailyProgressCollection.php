<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SankalpDailyProgressCollection extends ResourceCollection
{
    /**
     * Transform the daily progress logs into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($log) {
            $sankalp = $log->sankalp;

            $target = (int) ($sankalp?->target_count ?? 0);
            $completed = (int) ($sankalp?->completed_count ?? 0);

            return [
                'id' => $log->id,

                'sankalp' => $sankalp ? [
                    'id' => $sankalp->id,

                    'user' => [
                        'id' => $sankalp->user?->id,
                        'name' => $sankalp->user?->name,
                        'phone' => $sankalp->user?->phone,
                    ],

                    'naam' => $sankalp->naam ? [
                        'id' => $sankalp->naam->id,
                        'name' => $sankalp->naam->name,
                        'name_hindi' => $sankalp->naam->name_hindi,
                        'name_punjabi' => $sankalp->naam->name_punjabi,
                        'slug' => $sankalp->naam->slug,
                    ] : null,

                    'target_count' => $target,
                    'completed_count' => $completed,
                    'remaining_count' => max(0, $target - $completed),

                    'progress_percentage' => $target > 0
                        ? round(($completed / $target) * 100, 2)
                        : 0,

                    'status' => $sankalp->status,
                    'started_at' => $sankalp->started_at?->toISOString(),
                    'completed_at' => $sankalp->completed_at?->toISOString(),
                    'created_at' => $sankalp->created_at?->toISOString(),
                ] : null,

                'progress_date' => $log->progress_date->toDateString(),
                'count' => $log->count,
                'created_at' => $log->created_at?->toISOString(),
                'updated_at' => $log->updated_at?->toISOString(),
            ];
        })->values()->all();
    }
}
