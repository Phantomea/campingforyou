<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    public static function set(string $key, $value, string $type = 'string'): void
    {
        $storedValue = match ($type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue, 'type' => $type]
        );
    }

    public static function getPublicSettings(): array
    {
        $publicKeys = [
            'site_name',
            'site_description',
            'phone',
            'email',
            'address',
            'opening_hours',
            'facebook',
            'instagram',
            'iban',
            'bank_name',
            'bank_bic',
            'bank_code',
            'account_holder_name',
            'payment_deadline_days',
            'full_payment_deadline_days',
            'booking_cutoff_time',
            'pickup_time',
            'return_time',
            'upfront_payment_percent',
            'season_start_month',
            'season_start_day',
            'season_end_month',
            'season_end_day',
        ];

        $result = [];
        foreach (static::whereIn('key', $publicKeys)->get() as $s) {
            $result[$s->key] = match ($s->type) {
                'boolean' => filter_var($s->value, FILTER_VALIDATE_BOOLEAN),
                'json'    => json_decode($s->value, true),
                default   => $s->value,
            };
        }
        return $result;
    }

    /**
     * Najneskorší čas vyzdvihnutia pre daný deň = zatváracie hodiny - 1h.
     * Parsuje opening_hours JSON (napr. {"Po - Pi": "9:00 - 17:00", "So": "9:00 - 13:00", "Ne": "Zatvorené"}).
     */
    public static function latestPickupTime(\Carbon\Carbon $date): ?string
    {
        $hours = static::get('opening_hours');
        if (!is_array($hours)) return null;

        // Carbon: 0=Ne, 1=Po, 2=Ut, 3=St, 4=Št, 5=Pi, 6=So
        $dayMap  = ['Ne', 'Po', 'Ut', 'St', 'Št', 'Pi', 'So'];
        $allDays = ['Po', 'Ut', 'St', 'Št', 'Pi', 'So', 'Ne'];
        $dayAbbr = $dayMap[$date->dayOfWeek];

        $rowValue = null;
        foreach ($hours as $key => $value) {
            $parts = array_map('trim', explode('-', $key, 2));
            if (count($parts) === 2) {
                $from = array_search($parts[0], $allDays, true);
                $to   = array_search($parts[1], $allDays, true);
                $cur  = array_search($dayAbbr,  $allDays, true);
                if ($from !== false && $to !== false && $cur !== false && $cur >= $from && $cur <= $to) {
                    $rowValue = $value;
                    break;
                }
            } elseif (trim($key) === $dayAbbr) {
                $rowValue = $value;
                break;
            }
        }

        if (!$rowValue || stripos($rowValue, 'zatvor') !== false) return null;

        // "9:00 - 17:00" → vezmi poslednú časť → "17:00"
        $timeParts = array_map('trim', explode('-', $rowValue));
        $closeStr  = end($timeParts);

        try {
            return \Carbon\Carbon::createFromFormat('G:i', $closeStr)->subHour()->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }
}
