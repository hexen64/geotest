<?php

function format_rub($v){
    return '<span class="text-nowrap">'.number_format($v, 0, ',', ' ').'</span><span class="rubznak">p</span>';
}

function format_rub_num($v){
    return number_format($v, 0, ',', ' ');
}

function hide_entities($data){
    return str_replace("&", "&amp;", $data);
}

function custom_header($str){
    return '<h2><span>'.$str.'</span></h2>';
}

function undo_btn($id){
    return '<div id="undo_'.$id.'" class="btn-undo">отменить</div>';
}

function order_status($idorder, $count){
    if(!$count) return '';
    return 'Добавлено <a href="/order/'.$idorder.'">к заказу</a> '.$count.' шт.';
}

function delete_btn($url){
    return '<a href="'.$url.'" class="delete" title="Удалить" onclick="return confirm(\'Удалить?\');"></a>';
}

