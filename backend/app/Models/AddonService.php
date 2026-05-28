<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AddonService extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'is_premium',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_addon_service')
            ->withPivot('price');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
