<?php

namespace App\Services;

final class Formatter
{
    public static function formatRub(int $v): string
    {
        return '<span class="text-nowrap">' . number_format($v, 0, ',', ' ') . '</span><span class="rubznak">p</span>';
    }

}