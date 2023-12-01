<?php

namespace App\Twig;

use Twig\TwigFilter;

class DesignHelperExtension extends \Twig\Extension\AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('format_rub', [$this, 'formatRubFilter']),
        ];
    }

    public function formatRubFilter($value)
    {
        return '<span class="text-nowrap">' . number_format($value, 0, ',', ' ') . '</span><span class="rubznak">p</span>';
    }
}