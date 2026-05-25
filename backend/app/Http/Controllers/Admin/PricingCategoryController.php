<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = PricingCategory::withCount('items')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:pricing_categories,name',
            'sort_order' => 'integer',
        ]);

        $category = PricingCategory::create($validated);

        return response()->json($category->loadCount('items'), 201);
    }

    public function update(Request $request, PricingCategory $pricingCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:pricing_categories,name,' . $pricingCategory->id,
            'sort_order' => 'integer',
        ]);

        $pricingCategory->update($validated);

        return response()->json($pricingCategory->loadCount('items'));
    }

    public function destroy(PricingCategory $pricingCategory): JsonResponse
    {
        $pricingCategory->items()->update(['pricing_category_id' => null]);
        $pricingCategory->delete();

        return response()->json(['message' => 'Kategória bola vymazaná']);
    }
}
