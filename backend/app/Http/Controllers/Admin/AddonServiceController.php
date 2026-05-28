<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddonServiceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(AddonService::ordered()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'price'       => 'required|numeric|min:0',
            'is_premium'  => 'boolean',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer',
        ]);

        $service = AddonService::create($validated);
        return response()->json($service, 201);
    }

    public function update(Request $request, AddonService $addonService): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'price'       => 'required|numeric|min:0',
            'is_premium'  => 'boolean',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer',
        ]);

        $addonService->update($validated);
        return response()->json($addonService);
    }

    public function destroy(AddonService $addonService): JsonResponse
    {
        $addonService->delete();
        return response()->json(null, 204);
    }
}
