<?php

namespace App\Support;

use Carbon\Carbon;

class SlovakHolidays
{
    private static array $fixed = [
        '01-01', // Nový rok / Deň vzniku SR
        '01-06', // Zjavenie Pána
        '05-01', // Sviatok práce
        '05-08', // Deň víťazstva nad fašizmom
        '07-05', // Sviatok sv. Cyrila a Metoda
        '08-29', // Výročie SNP
        '09-01', // Deň Ústavy SR
        '09-15', // Sedembolestná Panna Mária
        '11-01', // Sviatok Všetkých svätých
        '11-17', // Deň boja za slobodu a demokraciu
        '12-24', // Štedrý deň
        '12-25', // Prvý sviatok vianočný
        '12-26', // Druhý sviatok vianočný
    ];

    public static function isHoliday(Carbon $date): bool
    {
        if (in_array($date->format('m-d'), self::$fixed, true)) {
            return true;
        }

        $easter = Carbon::createFromTimestamp(easter_date($date->year), 'UTC')->startOfDay();
        if ($date->isSameDay($easter->copy()->subDays(2))) return true; // Veľký piatok
        if ($date->isSameDay($easter->copy()->addDay()))   return true; // Veľkonočný pondelok

        return false;
    }
}
