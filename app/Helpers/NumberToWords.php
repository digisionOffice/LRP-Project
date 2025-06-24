<?php

namespace App\Helpers;

class NumberToWords
{
    private static $ones = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
        'sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas',
        'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'
    ];

    private static $tens = [
        '', '', 'dua puluh', 'tiga puluh', 'empat puluh', 'lima puluh',
        'enam puluh', 'tujuh puluh', 'delapan puluh', 'sembilan puluh'
    ];

    private static $scales = [
        '', 'ribu', 'juta', 'miliar', 'triliun'
    ];

    /**
     * Convert number to Indonesian words
     *
     * @param float|int $number
     * @return string
     */
    public static function convert($number): string
    {
        if ($number == 0) {
            return 'nol';
        }

        // Handle negative numbers
        if ($number < 0) {
            return 'minus ' . self::convert(abs($number));
        }

        // Convert to integer (remove decimal part for currency)
        $number = (int) $number;

        $words = '';
        $scaleIndex = 0;

        while ($number > 0) {
            $chunk = $number % 1000;
            
            if ($chunk != 0) {
                $chunkWords = self::convertChunk($chunk);
                
                if ($scaleIndex > 0) {
                    $chunkWords .= ' ' . self::$scales[$scaleIndex];
                }
                
                if ($words != '') {
                    $words = $chunkWords . ' ' . $words;
                } else {
                    $words = $chunkWords;
                }
            }
            
            $number = intval($number / 1000);
            $scaleIndex++;
        }

        return trim($words);
    }

    /**
     * Convert a chunk of 3 digits to words
     *
     * @param int $chunk
     * @return string
     */
    private static function convertChunk($chunk): string
    {
        $words = '';

        // Hundreds
        $hundreds = intval($chunk / 100);
        if ($hundreds > 0) {
            if ($hundreds == 1) {
                $words .= 'seratus';
            } else {
                $words .= self::$ones[$hundreds] . ' ratus';
            }
        }

        // Tens and ones
        $remainder = $chunk % 100;
        
        if ($remainder > 0) {
            if ($words != '') {
                $words .= ' ';
            }
            
            if ($remainder < 20) {
                if ($remainder == 1 && $chunk >= 1000) {
                    // Special case for "seribu" instead of "satu ribu"
                    $words .= 'se';
                } else {
                    $words .= self::$ones[$remainder];
                }
            } else {
                $tens = intval($remainder / 10);
                $ones = $remainder % 10;
                
                $words .= self::$tens[$tens];
                
                if ($ones > 0) {
                    $words .= ' ' . self::$ones[$ones];
                }
            }
        }

        return $words;
    }

    /**
     * Convert currency amount to words with "rupiah"
     *
     * @param float|int $amount
     * @return string
     */
    public static function convertCurrency($amount): string
    {
        $words = self::convert($amount);
        return $words . ' rupiah';
    }
}
