<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkDeleteSankalpRequest;
use App\Http\Requests\Admin\SankalpIndexRequest;
use App\Http\Requests\Admin\SankalpStatusRequest;
use App\Http\Requests\Admin\StoreSankalpRequest;
use App\Http\Requests\Admin\UpdateSankalpRequest;
use App\Http\Resources\SankalpListCollection;
use App\Services\SankalpService;
use Illuminate\Http\JsonResponse;

class SankalpController extends Controller
{
    public function __construct(
        private readonly SankalpService $sankalpService
    ) {
    }

    public function index(
        SankalpIndexRequest $request
    ): SankalpListCollection {
        $sankalps = $this->sankalpService->paginateForAdmin(
            $request->validated()
        );

        return (new SankalpListCollection($sankalps))
            ->additional([
                'message' => 'User Sankalps fetched successfully.',
            ]);
    }

    public function show(int $sankalp): SankalpListCollection
    {
        $record = $this->sankalpService->findForAdmin($sankalp);

        return (new SankalpListCollection(
            collect([$record])
        ))->additional([
            'message' => 'Sankalp fetched successfully.',
        ]);
    }

    public function store(
        StoreSankalpRequest $request
    ): JsonResponse {
        $sankalp = $this->sankalpService->create(
            $request->validated()
        );

        return (new SankalpListCollection(
            collect([$sankalp])
        ))->additional([
            'message' => 'Sankalp created successfully.',
        ])->response()->setStatusCode(201);
    }

    public function update(
        UpdateSankalpRequest $request,
        int $sankalp
    ): SankalpListCollection {
        $updatedSankalp = $this->sankalpService->update(
            $sankalp,
            $request->validated()
        );

        return (new SankalpListCollection(
            collect([$updatedSankalp])
        ))->additional([
            'message' => 'Sankalp updated successfully.',
        ]);
    }

    public function updateStatus(
        SankalpStatusRequest $request,
        int $sankalp
    ): SankalpListCollection {
        $updatedSankalp = $this->sankalpService->updateStatus(
            $sankalp,
            $request->validated('status')
        );

        return (new SankalpListCollection(
            collect([$updatedSankalp])
        ))->additional([
            'message' => 'Sankalp status updated successfully.',
        ]);
    }

    public function destroy(int $sankalp): JsonResponse
    {
        $this->sankalpService->delete($sankalp);

        return response()->json([
            'message' => 'Sankalp deleted successfully.',
        ]);
    }

    public function bulkDelete(
        BulkDeleteSankalpRequest $request
    ): JsonResponse {
        $deletedCount = $this->sankalpService->bulkDelete(
            $request->validated('ids')
        );

        return response()->json([
            'message' => 'Selected Sankalps deleted successfully.',
            'deleted_count' => $deletedCount,
        ]);
    }

    public function summary(): JsonResponse
    {
        return response()->json([
            'message' => 'Sankalp summary fetched successfully.',
            'data' => [
                'total_sankalps' => \App\Models\Sankalp::count(),
                'active_sankalps' => \App\Models\Sankalp::where(
                    'status',
                    'active'
                )->count(),
                'completed_sankalps' => \App\Models\Sankalp::where(
                    'status',
                    'completed'
                )->count(),
                'cancelled_sankalps' => \App\Models\Sankalp::where(
                    'status',
                    'cancelled'
                )->count(),
            ],
        ]);
    }
}
