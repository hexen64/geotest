<?php

namespace App\Twig;

use Twig\TwigFilter;

class DesignHelperExtension extends \Twig\Extension\AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('format_rub', [$this, 'formatRubFilter']),
            new TwigFilter('undo_btn', [$this, 'undoBtn']),
            new TwigFilter('declension', [$this, 'declension']),
        ];
    }

    public function formatRubFilter($value)
    {
        return '<span class="text-nowrap">' . number_format($value, 0, ',', ' ') . '</span><span class="rubznak">p</span>';
    }

    public function undoBtn($id){
        return '<div id="undo_'.$id.'" class="btn-undo">отменить</div>';
    }

    function declension ($digit){
        $word = ['наименование', 'наименования', 'наименований'];
        $last_digit = $digit%10;
        if(($last_digit < 5) && ($last_digit > 0) && ((ceil($digit/10)-1) != 1)){
            if($last_digit == 1){
                $ret = $word[0];
            }else{
                $ret = $word[1];
            }
        }else{
            $ret = $word[2];
        }
        return $ret;
    }



}