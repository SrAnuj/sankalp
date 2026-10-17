<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\DailyTargetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyTargetController extends Controller
{
    public function __construct(
        private readonly DailyTargetService $dailyTargetService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $targets = $this->dailyTargetService->getTargets(
            (int) $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Daily targets fetched successfully.',
            'data' => $targets,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sankalp_id' => ['required', 'integer', 'min:1'],
            'target_count' => ['required', 'integer', 'min:1'],
        ]);

        $target = $this->dailyTargetService->setTarget(
            (int) $request->user()->id,
            (int) $validated['sankalp_id'],
            (int) $validated['target_count']
        );

        return response()->json([
            'success' => true,
            'message' => 'Daily target set successfully.',
            'data' => $target,
        ], 201);
    }

    public function show(Request $request, int $sankalp): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $history = $this->dailyTargetService->getHistory(
            $userId,
            $sankalp
        );

        $today = $this->dailyTargetService->getTodaySummary(
            $userId,
            $sankalp
        );

        return response()->json([
            'success' => true,
            'message' => 'Daily target details fetched successfully.',
            'data' => [
                'history' => $history,
                'today' => $today,
            ],
        ]);
    }

    public function update(Request $request, int $sankalp): JsonResponse
    {
        $validated = $request->validate([
            'target_count' => ['required', 'integer', 'min:1'],
        ]);

        $target = $this->dailyTargetService->setTarget(
            (int) $request->user()->id,
            $sankalp,
            (int) $validated['target_count']
        );

        return response()->json([
            'success' => true,
            'message' => 'Daily target updated successfully.',
            'data' => $target,
        ]);
    }

    public function history(Request $request, int $sankalp): JsonResponse
    {
        $history = $this->dailyTargetService->getHistory(
            (int) $request->user()->id,
            $sankalp
        );

        return response()->json([
            'success' => true,
            'message' => 'Daily target history fetched successfully.',
            'data' => $history,
        ]);
    }

    public function today(Request $request, int $sankalp): JsonResponse
    {
        $summary = $this->dailyTargetService->getTodaySummary(
            (int) $request->user()->id,
            $sankalp
        );

        return response()->json([
            'success' => true,
            'message' => 'Today daily target fetched successfully.',
            'data' => $summary,
        ]);
    }
}
