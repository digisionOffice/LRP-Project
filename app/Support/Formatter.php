<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * A helper class for Indonesian-specific data formatting.
 */
class Formatter
{
    /**
     * Formats a number with Indonesian standards (dot for thousands, comma for decimals).
     *
     * Usage:
     * Formatter::number(1234567)      // "1.234.567"
     * Formatter::number(12345.67, 2)  // "12.345,67"
     *
     * @param int|float|null $value The number to format.
     * @param int $decimals The number of decimal places.
     * @return string|null
     */
    public static function number(int|float|null $value, int $decimals = 0): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return number_format($value, $decimals, ',', '.');
    }

    /**
     * Formats a number into Indonesian Rupiah currency format.
     *
     * Usage:
     * Formatter::rupiah(75000) // "Rp 75.000"
     *
     * @param int|float|null $value The amount to format.
     * @return string|null
     */
    public static function rupiah(int|float|null $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return 'Rp ' . self::number($value);
    }

    /**
     * Formats a date into various Indonesian formats.
     * This method leverages Carbon for accurate translation.
     *
     * Supported formats (examples for today, Monday, June 16, 2025):
     * 'd M Y' -> "16 Jun 2025" (3-char month)
     * 'd F Y' -> "16 Juni 2025" (full month name)
     * 'l, d F Y' -> "Senin, 16 Juni 2025" (with day name)
     *
     * @param string|\DateTimeInterface|null $date The date to format.
     * @param string $format The desired output format string.
     * @return string|null
     */
    public static function date(?string $date, string $format = 'd F Y'): ?string
    {
        if (!$date) {
            return null;
        }

        // Set locale to Indonesian and use translatedFormat for month/day names
        return Carbon::parse($date)->locale('id_ID')->translatedFormat($format);
    }

    /**
     * Formats a datetime into various Indonesian formats.
     *
     * Usage:
     * Formatter::dateTime(now(), 'd M Y, H:i') // "16 Jun 2025, 23:39"
     *
     * @param string|\DateTimeInterface|null $dateTime The datetime to format.
     * @param string $format The desired output format string.
     * @return string|null
     */
    public static function dateTime(?string $dateTime, string $format = 'd F Y, H:i'): ?string
    {
        if (!$dateTime) {
            return null;
        }

        return Carbon::parse($dateTime)->locale('id_ID')->translatedFormat($format);
    }
}
