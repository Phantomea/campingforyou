<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PricingItem;
use Illuminate\Http\JsonResponse;

class PricingController extends Controller
{
    public function index(): JsonResponse
    {
        $items = PricingItem::ordered()->get();

        $grouped = $items->groupBy(fn($item) => $item->category?->name ?? 'Ostatné');

        return response()->json($grouped);
    }
}
