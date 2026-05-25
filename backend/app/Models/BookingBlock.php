<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class BookingBlock extends Model
{
    protected $fillable = [
        'service_id',
        'block_date_from',
        'block_date_to',
        'reason',
    ];

    protected $casts = [
        'block_date_from' => 'date:Y-m-d',
        'block_date_to' => 'date:Y-m-d',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** Check whether a given date (Y-m-d string) falls within this block */
    public function blocksDate(string $date): bool
    {
        $d = Carbon::parse($date);
        $from = Carbon::parse($this->block_date_from);
        $to = $this->block_date_to ? Carbon::parse($this->block_date_to) : $from;
        return $d->gte($from) && $d->lte($to);
    }
}
