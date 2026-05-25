<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'service_id',
        'date_from',
        'date_to',
        'total_days',
        'total_price',
        'customer_name',
        'customer_phone',
        'customer_email',
        'note',
        'status',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'total_days' => 'integer',
        'total_price' => 'decimal:2',
    ];

    protected $appends = ['date_from_formatted', 'date_to_formatted'];

    public function getDateFromFormattedAttribute(): string
    {
        return $this->date_from->locale('sk')->isoFormat('D. MMMM YYYY');
    }

    public function getDateToFormattedAttribute(): ?string
    {
        return $this->date_to?->locale('sk')->isoFormat('D. MMMM YYYY');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date_from', '>=', today())
                     ->whereIn('status', ['pending', 'confirmed']);
    }
}
