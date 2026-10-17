<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SankalpDailyProgressCollection;
use App\Services\DailyTargetService;
use App\Services\SankalpProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SankalpProgressController extends Controller
{
    public function __construct(
        private readonly SankalpProgressService $progressService,
        private readonly DailyTargetService $dailyTargetService
    ) {
    }

    /**
     * Get progress records for the authenticated user's Sankalps.
     */
    public function index(Request $request): JsonResponse
    {
        $progressLogs = $this->progressService->getProgressRecords(
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Sankalp progress list fetched successfully.',
            'data' => (new SankalpDailyProgressCollection(
                $progressLogs
            ))->toArray($request),
        ]);
    }

    /**
     * Create or increment progress using sankalp_id and progress_date.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sankalp_id' => ['required', 'integer', 'min:1'],
            'progress_date' => ['required', 'date_format:Y-m-d'],
            'count' => ['required', 'integer', 'min:1'],
        ]);

        $userId = (int) $request->user()->id;
        $sankalpId = (int) $validated['sankalp_id'];
        $progressDate = $validated['progress_date'];

        $this->progressService->addProgress(
            $userId,
            $sankalpId,
            (int) $validated['count'],
            $progressDate
        );

        $progressLogs = $this->progressService->getDailyProgress(
            $userId,
            $sankalpId
        );

        $dailyTargetSummary = $this->dailyTargetService->getSummaryForDate(
            $userId,
            $sankalpId,
            $progressDate
        );

        return response()->json([
            'success' => true,
            'message' => 'Sankalp progress added successfully.',
            'data' => (new SankalpDailyProgressCollection(
                $progressLogs
            ))->toArray($request),
            'daily_target_summary' => $dailyTargetSummary,
        ], 201);
    }

    /**
     * Add progress to a specific Sankalp.
     */
    public function addProgress(
        Request $request,
        int $sankalp
    ): JsonResponse {
        $validated = $request->validate([
            'count' => ['required', 'integer', 'min:1'],
            'progress_date' => ['sometimes', 'date_format:Y-m-d'],
        ]);

        $userId = (int) $request->user()->id;
        $progressDate = $validated['progress_date']
            ?? now()->toDateString();

        $this->progressService->addProgress(
            $userId,
            $sankalp,
            (int) $validated['count'],
            $progressDate
        );

        $progressLogs = $this->progressService->getDailyProgress(
            $userId,
            $sankalp
        );

        $dailyTargetSummary = $this->dailyTargetService->getSummaryForDate(
            $userId,
            $sankalp,
            $progressDate
        );

        return response()->json([
            'success' => true,
            'message' => 'Sankalp progress added successfully.',
            'data' => (new SankalpDailyProgressCollection(
                $progressLogs
            ))->toArray($request),
            'daily_target_summary' => $dailyTargetSummary,
        ]);
    }

    /**
     * Get daily progress history for a specific Sankalp.
     */
    public function dailyProgress(
        Request $request,
        int $sankalp
    ): JsonResponse {
        $userId = (int) $request->user()->id;

        $progressLogs = $this->progressService->getDailyProgress(
            $userId,
            $sankalp
        );

        return response()->json([
            'success' => true,
            'message' => 'Daily Sankalp progress fetched successfully.',
            'data' => (new SankalpDailyProgressCollection(
                $progressLogs
            ))->toArray($request),
        ]);
    }
}
