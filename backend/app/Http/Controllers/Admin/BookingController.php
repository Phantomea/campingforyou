<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with('service:id,title')
            ->orderBy('date_from')
            ->orderBy('date_to');

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('date_from', '<=', $request->date)
                  ->whereDate('date_to', '>=', $request->date);
        }

        $bookings = $query->get();

        return response()->json($bookings);
    }

    public function show(Booking $booking): JsonResponse
    {
        $booking->load('service:id,title');
        return response()->json($booking);
    }

    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $oldStatus = $booking->status;
        $booking->update(['status' => $request->status]);

        if ($request->status === 'confirmed' && $oldStatus !== 'confirmed') {
            $booking->load('service:id,title');
            Mail::to($booking->customer_email)->queue(new BookingConfirmedMail($booking));
        }

        if ($request->status === 'cancelled' && $oldStatus !== 'cancelled') {
            $booking->load('service:id,title');
            Mail::to($booking->customer_email)->queue(new BookingCancelledMail($booking));
        }

        return response()->json($booking->load('service:id,title'));
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();
        return response()->json(['message' => 'Rezervácia bola vymazaná.']);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'pending'   => Booking::where('status', 'pending')->where('date_from', '>=', today())->count(),
            'confirmed' => Booking::where('status', 'confirmed')->where('date_from', '>=', today())->count(),
            'today'     => Booking::whereDate('date_from', '<=', today())
                                  ->whereDate('date_to', '>=', today())
                                  ->whereIn('status', ['pending', 'confirmed'])
                                  ->count(),
        ]);
    }
}
