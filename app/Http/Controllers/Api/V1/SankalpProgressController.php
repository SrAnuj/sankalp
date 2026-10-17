<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSankalpProgressRequest;
use App\Http\Requests\StoreSankalpRequest;
use App\Http\Resources\SankalpResource;
use App\Models\Sankalp;
use App\Services\SankalpService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SankalpProgressController extends Controller
{
    public function __construct(
        private readonly SankalpService $sankalpService
    ) {
    }

    public function index(Request $request)
    {
        $perPage = min(
            max((int) $request->query('per_page', 15), 1),
            100
        );

        return SankalpResource::collection(
            $this->sankalpService->getUserSankalps(
                (int) $request->user()->id,
                $perPage
            )
        );
    }

    public function store(StoreSankalpRequest $request): JsonResponse
    {
        $sankalp = $this->sankalpService->createForUser(
            (int) $request->user()->id,
            $request->validated()
        );

        return (new SankalpResource($sankalp->load('naam')))
            ->response()
            ->setStatusCode(201);
    }

    public function addProgress(
        StoreSankalpProgressRequest $request,
        int $sankalp
    ): SankalpResource {
        $updated = $this->sankalpService->addProgress(
            (int) $request->user()->id,
            $sankalp,
            (int) $request->validated('count')
        );

        return new SankalpResource($updated);
    }

    public function dailyProgress(Request $request, int $sankalp): JsonResponse
    {
        // Ownership check prevents users from viewing another user's logs.
        $record = Sankalp::query()
            ->where('user_id', $request->user()->id)
            ->with('progressLogs')
            ->findOrFail($sankalp);

        return response()->json([
            'sankalp_id' => $record->id,
            'daily_progress' => $record->progressLogs
                ->sortByDesc('progress_date')
                ->values()
                ->map(fn ($log) => [
                    'date' => $log->progress_date->toDateString(),
                    'count' => $log->count,
                ]),
        ]);
    }
}
