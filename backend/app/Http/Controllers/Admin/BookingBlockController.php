<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingBlock;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingBlockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BookingBlock::with('service:id,title')
            ->orderBy('block_date_from');

        if ($request->has('service_id')) {
            if ($request->boolean('strict')) {
                $query->where('service_id', $request->service_id);
            } else {
                $query->where(fn($q) => $q->whereNull('service_id')->orWhere('service_id', $request->service_id));
            }
        }

        if ($request->has('from')) {
            $query->where('block_date_from', '>=', $request->from);
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id'      => 'nullable|exists:services,id',
            'block_date_from' => 'required|date_format:Y-m-d',
            'block_date_to'   => 'nullable|date_format:Y-m-d|after_or_equal:block_date_from',
            'reason'          => 'nullable|string|max:200',
        ]);

        $block = BookingBlock::create($validated);
        $block->load('service:id,title');

        return response()->json($block, 201);
    }

    public function destroy(BookingBlock $bookingBlock): JsonResponse
    {
        $bookingBlock->delete();
        return response()->json(['message' => 'Blok bol odstránený.']);
    }
}
