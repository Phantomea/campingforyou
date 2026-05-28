<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddonService;
use Illuminate\Http\JsonResponse;

class AddonServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $services = AddonService::active()->ordered()->get();
        return response()->json($services);
    }
}
