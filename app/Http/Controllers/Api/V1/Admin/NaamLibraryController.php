<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkDeleteNaamLibraryRequest;
use App\Http\Requests\Admin\NaamLibraryIndexRequest;
use App\Http\Requests\Admin\StoreNaamLibraryRequest;
use App\Http\Requests\Admin\UpdateNaamLibraryRequest;
use App\Http\Requests\Admin\UpdateNaamLibrarySortOrderRequest;
use App\Http\Requests\Admin\UpdateNaamLibraryStatusRequest;
use App\Http\Resources\NaamLibraryCollection;
use App\Services\Admin\NaamLibraryService;
use Illuminate\Http\JsonResponse;

class NaamLibraryController extends Controller
{
    public function __construct(
        private readonly NaamLibraryService $naamLibraryService
    ) {
    }

    public function index(
        NaamLibraryIndexRequest $request
    ): NaamLibraryCollection {
        $naams = $this->naamLibraryService->paginate(
            $request->validated()
        );

        return (new NaamLibraryCollection($naams))
            ->additional([
                'message' => 'Naam Library fetched successfully.',
            ]);
    }

    public function show(int $naam): NaamLibraryCollection
    {
        return (new NaamLibraryCollection(
            collect([$this->naamLibraryService->find($naam)])
        ))->additional([
            'message' => 'Naam fetched successfully.',
        ]);
    }

    public function store(
        StoreNaamLibraryRequest $request
    ): JsonResponse {
        $naam = $this->naamLibraryService->create(
            $request->validated()
        );

        return (new NaamLibraryCollection(collect([$naam])))
            ->additional([
                'message' => 'Naam created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateNaamLibraryRequest $request,
        int $naam
    ): NaamLibraryCollection {
        $updatedNaam = $this->naamLibraryService->update(
            $naam,
            $request->validated()
        );

        return new NaamLibraryCollection(
            collect([$updatedNaam])
        );
    }

    public function updateStatus(
        UpdateNaamLibraryStatusRequest $request,
        int $naam
    ): NaamLibraryCollection {
        $updatedNaam = $this->naamLibraryService->updateStatus(
            $naam,
            $request->validated('status')
        );

        return new NaamLibraryCollection(
            collect([$updatedNaam])
        );
    }

    public function updateSortOrder(
        UpdateNaamLibrarySortOrderRequest $request,
        int $naam
    ): NaamLibraryCollection {
        $updatedNaam = $this->naamLibraryService->updateSortOrder(
            $naam,
            (int) $request->validated('sort_order')
        );

        return new NaamLibraryCollection(
            collect([$updatedNaam])
        );
    }

    public function destroy(int $naam): JsonResponse
    {
        $this->naamLibraryService->delete($naam);

        return response()->json([
            'message' => 'Naam deleted successfully.',
        ]);
    }

    public function bulkDelete(
        BulkDeleteNaamLibraryRequest $request
    ): JsonResponse {
        $deletedCount = $this->naamLibraryService->bulkDelete(
            $request->validated('ids')
        );

        return response()->json([
            'message' => 'Selected Naams deleted successfully.',
            'deleted_count' => $deletedCount,
        ]);
    }
}