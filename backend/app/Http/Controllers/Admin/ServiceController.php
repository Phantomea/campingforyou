<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $services = Service::ordered()->get();

        return response()->json($services);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'slug'          => 'nullable|string|max:255|unique:services',
            'description'   => 'nullable|string',
            'price'         => 'nullable|numeric|min:0',
            'price_per_day' => 'nullable|numeric|min:0',
            'image'         => 'nullable|string',
            'widget_code'   => 'nullable|string',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer',
            'manufacturer'  => 'nullable|string|max:100',
            'capacity'      => 'nullable|integer|min:1|max:20',
            'year'          => 'nullable|integer|min:1990|max:2100',
            'license_plate' => 'nullable|string|max:20',
        ]);

        $service = Service::create($validated);

        return response()->json($service, 201);
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json($service);
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'slug'          => 'nullable|string|max:255|unique:services,slug,' . $service->id,
            'description'   => 'nullable|string',
            'price'         => 'nullable|numeric|min:0',
            'price_per_day' => 'nullable|numeric|min:0',
            'image'         => 'nullable|string',
            'widget_code'   => 'nullable|string',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer',
            'manufacturer'  => 'nullable|string|max:100',
            'capacity'      => 'nullable|integer|min:1|max:20',
            'year'          => 'nullable|integer|min:1990|max:2100',
            'license_plate' => 'nullable|string|max:20',
        ]);

        $service->update($validated);

        return response()->json($service);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json(['message' => 'Karavan bol vymazaný']);
    }
}
