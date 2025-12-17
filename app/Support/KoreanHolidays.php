<?php

namespace App\Support;

use Yasumi\Yasumi;

class KoreanHolidays
{
    /**
     * Returns holiday date strings (Y-m-d) for given years in South Korea.
     *
     * Includes lunar holidays and substitute holidays supported by Yasumi provider.
     *
     * @param int[] $years
     * @return string[]
     */
    public static function dates(array $years): array
    {
        $dates = [];
        $years = array_values(array_unique(array_map('intval', $years)));

        foreach ($years as $year) {
            $provider = Yasumi::create('SouthKorea', $year, 'ko_KR');
            foreach ($provider as $holiday) {
                $dates[] = $holiday->format('Y-m-d');
            }
        }

        $dates = array_values(array_unique($dates));
        sort($dates);

        return $dates;
    }
}


