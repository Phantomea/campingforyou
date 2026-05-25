<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'price',
        'image',
        'widget_code',
        'is_active',
        'sort_order',
        'manufacturer',
        'capacity',
        'year',
        'license_plate',
        'price_per_day',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_per_day' => 'decimal:2',
        'is_active' => 'boolean',
        'capacity' => 'integer',
        'year' => 'integer',
        'sort_order' => 'integer',
    ];

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
