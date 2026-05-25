<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmedMail;
use App\Mail\BookingNewGarageMail;
use App\Models\Booking;
use App\Models\BookingBlock;
use App\Models\Service;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    /** Skontroluje, či je dátumový rozsah blokovaný pre daný karavan */
    private function isRangeBlocked(int $serviceId, string $dateFrom, string $dateTo): bool
    {
        $blocks = BookingBlock::where(fn($q) => $q->whereNull('service_id')->orWhere('service_id', $serviceId))
            ->get();

        $from = Carbon::parse($dateFrom);
        $to   = Carbon::parse($dateTo);

        foreach ($blocks as $block) {
            $blockFrom = Carbon::parse($block->block_date_from);
            $blockTo   = $block->block_date_to ? Carbon::parse($block->block_date_to) : $blockFrom;
            // Overlap check: requested range overlaps block range
            if ($from->lte($blockTo) && $to->gte($blockFrom)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vráti dostupnosť karavanu pre zadaný dátumový rozsah.
     * GET /api/bookings/availability?caravan_id=1&date_from=2026-06-01&date_to=2026-06-07
     */
    public function availability(Request $request): JsonResponse
    {
        $request->validate([
            'caravan_id' => 'required|exists:services,id',
            'date_from'  => 'required|date_format:Y-m-d|after_or_equal:today',
            'date_to'    => 'required|date_format:Y-m-d|after:date_from',
        ]);

        $caravanId = $request->caravan_id;
        $dateFrom  = $request->date_from;
        $dateTo    = $request->date_to;

        if ($this->isRangeBlocked($caravanId, $dateFrom, $dateTo)) {
            return response()->json(['available' => false, 'reason' => 'blocked']);
        }

        $conflict = Booking::where('service_id', $caravanId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('date_from', '<=', $dateTo)
            ->where('date_to', '>=', $dateFrom)
            ->exists();

        if ($conflict) {
            return response()->json(['available' => false, 'reason' => 'booked']);
        }

        return response()->json(['available' => true]);
    }

    /**
     * Vráti blokované dni pre daný mesiac — pre kalendár.
     * GET /api/bookings/blocked-days?caravan_id=1&year=2026&month=6
     */
    public function blockedDays(Request $request): JsonResponse
    {
        $request->validate([
            'caravan_id' => 'required|exists:services,id',
            'year'       => 'required|integer',
            'month'      => 'required|integer|min:1|max:12',
        ]);

        $caravanId = $request->caravan_id;
        $year      = (int) $request->year;
        $month     = (int) $request->month;

        $firstDay = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $lastDay  = $firstDay->copy()->endOfMonth();

        $blocked = [];

        // Collect admin-blocked dates
        $blocks = BookingBlock::where(fn($q) => $q->whereNull('service_id')->orWhere('service_id', $caravanId))
            ->where('block_date_from', '<=', $lastDay->toDateString())
            ->where(fn($q) => $q->whereNull('block_date_to')->orWhere('block_date_to', '>=', $firstDay->toDateString()))
            ->get();

        // Collect booked dates
        $bookings = Booking::where('service_id', $caravanId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('date_from', '<=', $lastDay->toDateString())
            ->where('date_to', '>=', $firstDay->toDateString())
            ->get();

        $day = $firstDay->copy();
        while ($day->lte($lastDay)) {
            $dateStr = $day->toDateString();

            // Check blocks
            foreach ($blocks as $block) {
                $blockFrom = Carbon::parse($block->block_date_from);
                $blockTo   = $block->block_date_to ? Carbon::parse($block->block_date_to) : $blockFrom;
                if ($day->gte($blockFrom) && $day->lte($blockTo)) {
                    $blocked[] = $dateStr;
                    break;
                }
            }

            if (!in_array($dateStr, $blocked)) {
                foreach ($bookings as $booking) {
                    $bookFrom = Carbon::parse($booking->date_from);
                    $bookTo   = Carbon::parse($booking->date_to);
                    if ($day->gte($bookFrom) && $day->lte($bookTo)) {
                        $blocked[] = $dateStr;
                        break;
                    }
                }
            }

            $day->addDay();
        }

        return response()->json(['blocked_days' => array_values(array_unique($blocked))]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id'     => 'required|exists:services,id',
            'date_from'      => 'required|date_format:Y-m-d|after_or_equal:today',
            'date_to'        => 'required|date_format:Y-m-d|after:date_from',
            'customer_name'  => 'required|string|max:100',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'required|email|max:150',
            'note'           => 'nullable|string|max:500',
        ]);

        $caravan = Service::findOrFail($validated['service_id']);

        // Skontrolovať bloky
        if ($this->isRangeBlocked($caravan->id, $validated['date_from'], $validated['date_to'])) {
            return response()->json(['message' => 'Zvolený termín je zablokovaný.'], 422);
        }

        // Skontrolovať konflikty s existujúcimi rezerváciami
        $conflict = Booking::where('service_id', $caravan->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('date_from', '<=', $validated['date_to'])
            ->where('date_to', '>=', $validated['date_from'])
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'Karavan je v zvolenom termíne už rezervovaný.'], 422);
        }

        // Vypočítať počet dní a celkovú cenu
        $from      = Carbon::parse($validated['date_from']);
        $to        = Carbon::parse($validated['date_to']);
        $totalDays = $from->diffInDays($to);

        $validated['total_days']  = $totalDays;
        $validated['total_price'] = $caravan->price_per_day
            ? round($caravan->price_per_day * $totalDays, 2)
            : null;
        $validated['status'] = 'pending';

        $booking = Booking::create($validated);
        $booking->load('service:id,title');

        $garageEmail = Setting::get('email', config('mail.from.address'));
        Mail::to($garageEmail)->queue(new BookingNewGarageMail($booking, autoConfirmed: false));

        return response()->json($booking, 201);
    }
}
