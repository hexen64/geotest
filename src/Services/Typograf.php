<?php

namespace App\Services;

final class Typograf
{
    public static function tagAutoClose($txt, $unclosed_tags = array('li', 'br', 'p', 'hr'))
    {
        if (preg_match_all('/<\s*(\/*)\s*([^\<\>\s]+)[^\<\>]*>/', $txt, $matches)) {
            $unclosed_tags = array_flip($unclosed_tags);
            for ($i = count($matches[2]) - 1; $i >= 0; $i--) {
                $matches[2][$i] = strtolower($matches[2][$i]);
                if (!isset($unclosed_tags[$matches[2][$i]])) {
                    if (!isset($tags[$matches[2][$i]])) {
                        $tags[$matches[2][$i]] = 0;
                    };
                    if ($matches[1][$i] == '/') {
                        $tags[$matches[2][$i]]--;
                    } else {
                        $tags[$matches[2][$i]]++;
                    };
                };
            };
            foreach ($tags as $tag => $status) {
                $txt .= str_repeat('</' . $tag . '>', $status);
            }
        };
        return $txt;
    }

    public static function wrapMail($txt, $wrap_factor = 75, $typo_flag = 1)
    {
        $valid_len = $wrap_factor;
        $invalid_len = $valid_len + 1;
        $txt = preg_replace('/\r+/', '', $txt);
        if ($typo_flag) {
            $strings = explode("\n", $txt);
            foreach (array_keys($strings) as $key) {
                $strings[$key] = self::process($strings[$key]);
            };
            $txt = join("\n", $strings);
            unset($strings);
            $txt = preg_replace('/<\s*nobr\s*>/i', "\x02", $txt);
            $txt = preg_replace('/<\s*\/\s*nobr\s*>/i', "\x03", $txt);
            $txt = preg_replace('/\x02([^\x02\x03])\x03/ie', "preg_replace('/ /', '&nbsp;', '\\2')", $txt);
            $txt = preg_replace('/[\x02\x03]+/', '', $txt);
            $txt = preg_replace('/&nbsp;/i', "\x01", $txt);
            $txt = preg_replace('/&(r|l)aquo;/i', '"', $txt);
            $txt = preg_replace('/&(n|m)dash;/i', '-', $txt);
            $txt = preg_replace('/&#8470;/i', 'N', $txt);
        };
        $txt = preg_replace('/^([^ \n]{' . $invalid_len . ',}) *\n?/m', "\\1\n", $txt);
        $txt = preg_replace('/ ([^ \n]{' . $invalid_len . ',}) *\n?/', "\n\\1\n", $txt);
#vars_dump($txt, 1);
        $result = '';
        foreach (explode("\n", $txt) as $str) {
            if (strlen($str) > $valid_len && preg_match('/ /', $str)) {
                preg_match_all('/(.{0,' . $valid_len . '})( +|$)/', $str, $matches);
                $result .= implode("\n", $matches[1]);
            } else {
                $result .= $str . "\n";
            }
        }
        if ($typo_flag) {
            $result = preg_replace('/\x01/', ' ', $result);
        };
        $result = preg_replace('/\n$/i', '', $result);
        return $result;
    }

    public static function advRedact($its, $add_p = 0)
    {
        if (isset($its)) {
            if (!preg_match_all('/([^<>]*)(<[^<>]+>)([^<>]*)/', $its, $mathes)) {
                $mathes[0][0] = $its;
                $mathes[1][0] = $its;
                $mathes[2][0] = '';
                $mathes[3][0] = '';
            };
        };
        $its = '';
        foreach (array_keys($mathes[0]) as $idx) {
            $its .= $mathes[1][$idx];
            if (preg_match('/<\s*\/*\s*nobr\s*>/i', $mathes[2][$idx])) {
                $its .= $mathes[2][$idx];
            } else {
                if ($mathes[2][$idx]) {
                    $its .= '<sleeping_tag_' . $idx . '>';
                };
            };
            $its .= $mathes[3][$idx];
        };
        $its = self::process($its);
        $its = preg_replace('/<sleeping_tag_(\d+)>/e', "\$mathes[2][$1]", $its);
        if ($add_p) {
            # приделать если надо <p>
            if ($add_p) {
                $its = preg_replace('/<p>/', "\x01", $its);
                $its = preg_replace('/<\/p>/', "\x02", $its);
                $its = preg_replace('/^/', "\x01", $its);
                $its = preg_replace('/\n/', "\x02\n\x01", $its);
                $its = preg_replace('/$/', "\x02", $its);
                $its = preg_replace('/\x01\x02/', '', $its);
                $its = preg_replace('/\x01+/', '<p>', $its);
                $its = preg_replace('/\x02+/', '</p>', $its);
            };
        };
        return $its;
    }

    public static function fineNobr($its)
    {
        /*
        vars_dump($its,1);
                $its=preg_replace('/&nbsp;/',"\x03",$its);
                $its=preg_replace('/<nobr>/',"\x01",$its);
                $its=preg_replace('/<\/nobr>/',"\x02",$its);
                $after=preg_replace('/\x01([^\x02]*)\x01/',"\x01$1",$its);
                $after=preg_replace('/\x02([^\x01]*)\x02/',"$1\x02",$its);
                while($after!=$its){
                    $its=$after;
                    $after=preg_replace('/\x01([^\x02]*)\x01/',"\x01$1",$its);
                    $after=preg_replace('/\x02([^\x01]*)\x02/',"$1\x02",$its);
                };
        vars_dump($its,1);
                preg_match_all('/([^\x01\x02]*)\x01?([^\x01\x02]*)\x02?([^\x01\x02]*)/',$its,$matches);
                $its.='';
                foreach(array_keys($matches) as $idx){
                    $its.=$matches[1][$idx];
                    $its.=preg_replace('/\s/',"\x03",$matches[2][$idx]);
                    $its.=$matches[3][$idx];
                };
        vars_dump($matches,1);
                $its=preg_replace('/\x01/','<nobr>',$its);
                $its=preg_replace('/\x02/','</nobr>',$its);
        */
        # зачистка рекурсирующих нобров
        $its = preg_replace('/<nobr>/', "\x01", $its);
        $its = preg_replace('/<\/nobr>/', "\x02", $its);
        $its = preg_replace('/\x01([^\x01\x02]*)\x01/', "\x01\\1", $its);
        $its = preg_replace('/\x02([^\x02\x01]*)\x02/', "\\1\x02", $its);
        # изъятие лишних нобров
        $after = preg_replace('/(\x01[^\x01\x02]*)&nbsp;([^\x01\x02]*\x02)/', '\\1 \\2', $its);
        while ($after != $its) {
            $its = $after;
            $after = preg_replace('/(\x01[^\x01\x02]*)&nbsp;([^\x01\x02]*\x02)/', '\\1 \\2', $its);
        };
        # замена подстановок на нобры
        $its = preg_replace('/\x01/', '<nobr>', $its);
        $its = preg_replace('/\x02/', '</nobr>', $its);
        return $its;
    }

    public static function process($its)
    {
        # бахнуть нобры
        $its = preg_replace('/<[\/]*nobr>/u', '', $its);
        # убрать \r \t
        $its = preg_replace('/\r|\t/u', '', $its);
        # убрать пробелы в начале строк
        $its = preg_replace('/^\s+/', '', $its);
        $its = preg_replace('/\n\s+/u', "\n", $its);
        # убрать множественные проьелы
        $its = preg_replace('/\s{2,}/u', ' ', $its);
        # убрать пробелы в конце строк
        $its = preg_replace('/\s+$/u', '', $its);
        # замена козьих ковычек на человечачьи
        $its = preg_replace('/[“«]/u', '&laquo;', $its);
        $its = preg_replace('/[”»]/u', '&raquo;', $its);
        # замена козьей палки на рульную
        $its = preg_replace('/[–—]/u', '-', $its);
        # замена козьей троеточей на три козьих точки
        $its = preg_replace('/[…]/u', '...', $its);
        # запятой
        #		$its=preg_replace('/\s*,\s*/',', ',$its);
        $its = preg_replace('/\s*,\s*([^\d])/u', ', \\1', $its);
        # запятой между цифрами
        #		$its=preg_replace('/(\s\d+),\s(\d)/','\\1,\\2',$its);
        #		$its=preg_replace('/^(\d+),\s(\d)/','\\1,\\2',$its);
        # кривые пробелы возле скобок
        $its = preg_replace('/\(\s+/u', '(', $its);
        $its = preg_replace('/\s+\)/u', ')', $its);

        $its = preg_replace('/([href|title|align|src|alt|width|height|style|class])="([^"]+)"/u', '$1=x02$2x02', $its);
        # ковычки
        # _"слово
        # ковычка между словом и троеточим
        $its = preg_replace('/"\s*(\.\.\.)/u', '&raquo;\\1', $its);
        # открывающая ковычка в начале строки
        $its = preg_replace('/^"/um', '&laquo;', $its);
        # открывающая ковычка справа от сепаратора
        $its = preg_replace('/([; >(])"([^\s\>\&\.\,\:\!\?\;\)])/u', '\\1&laquo;\\2', $its);
        # слово"_
        $its = preg_replace('/"/u', '&raquo;', $its);
#		$its=preg_replace('/([^\s\>])"/','\\1&raquo;',$its);
        $its = preg_replace('/x02/u', '"', $its);

        # телефонный номер c кодом типа (3432) 31-31-31
        $its = preg_replace('/\(\s*(\d+)\s*\)\s*(\d+)\s*-\s*(\d+)\s*-\s*(\d+)/u', '<nobr>(\\1) \\2&ndash;\\3&ndash;\\4</nobr>', $its);
        # телефонный номер дуплем по 3 типа 31-31-31
        $its = preg_replace('/(\d+)\s*-\s*(\d+)\s*-\s*(\d+)/u', '<nobr>\\1&ndash;\\2&ndash;\\3</nobr>', $its);
        # газетный сдвоенный номер
        $its = preg_replace('/(\d+)\s*-\s*(\d+)\s*\((\d+)\s*-\s*(\d+)\)/u', '<nobr>\\1&ndash;\\2&nbsp;(\\3&ndash;\\4)</nobr>', $its);
        # газетный одинарный номер номер
        $its = preg_replace('/(\d+)\s*\(\s*(\d+)\s*\)/u', '<nobr>\\1&nbsp;(\\2)</nobr>', $its);
        # квадратные метры
        $its = preg_replace('/\s+м2([\s<\.,]+)/u', ' <nobr>кв.м</nobr>\\1', $its);
        # кубические метры
        $its = preg_replace('/\s+м3([\s<\.,]+)/u', ' <nobr>куб.м</nobr>\\1', $its);
        # квадратные сантиметры
        $its = preg_replace('/\s+см2([\s<\.,]+)/u', ' <nobr>кв.см</nobr>\\1', $its);
        # кубические сантиметры
        $its = preg_replace('/\s+см3([\s<\.,]+)/u', ' <nobr>куб.см</nobr>\\1', $its);

        # Фамилия А. Нарзанович
        $its = preg_replace('/([А-Я])\s*\.\s*([А-Я][а-я]+)/u', '\\1.&nbsp;\\2', $its);
        # Фамилия А.А. Нарзанович
#		$its=preg_replace ('/(.)\s*\.\s*(.)\s*\.\s*(\S+)/', '<font color=#ff0000>\\1.&nbsp;\\2.&nbsp;\\3</font>',$its);
        # палка между цифрами типа 10-20
        $its = preg_replace('/(\d+)\s*-\s*(\d+)/u', '<nobr>\\1&ndash;\\2</nobr>', $its);
        # троеточие
        $its = preg_replace('/\.\.\./u', '&#133;', $its);
        # слово после цифири типа 6075634 лет
        $its = preg_replace('/(\d)\s+/u', '\\1&nbsp;', $its);

        # палка в начале строки
        $its = preg_replace('/(\n<[^>]+>)\s*-\s*/u', '\\1&mdash;&nbsp;', $its);
        # неправильная палка близко к слову и пробел перед ней
        $its = preg_replace('/([^А-Яа-я0-9A-Za-z])-\s*/u', '\\1- ', $its);
        # палка между словами
        $its = preg_replace('/\s+-\s+/u', '&nbsp;&mdash; ', $its);

        # неразрывный пробел после маленького слова в данном случае длинной 2
#		vars_dump($its,1);
        $preg_pattern = '/([\s\-;>][^\n .,;]{1,2})\s+/u';
        $after = preg_replace($preg_pattern, '$1&nbsp;', $its);
        while ($after != $its) {
            $its = $after;
            $after = preg_replace($preg_pattern, '\\1&nbsp;', $its);
        };
        $its = preg_replace('/<([a-z]{1,2})&nbsp;/u', '<$1 ', $its);

        # всяко номер поменять
        $its = preg_replace('/№/u', '&#8470;', $its);

#		vars_dump($its,1);
        # дефис типа 60-летие, 3-й
        $its = preg_replace('/(\d+-[^<\s&]+)/u', '<nobr>\\1</nobr>', $its);

        # прибивка лишних &nobr;
        $its = self::fineNobr($its);

        return $its;
    }
}