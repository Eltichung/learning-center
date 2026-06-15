<?php

namespace App\Support;

class Money
{
    /** 120000 -> "120.000đ" */
    public static function vnd(int|float|null $n): string
    {
        return number_format((int) $n, 0, ',', '.') . 'đ';
    }

    /**
     * Dạng rút gọn, KHÔNG làm tròn sai lệch:
     * 1650000 -> "1.65tr", 18400000 -> "18.4tr", 2200000 -> "2.2tr",
     * 950000 -> "950k", 360500 -> "360.5k", 0 -> "0đ".
     * (tr: tối đa 2 số lẻ; k: tối đa 1 số lẻ; bỏ số 0 thừa)
     */
    public static function short(int|float|null $n): string
    {
        $n = (int) $n;
        if ($n >= 1_000_000) {
            return rtrim(rtrim(number_format($n / 1_000_000, 2, '.', ''), '0'), '.') . 'tr';
        }
        if ($n >= 1_000) {
            return rtrim(rtrim(number_format($n / 1_000, 1, '.', ''), '0'), '.') . 'k';
        }
        return $n . 'đ';
    }
}
