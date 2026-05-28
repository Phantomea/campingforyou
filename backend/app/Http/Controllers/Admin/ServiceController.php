<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Service::ordered()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());
        $service = Service::create($validated);
        return response()->json($service, 201);
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json($service);
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $validated = $request->validate($this->rules($service->id));
        $service->update($validated);
        return response()->json($service->fresh());
    }

    public function destroy(Service $service): JsonResponse
    {
        // Vymazať aj nahrané fotografie
        foreach ($service->images ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }
        $service->delete();
        return response()->json(['message' => 'Karavan bol vymazaný']);
    }

    /** POST /admin/services/{service}/images — nahratie fotografií */
    public function uploadImages(Request $request, Service $service): JsonResponse
    {
        $request->validate([
            'files'   => 'required|array|max:20',
            'files.*' => 'file|image|max:8192|mimes:jpg,jpeg,png,webp',
        ]);

        $images = $service->images ?? [];

        foreach ($request->file('files') as $file) {
            $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs("services/{$service->id}", $name, 'public');
            $images[] = $path;
        }

        $service->update(['images' => $images]);

        return response()->json(['images' => $images]);
    }

    /** DELETE /admin/services/{service}/images — odstránenie jednej fotografie */
    public function deleteImage(Request $request, Service $service): JsonResponse
    {
        $request->validate(['image' => 'required|string']);

        $images = $service->images ?? [];
        $target = $request->image;

        if (!in_array($target, $images, true)) {
            return response()->json(['message' => 'Fotografia nenájdená'], 404);
        }

        Storage::disk('public')->delete($target);
        $service->update(['images' => array_values(array_filter($images, fn($p) => $p !== $target))]);

        return response()->json(['images' => $service->fresh()->images ?? []]);
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:services,slug' . ($ignoreId ? ",{$ignoreId}" : ''),
            'description'      => 'nullable|string',
            // Ceny
            'price_low_season' => 'nullable|numeric|min:0',
            'price_high_season'=> 'nullable|numeric|min:0',
            // Obrázok (hlavný) a galéria
            'image'            => 'nullable|string',
            // Základné parametre
            'manufacturer'     => 'nullable|string|max:100',
            'capacity'         => 'nullable|integer|min:1|max:20',
            'sleeping_capacity'=> 'nullable|integer|min:1|max:20',
            'year'             => 'nullable|integer|min:1990|max:2100',
            'license_plate'    => 'nullable|string|max:20',
            // Technické parametre
            'length_cm'        => 'nullable|integer|min:1',
            'width_cm'         => 'nullable|integer|min:1',
            'height_cm'        => 'nullable|integer|min:1',
            'weight_kg'        => 'nullable|integer|min:1',
            'max_weight_kg'    => 'nullable|integer|min:1',
            'tow_ball_size'    => 'nullable|string|max:20',
            // Motor / pohon
            'engine'             => 'nullable|string|max:100',
            'transmission'       => 'nullable|string|max:50',
            'fuel_consumption'   => 'nullable|string|max:30',
            'fuel_tank_l'        => 'nullable|integer|min:1',
            'max_towing_kg'      => 'nullable|integer|min:1',
            'driving_seats'      => 'nullable|integer|min:1|max:20',
            'license_category'   => 'nullable|string|max:10',
            // Kapacity
            'fresh_water_l'      => 'nullable|integer|min:1',
            'waste_water_l'      => 'nullable|integer|min:1',
            'battery_ah'         => 'nullable|integer|min:1',
            'battery_type'       => 'nullable|string|max:30',
            'fridge_l'           => 'nullable|integer|min:1',
            'awning_m'           => 'nullable|numeric|min:0',
            // Vybavenie
            'has_aircon'            => 'boolean',
            'has_roof_aircon'       => 'boolean',
            'has_solar'             => 'boolean',
            'has_shower'            => 'boolean',
            'has_outdoor_shower'    => 'boolean',
            'has_toilet'            => 'boolean',
            'has_kitchen'           => 'boolean',
            'has_heating'           => 'boolean',
            'has_awning'            => 'boolean',
            'has_bike_rack'         => 'boolean',
            'has_spare_wheel'       => 'boolean',
            'has_gas_alarm'         => 'boolean',
            'has_smoke_alarm'       => 'boolean',
            'has_co_alarm'          => 'boolean',
            'has_reversing_camera'  => 'boolean',
            // Podmienky
            'min_rental_days'  => 'nullable|integer|min:1|max:365',
            'deposit'          => 'nullable|numeric|min:0',
            'internal_note'    => 'nullable|string|max:2000',
            // Meta
            'widget_code'      => 'nullable|string',
            'is_active'        => 'boolean',
            'sort_order'       => 'integer',
        ];
    }
}
