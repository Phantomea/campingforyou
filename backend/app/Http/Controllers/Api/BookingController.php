<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmedMail;
use App\Mail\BookingNewGarageMail;
use App\Mail\BookingReceivedMail;
use App\Models\AddonService;
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

        // Vypočítaj min_date podľa cutoff času
        $minDate = now()->toDateString();
        $cutoff  = Setting::get('booking_cutoff_time');
        if ($cutoff) {
            [$h, $m] = explode(':', $cutoff);
            $cutoffTime = now()->setHour((int)$h)->setMinute((int)$m)->setSecond(0);
            if (now()->gte($cutoffTime)) {
                $minDate = now()->addDay()->toDateString();
            }
        }

        return response()->json([
            'blocked_days' => array_values(array_unique($blocked)),
            'min_date'     => $minDate,
        ]);
    }

    /** Vypočíta cenu prenájmu podľa sezónnych cien deň po dni. */
    private function calcTotalPrice(Service $service, Carbon $from, Carbon $to): ?float
    {
        $high = $service->price_high_season !== null ? (float) $service->price_high_season : null;
        $low  = $service->price_low_season  !== null ? (float) $service->price_low_season  : null;

        if ($high === null && $low === null) {
            return null;
        }

        $total = 0.0;
        $day   = $from->copy()->startOfDay();
        $end   = $to->copy()->startOfDay();

        while ($day->lt($end)) {
            $total += Service::isHighSeason($day) ? ($high ?? $low) : ($low ?? $high);
            $day->addDay();
        }

        return round($total, 2);
    }

    /** Vypočíta deadline platby: min(createdAt + N dní, deň prevzatia) */
    private function calcPaymentDeadline(Carbon $dateFrom, ?Carbon $createdAt = null): Carbon
    {
        $base     = $createdAt ?? now();
        $days     = (int) (Setting::get('payment_deadline_days') ?? 5);
        $byDays   = $base->copy()->addDays($days)->startOfDay();
        $byPickup = $dateFrom->copy()->startOfDay();

        return $byDays->lt($byPickup) ? $byDays : $byPickup;
    }

    /**
     * Celý platobný plán: záloha + doplatka + deadliny.
     *
     * Rozdelenie platby nastane iba ak daysUntilPickup > full_payment_deadline_days:
     *   - deposit deadline = min(base + payment_deadline_days, dateFrom)
     *   - full deadline    = base + full_payment_deadline_days
     *
     * Jednorázová platba (pickup príliš blízko):
     *   - deadline = max(today, dateFrom - full_payment_deadline_days)
     */
    private function buildPaymentSchedule(Carbon $dateFrom, float $totalPrice, ?Carbon $createdAt = null): array
    {
        $base           = ($createdAt ?? now())->copy()->startOfDay();
        $today          = now()->startOfDay();
        $upfrontPercent = (int) (Setting::get('upfront_payment_percent') ?: 100);
        $fullDays       = (int) (Setting::get('full_payment_deadline_days') ?: 0);
        $daysUntilPickup = (int) $base->diffInDays($dateFrom->copy()->startOfDay(), false);

        $splitPayment = $upfrontPercent < 100
            && $fullDays > 0
            && $daysUntilPickup > $fullDays;

        if (!$splitPayment) {
            if ($fullDays > 0) {
                $singleDeadline = $dateFrom->copy()->startOfDay()->subDays($fullDays);
                if ($singleDeadline->lt($today)) {
                    $singleDeadline = $today->copy();
                }
            } else {
                $singleDeadline = $this->calcPaymentDeadline($dateFrom, $createdAt);
            }

            return [
                'upfront_amount'        => $totalPrice,
                'remaining_amount'      => 0.0,
                'payment_deadline'      => $singleDeadline->toDateString(),
                'full_payment_deadline' => null,
                'single_payment'        => true,
            ];
        }

        $depositDeadline = $this->calcPaymentDeadline($dateFrom, $createdAt);
        $fullDeadline    = $base->copy()->addDays($fullDays);
        $upfrontAmount   = round($totalPrice * $upfrontPercent / 100, 2);

        return [
            'upfront_amount'        => $upfrontAmount,
            'remaining_amount'      => round($totalPrice - $upfrontAmount, 2),
            'payment_deadline'      => $depositDeadline->toDateString(),
            'full_payment_deadline' => $fullDeadline->toDateString(),
            'single_payment'        => false,
        ];
    }

    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date_from'  => 'required|date_format:Y-m-d',
            'date_to'    => 'required|date_format:Y-m-d|after:date_from',
        ]);

        $service    = Service::findOrFail($request->service_id);
        $from       = Carbon::parse($request->date_from);
        $to         = Carbon::parse($request->date_to);
        $totalDays  = (int) $from->diffInDays($to);
        $totalPrice = $this->calcTotalPrice($service, $from, $to);

        $schedule = $this->buildPaymentSchedule($from, (float) ($totalPrice ?? 0));

        return response()->json(array_merge([
            'total_days'         => $totalDays,
            'total_price'        => $totalPrice,
            'deposit'            => $service->deposit,
            'latest_pickup_time' => Setting::latestPickupTime($from),
        ], $schedule));
    }

    public function status(string $token): JsonResponse
    {
        $booking = Booking::where('token', $token)
            ->with('service:id,title,slug,manufacturer,year,images,image')
            ->firstOrFail();

        $totalPrice = (float) ($booking->total_price ?? 0);
        $createdAt  = Carbon::parse($booking->created_at);

        $schedule = $this->buildPaymentSchedule(
            Carbon::parse($booking->date_from),
            $totalPrice,
            $createdAt
        );

        // Honour the upfront_amount snapshot (agreed at booking time) when split
        if ($booking->upfront_amount !== null && !$schedule['single_payment']) {
            $snap = (float) $booking->upfront_amount;
            $schedule['upfront_amount']   = $snap;
            $schedule['remaining_amount'] = round($totalPrice - $snap, 2);
        }

        return response()->json(array_merge([
            'id'                  => $booking->id,
            'token'               => $booking->token,
            'status'              => $booking->status,
            'date_from'           => $booking->date_from->toDateString(),
            'date_to'             => $booking->date_to->toDateString(),
            'date_from_formatted' => $booking->date_from_formatted,
            'date_to_formatted'   => $booking->date_to_formatted,
            'total_days'          => $booking->total_days,
            'total_price'         => $booking->total_price,
            'customer_name'    => $booking->customer_name,
            'customer_phone'   => $booking->customer_phone,
            'customer_email'   => $booking->customer_email,
            'note'             => $booking->note,
            'billing_company'  => $booking->billing_company,
            'billing_ico'      => $booking->billing_ico,
            'billing_dic'      => $booking->billing_dic,
            'billing_street'   => $booking->billing_street,
            'billing_city'     => $booking->billing_city,
            'billing_zip'      => $booking->billing_zip,
            'service'          => $booking->service,
            'created_at'       => $booking->created_at,
            'latest_pickup_time' => Setting::latestPickupTime($booking->date_from),
        ], $schedule));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id'       => 'required|exists:services,id',
            'date_from'        => 'required|date_format:Y-m-d|after_or_equal:today',
            'date_to'          => 'required|date_format:Y-m-d|after:date_from',
            'customer_name'    => 'required|string|max:100',
            'customer_phone'   => 'required|string|max:30',
            'customer_email'   => 'required|email|max:150',
            'note'             => 'nullable|string|max:500',
            'billing_company'  => 'nullable|string|max:200',
            'billing_ico'      => 'nullable|string|max:20',
            'billing_dic'      => 'nullable|string|max:30',
            'billing_street'   => 'nullable|string|max:200',
            'billing_city'     => 'nullable|string|max:100',
            'billing_zip'      => 'nullable|string|max:10',
            'addon_service_ids' => 'nullable|array',
            'addon_service_ids.*' => 'integer|exists:addon_services,id',
        ]);

        $caravan = Service::findOrFail($validated['service_id']);

        // Skontrolovať cutoff čas pre dnešný deň
        $cutoff = Setting::get('booking_cutoff_time');
        if ($cutoff && $validated['date_from'] === now()->toDateString()) {
            [$h, $m] = explode(':', $cutoff);
            $cutoffTime = now()->setHour((int)$h)->setMinute((int)$m)->setSecond(0);
            if (now()->gt($cutoffTime)) {
                return response()->json([
                    'message' => "Rezervácie na dnešný deň nie sú možné po {$cutoff}.",
                ], 422);
            }
        }

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

        // Načítať a validovať addon služby (premium exkluzivita)
        $addonServiceIds = $validated['addon_service_ids'] ?? [];
        $addonServices = collect();
        if (!empty($addonServiceIds)) {
            $addonServices = AddonService::active()->whereIn('id', $addonServiceIds)->get();
            $hasPremium = $addonServices->where('is_premium', true)->count();
            if ($hasPremium && $addonServices->count() > 1) {
                return response()->json(['message' => 'Prémiové vrátenie nemožno kombinovať s inými službami.'], 422);
            }
        }

        // Vypočítať počet dní a celkovú cenu
        $from      = Carbon::parse($validated['date_from']);
        $to        = Carbon::parse($validated['date_to']);
        $totalDays = $from->diffInDays($to);

        $rentalPrice = $this->calcTotalPrice($caravan, $from, $to) ?? 0;
        $addonsPrice = (float) $addonServices->sum('price');
        $totalAmount = $rentalPrice + $addonsPrice;

        $totalAmount = $totalAmount > 0 ? round($totalAmount, 2) : 0.0;

        // Vypočítať platobný plán a uložiť zálohu ako snapshot
        $schedule = $this->buildPaymentSchedule($from, $totalAmount);

        $bookingData = collect($validated)->except('addon_service_ids')->merge([
            'total_days'     => $totalDays,
            'total_price'    => $totalAmount > 0 ? $totalAmount : null,
            'upfront_amount' => $totalAmount > 0 ? $schedule['upfront_amount'] : null,
            'status'         => 'pending',
        ])->toArray();

        $booking = Booking::create($bookingData);

        // Pripojiť addon služby s cenou ako snapshot
        if ($addonServices->isNotEmpty()) {
            $syncData = $addonServices->mapWithKeys(fn($s) => [$s->id => ['price' => $s->price]])->toArray();
            $booking->addonServices()->sync($syncData);
        }

        $booking->load('service:id,title,slug,images,image', 'addonServices');

        $garageEmail = Setting::get('email', config('mail.from.address'));
        Mail::to($garageEmail)->queue(new BookingNewGarageMail($booking, autoConfirmed: false));
        Mail::to($booking->customer_email)->queue(new BookingReceivedMail($booking, $schedule));

        return response()->json(array_merge($booking->toArray(), $schedule), 201);
    }
}
