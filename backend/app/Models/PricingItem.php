<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingItem extends Model
{
    protected $fillable = [
        'name',
        'pricing_category_id',
        'price_from',
        'price_to',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'price_from' => 'decimal:2',
        'price_to' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PricingCategory::class, 'pricing_category_id');
    }

    public function scopeOrdered($query)
    {
        return $query->with('category')
            ->orderBy(
                PricingCategory::select('sort_order')->whereColumn('pricing_categories.id', 'pricing_items.pricing_category_id'),
            )
            ->orderBy(
                PricingCategory::select('name')->whereColumn('pricing_categories.id', 'pricing_items.pricing_category_id'),
            )
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
