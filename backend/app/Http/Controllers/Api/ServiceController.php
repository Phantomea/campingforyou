<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $services = Service::active()->ordered()->get();

        return response()->json($services);
    }

    public function show(Service $service): JsonResponse
    {
        if (!$service->is_active) {
            abort(404);
        }

        return response()->json($service);
    }
}
