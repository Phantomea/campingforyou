<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function public(): JsonResponse
    {
        $settings = Setting::getPublicSettings();

        // Parse opening_hours from JSON
        if (isset($settings['opening_hours'])) {
            $settings['opening_hours'] = json_decode($settings['opening_hours'], true);
        }

        return response()->json($settings);
    }
}
