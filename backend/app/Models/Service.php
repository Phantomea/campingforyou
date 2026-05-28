<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Setting;

class Service extends Model
{
    protected $fillable = [
        'title', 'slug', 'description',
        'price_low_season', 'price_high_season',
        'image', 'images',
        'widget_code', 'is_active', 'sort_order',
        'manufacturer', 'capacity', 'sleeping_capacity',
        'year', 'license_plate',
        // Technické parametre
        'length_cm', 'width_cm', 'height_cm',
        'weight_kg', 'max_weight_kg', 'tow_ball_size',
        // Vybavenie
        'has_aircon', 'has_roof_aircon', 'has_solar', 'has_shower', 'has_toilet',
        'has_kitchen', 'has_heating', 'has_awning', 'has_bike_rack',
        'has_gas_alarm', 'has_smoke_alarm', 'has_co_alarm', 'has_spare_wheel',
        'has_outdoor_shower', 'has_reversing_camera',
        // Motor / pohon
        'engine', 'transmission', 'fuel_consumption', 'fuel_tank_l',
        'max_towing_kg', 'driving_seats', 'license_category',
        // Kapacity
        'fresh_water_l', 'waste_water_l', 'battery_ah', 'battery_type',
        'fridge_l', 'awning_m',
        // Podmienky
        'min_rental_days', 'deposit', 'internal_note',
    ];

    protected $casts = [
        'price_low_season' => 'decimal:2',
        'price_high_season'=> 'decimal:2',
        'deposit'          => 'decimal:2',
        'images'           => 'array',
        'is_active'        => 'boolean',
        'capacity'         => 'integer',
        'sleeping_capacity'=> 'integer',
        'year'             => 'integer',
        'sort_order'       => 'integer',
        'min_rental_days'  => 'integer',
        'length_cm'        => 'integer',
        'width_cm'         => 'integer',
        'height_cm'        => 'integer',
        'weight_kg'        => 'integer',
        'max_weight_kg'    => 'integer',
        'has_aircon'       => 'boolean',
        'has_roof_aircon'  => 'boolean',
        'has_solar'        => 'boolean',
        'has_shower'       => 'boolean',
        'has_toilet'       => 'boolean',
        'has_kitchen'      => 'boolean',
        'has_heating'      => 'boolean',
        'has_awning'       => 'boolean',
        'has_bike_rack'    => 'boolean',
        'has_gas_alarm'    => 'boolean',
        'has_smoke_alarm'  => 'boolean',
        'has_co_alarm'          => 'boolean',
        'has_spare_wheel'       => 'boolean',
        'has_outdoor_shower'    => 'boolean',
        'has_reversing_camera'  => 'boolean',
        'fuel_tank_l'           => 'integer',
        'max_towing_kg'         => 'integer',
        'driving_seats'         => 'integer',
        'fresh_water_l'         => 'integer',
        'waste_water_l'         => 'integer',
        'battery_ah'            => 'integer',
        'fridge_l'              => 'integer',
        'awning_m'              => 'decimal:1',
    ];

    protected $appends = ['price', 'price_formatted', 'deposit_formatted'];

    private static function formatCzk(mixed $value): ?string
    {
        if ($value === null) return null;
        return number_format((float) $value, 0, ',', "\xc2\xa0");
    }

    /** Aktuálna cena podľa sezóny (high/low) platná pre dnešný dátum. */
    protected function price(): Attribute
    {
        return Attribute::get(function () {
            $high = isset($this->attributes['price_high_season']) ? (float) $this->attributes['price_high_season'] : null;
            $low  = isset($this->attributes['price_low_season'])  ? (float) $this->attributes['price_low_season']  : null;

            if ($high === null && $low === null) return null;

            return self::isHighSeason(Carbon::today()) ? ($high ?? $low) : ($low ?? $high);
        });
    }

    protected function priceFormatted(): Attribute
    {
        return Attribute::get(fn () => self::formatCzk($this->price));
    }

    protected function depositFormatted(): Attribute
    {
        return Attribute::get(fn () => self::formatCzk($this->attributes['deposit'] ?? null));
    }

    /**
     * Vráti true ak daný deň patrí do vysokej sezóny.
     * Sezóna sa číta zo settings; default 1. máj – 1. september.
     */
    public static function isHighSeason(Carbon $day): bool
    {
        $startMonth = (int) (Setting::get('season_start_month') ?: 5);
        $startDay   = (int) (Setting::get('season_start_day')   ?: 1);
        $endMonth   = (int) (Setting::get('season_end_month')   ?: 9);
        $endDay     = (int) (Setting::get('season_end_day')     ?: 1);

        $m = $day->month;
        $d = $day->day;

        $afterStart = $m > $startMonth || ($m === $startMonth && $d >= $startDay);
        $beforeEnd  = $m < $endMonth   || ($m === $endMonth   && $d <= $endDay);

        return $afterStart && $beforeEnd;
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->title);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }
}
