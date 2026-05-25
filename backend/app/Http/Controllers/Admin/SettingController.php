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
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'opening_hours' => 'nullable|array',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $type = is_array($value) ? 'json' : 'string';
                Setting::set($key, $value, $type);
            }
        }

        return response()->json(['message' => 'Nastavenia boli uložené']);
    }
}
