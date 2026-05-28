<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::all()->pluck('value', 'key');

        // Parse JSON settings
        if (isset($settings['opening_hours'])) {
            $settings['opening_hours'] = json_decode($settings['opening_hours'], true);
        }

        return response()->json($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'site_name'               => 'required|string|max:255',
            'site_description'        => 'required|string',
            'phone'                   => 'required|string|max:50',
            'email'                   => 'required|email|max:255',
            'address'                 => 'required|string',
            'opening_hours'           => 'required|array',
            'facebook'                => 'nullable|url|max:255',
            'instagram'               => 'nullable|url|max:255',
            'iban'                    => 'required|string|max:50',
            'bank_name'               => 'required|string|max:100',
            'bank_bic'                => 'required|string|max:20',
            'bank_code'               => 'nullable|string|max:10',
            'account_holder_name'     => 'required|string|max:140',
            'payment_deadline_days'          => 'required|integer|min:1|max:60',
            'full_payment_deadline_days'     => 'nullable|integer|min:0|max:365',
            'booking_cutoff_time'     => 'nullable|string|regex:/^\d{2}:\d{2}$/',
            'pickup_time'             => 'required|string|regex:/^\d{2}:\d{2}$/',
            'return_time'             => 'required|string|regex:/^\d{2}:\d{2}$/',
            'upfront_payment_percent' => 'required|integer|min:0|max:100',
            'season_start_month'      => 'nullable|integer|min:1|max:12',
            'season_start_day'        => 'nullable|integer|min:1|max:31',
            'season_end_month'        => 'nullable|integer|min:1|max:12',
            'season_end_day'          => 'nullable|integer|min:1|max:31',
        ]);

        foreach ($validated as $key => $value) {
            $type = match (true) {
                is_array($value) => 'json',
                is_bool($value)  => 'boolean',
                default          => 'string',
            };
            Setting::set($key, $value ?? '', $type);
        }

        return response()->json(['message' => 'Nastavenia boli uložené']);
    }
}
