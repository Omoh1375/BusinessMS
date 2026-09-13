<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Branch::class);

        $business = app('currentBusiness');

        $branches = $business->branches()
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Branches retrieved successfully.',
            'data' => [
                'branches' => $branches,
            ],
        ]);
    }

    public function store(StoreBranchRequest $request): JsonResponse
    {
        Gate::authorize('create', Branch::class);

        $business = app('currentBusiness');

        $data = $request->validated();

        if (($data['is_main'] ?? false) === true) {
            $business->branches()
                ->where('is_main', true)
                ->update([
                    'is_main' => false,
                ]);
        }

        $branch = $business->branches()->create([
            ...$data,
            'country' => $data['country'] ?? $business->country ?? 'Nigeria',
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Branch created successfully.',
            'data' => [
                'branch' => $branch,
            ],
        ], 201);
    }

    public function show(Branch $branch): JsonResponse
    {
        Gate::authorize('view', $branch);

        return response()->json([
            'message' => 'Branch retrieved successfully.',
            'data' => [
                'branch' => $branch,
            ],
        ]);
    }

    public function update(
        UpdateBranchRequest $request,
        Branch $branch
    ): JsonResponse {
        Gate::authorize('update', $branch);

        $business = app('currentBusiness');

        $data = $request->validated();

        if (($data['is_main'] ?? false) === true) {
            $business->branches()
                ->where('id', '!=', $branch->id)
                ->where('is_main', true)
                ->update([
                    'is_main' => false,
                ]);
        }

        $branch->update($data);

        return response()->json([
            'message' => 'Branch updated successfully.',
            'data' => [
                'branch' => $branch->fresh(),
            ],
        ]);
    }

    public function destroy(Branch $branch): JsonResponse
    {
        Gate::authorize('delete', $branch);

        if ($branch->is_main) {
            return response()->json([
                'message' => 'The main branch cannot be deleted. Set another branch as main first.',
            ], 422);
        }

        $branch->delete();

        return response()->json([
            'message' => 'Branch deleted successfully.',
        ]);
    }
}