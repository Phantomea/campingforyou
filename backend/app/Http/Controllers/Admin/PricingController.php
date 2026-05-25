<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index(): JsonResponse
    {
        $items = PricingItem::ordered()->get();

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pricing_category_id' => 'nullable|exists:pricing_categories,id',
            'price_from' => 'nullable|numeric|min:0',
            'price_to' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
        ]);

        $item = PricingItem::create($validated);

        return response()->json($item->load('category'), 201);
    }

    public function show(PricingItem $pricing): JsonResponse
    {
        return response()->json($pricing->load('category'));
    }

    public function update(Request $request, PricingItem $pricing): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pricing_category_id' => 'nullable|exists:pricing_categories,id',
            'price_from' => 'nullable|numeric|min:0',
            'price_to' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
        ]);

        $pricing->update($validated);

        return response()->json($pricing->load('category'));
    }

    public function destroy(PricingItem $pricing): JsonResponse
    {
        $pricing->delete();

        return response()->json(['message' => 'Položka cenníka bola vymazaná']);
    }
}
