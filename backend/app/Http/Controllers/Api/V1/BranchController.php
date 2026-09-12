<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
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

    public function store(Request $request): JsonResponse
    {
        $business = app('currentBusiness');

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('branches', 'code')
                    ->where('business_id', $business->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'country' => [
                'nullable',
                'string',
                'max:100',
            ],

            'is_main' => [
                'sometimes',
                'boolean',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        if (($data['is_main'] ?? false) === true) {
            $business->branches()
                ->where('is_main', true)
                ->update([
                    'is_main' => false,
                ]);
        }

        $branch = $business->branches()->create([
            ...$data,
            'country' => $data['country'] ?? $business->country,
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
        $this->ensureBranchBelongsToCurrentBusiness($branch);

        return response()->json([
            'message' => 'Branch retrieved successfully.',
            'data' => [
                'branch' => $branch,
            ],
        ]);
    }

    public function update(Request $request, Branch $branch): JsonResponse
    {
        $this->ensureBranchBelongsToCurrentBusiness($branch);

        $business = app('currentBusiness');

        $validator = Validator::make($request->all(), [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('branches', 'code')
                    ->where('business_id', $business->id)
                    ->ignore($branch->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'country' => [
                'nullable',
                'string',
                'max:100',
            ],

            'is_main' => [
                'sometimes',
                'boolean',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

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
        $this->ensureBranchBelongsToCurrentBusiness($branch);

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

    private function ensureBranchBelongsToCurrentBusiness(Branch $branch): void
    {
        $business = app('currentBusiness');

        abort_unless(
            $branch->business_id === $business->id,
            404
        );
    }
}