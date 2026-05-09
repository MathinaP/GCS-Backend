<?php

namespace App\Helpers;

class NumberToWords
{
    private static array $ones = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private static array $tens = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    public static function convert(float|int|string $amount): string
    {
        $amount  = round((float) $amount, 2);
        $rupees  = (int) $amount;
        $paise   = (int) round(($amount - $rupees) * 100);

        $words = 'Rupees ' . self::inWords($rupees);
        if ($paise > 0) {
            $words .= ' and ' . self::inWords($paise) . ' Paise';
        }

        return $words . ' Only';
    }

    private static function inWords(int $num): string
    {
        if ($num === 0) return 'Zero';

        $result = '';

        if ($num >= 10_000_000) {
            $result .= self::inWords((int) ($num / 10_000_000)) . ' Crore ';
            $num %= 10_000_000;
        }
        if ($num >= 100_000) {
            $result .= self::inWords((int) ($num / 100_000)) . ' Lakh ';
            $num %= 100_000;
        }
        if ($num >= 1_000) {
            $result .= self::inWords((int) ($num / 1_000)) . ' Thousand ';
            $num %= 1_000;
        }
        if ($num >= 100) {
            $result .= self::$ones[(int) ($num / 100)] . ' Hundred ';
            $num %= 100;
        }
        if ($num > 0) {
            if ($num < 20) {
                $result .= self::$ones[$num];
            } else {
                $result .= self::$tens[(int) ($num / 10)];
                if ($num % 10) {
                    $result .= ' ' . self::$ones[$num % 10];
                }
            }
        }

        return trim($result);
    }
}
