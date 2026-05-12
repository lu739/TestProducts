<?php

namespace App\Support;

use Carbon\CarbonInterface;

final class ApiDateTime
{
    /**
     * Человекочитаемая дата-время для JSON API (день.месяц.год, часы:минуты:секунды).
     */
    public static function format(?CarbonInterface $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value->copy()
            ->timezone(config('app.timezone'))
            ->format('d.m.Y, H:i:s');
    }
}
