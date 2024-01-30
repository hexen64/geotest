<?php

namespace App\Services;

use SimpleXMLElement;

final class XmlRenderer
{

    public static function transliterate($string)
    {
        $url = trim($string);
        $url = preg_replace('/[^0-9_A-Za-zА-Яа-я ]/ui', '', $url);
        $url = iconv('utf-8', 'cp1251', $url);
        $url = strtolower($url);
        $url = iconv('cp1251', 'utf-8', $url);
        $url = preg_replace('/[,;.:]/', ' ', $url);
        $url = preg_replace('/\s+/', ' ', $url);
        $url = trim($url);
        $url = str_replace(' ', '_', $url);
        $cyr = array(
            'а', 'б', 'в', 'г', 'д', 'е', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у',
            'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'ю', 'я', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ж', 'З', 'И',
            'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь',
            'Ю', 'Я');
        $lat = array(
            'a', 'b', 'v', 'g', 'd', 'e', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u',
            'f', 'h', 'ts', 'ch', 'sh', 'sht', 'a', 'i', 'y', 'yu', 'ya', 'A', 'B', 'V', 'G', 'D', 'E', 'Zh', 'Z',
            'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'Ts', 'Ch', 'Sh', 'Sht', 'A',
            'I', 'Y', 'Yu', 'Ya');
        $url = str_replace($cyr, $lat, $url);
        return $url;
    }

    public static function renderGallery(SimpleXMLElement $xml_obj)
    {
        $ret = '<div class="gallery">
<h3>' . ($xml_obj->attributes()->title ? $xml_obj->attributes()->title : 'Фотографии') . '</h3>
<div id="photoAlbumPhotosList">
';
        foreach ($xml_obj->img as $img) {
            $ret .= self::renderImg($img, 'gallery') . PHP_EOL;
        }
        $ret .= '<div class="clearfix"></div>
</div>
</div>';
        return $ret;
    }

    public static function renderGallerySmipro(SimpleXMLElement $xml_obj)
    {
        $title = $xml_obj->attributes()->title ? $xml_obj->attributes()->title : 'Фотографии';
        $photos = array();
        foreach ($xml_obj->img as $img) {
            if (!isset($img->attributes()->src)) continue;

            $ph = array();
            foreach ($img->attributes() as $name => $attr) {
                $ph[$name] = $attr;
            }
            if ($thumb = self::convertImg($ph['src'], 'gallery_smipro')) {
                $id = preg_replace('/(.*)\./', '$1', $ph['src']);
                $ph['id'] = $id;
                $ph['thumb'] = $thumb;
            } else {
                $ph['thumb'] = $ph['src'];
            }
            if (!isset($ph['alt'])) $ph['alt'] = '';
            $photos[] = $ph;
        }

        $html = '';
        $i = 1;
        foreach ($photos as $ph) {
//        $html .= '
//<div class="item'.($i === 1 ? ' active' : '').'" data-pslider-pos="'.$i.'">
//    <a href="/i/'.$ph['src'].'" target="_blank">
//        <div style="background-image: url(/i/'.$ph['thumb'].')" alt="Фото №'.$i.'" title="'.$ph['alt'].'"></div>
//    </a>
//    <div class="item-desc">'.$ph['alt'].'</div>
//</div>';
            $html .= '
            <div class="slide">
                <a href="/i/' . $ph['src'] . '" target="_blank" class="slide-img">
                    <div style="background-image: url(/i/' . $ph['thumb'] . ')" alt="Фото №' . $i . '" title="' . $ph['alt'] . '"></div>
                </a>
                <div class="slide-text">' . $ph['alt'] . '</div>
            </div>
            ';
            $i++;
        }

//    $html = '
//<div class="pslider__wrapper">
//    <div class="pslider__photos__wrapper">
//        <div class="navi_left" style="display: none;"></div>
//        <div class="navi_right"></div>
//        <div class="pslider__photos__list">'.$html.'</div>
//    </div>
//</div>';
        $html = '<div class="owl-carousel owl-theme slider slider-phone">' . $html . '</div>';

        return $html;
    }

    public static function renderVideo2(SimpleXMLElement $xml_obj)
    {
        $str = $xml_obj->iframe->saveXML();
        $str = substr($str, 0, strlen($str) - 2);
        $str .= '></iframe>';
        if (preg_match('/^(.*src=")([^"]+)(".*)$/is', $str, $matches)) {
            $src = $matches[2];
            $src .= (strpos($src, '?') === false ? '?' : '&') . 'wmode=opaque';
            $str = $matches[1] . $src . $matches[3];
        }
        return
            '<div class="video">' . PHP_EOL .
            ($xml_obj->attributes()->title ? '<h2><span>' . $xml_obj->attributes()->title . '</span></h2>' . PHP_EOL : '') .
            '<div class="video-container">' . $str . '</div>' . PHP_EOL .
            '</div>';
    }

    public static function renderText(SimpleXMLElement $xml_obj)
    {
        return '<div class="text">' . Typograf::process($xml_obj->saveXML()) . '</div>';
    }

    public static function renderSpec(SimpleXMLElement $xml_obj)
    {
        $note = '';
        $ret = '';

        foreach ($xml_obj as $index => $spec) {
            if ($index == 'note') {
                $note = $spec->saveXML();
                $note = str_replace('<note>', '<div class="spec-note">', $note);
                $note = str_replace('</note>', '</div>', $note);
                continue;
            }

            $ret .= '
        <tr>
            <td>' . $spec->attributes()->param . '</td>
            <td>' . $spec->attributes()->val . '</td>
        </tr>';
        }

        $ret = '
        <div class="block-row">
            <div class="block-column">
                <table class="table spec">' . $ret . '</table>
            </div>
            <div class="block-column">' . $note . '</div>
        </div>';

        return $ret;
    }

    public static function renderConsist(SimpleXMLElement $xml_obj)
    {
        $note = '';
        $ret = '';

        foreach ($xml_obj as $index => $val) {
            if ($index == 'note') {
                $note = $val->saveXML();
                $note = str_replace('<note>', '<div class="spec-note">', $note);
                $note = str_replace('</note>', '</div>', $note);
                continue;
            }

            $ret .= '
<tr>
    <td>' . $val->attributes()->param . '</td>
    <td>' . $val->attributes()->val . '</td>
</tr>';
        }

        $ret = '
<div class="block-row">
    <div class="block-column">
        <table class="table spec">' . $ret . '</table>
    </div>
    <div class="block-column">' . $note . '</div>
</div>';

        return $ret;
    }

    public static function renderUsage(SimpleXMLElement $xml_obj, $complects)
    {
        $ret = '';
        foreach ($complects as $complect) {
            $ret .= '<li><a href="/complect/' . $complect['complect_id'] . ($complect['id'] != $complect['complect_id'] ? '#' . $complect['id'] : '') . '">' . $complect['name'] . '</a></li>' . PHP_EOL;
        }
        return $ret ? '<h2><span>Применимость</span></h2><ul class="usage">' . Typograf::process($ret) . '</ul>' : '';
    }

    public static function renderImg(SimpleXMLElement $xml_obj, $type)
    {
        $str = '';
        $src = '';
        foreach ($xml_obj->attributes() as $name => $attr) {
            if ($name == 'src') {
                $src = $attr;
                continue;
            }
            if ($name == 'href') {
                continue;
            }
            $str .= ' ' . $name . '="' . $attr . '"';
        }
        if ($thumb = self::convertImg($src, $type)) {
            $img_name = preg_replace('/(.*)\./', '$1', $src);
            $str = '<a href="/i/' . $src . '" class="img"' . ($type == 'gallery' ? ' data-action="showBigPhoto" id="' . $img_name . '"' : '') . '><img src="/i/' . $thumb . '" ' . $str . '></a>';
        }
        return $str;
    }

    public static function convertImg($file, $type)
    {
        $params = array(
            'gallery' => array('resize' => 'x150', 'thumb' => 'g'),
//		'gallery_smipro' => array('resize' => 'x400', 'thumb' => 'gs'),
            'gallery_smipro' => array('resize' => 'x484', 'thumb' => 'gs'),
            'right' => array('resize' => '200', 'thumb' => 'r'),
            'devices' => array('resize' => '250', 'thumb' => 'd')
        );
        $img_dir = realpath(dirname(__FILE__) . '/../../public/i');

        if (!file_exists($img_dir . '/' . $file)) {
            return false;
        }
        if (array_key_exists($type, $params) === false) {
            return false;
        }

        if (preg_match('/^(.+)\.([A-Za-z]*)$/i', $file, $matches)) {
            $thumb = $matches[1] . '_' . $params[$type]['thumb'] . '.' . $matches[2];
            $md5 = md5_file($img_dir . '/' . $file);

//            if (file_exists($img_dir . '/' . $thumb)) {
//                $exif = exif_read_data($img_dir . '/' . $thumb);
//            }

//            if (!isset($exif) or !isset($exif['COMMENT'][0]) or $exif['COMMENT'][0] != $md5) {
//                $cmd = 'gm convert -interlace line -resize ' . $params[$type]['resize'] . ' -comment \'' . $md5 . '\' ' . $img_dir . '/' . $file . ' ' . $img_dir . '/' . $thumb;
//                exec($cmd);
//            }

            return $thumb;
        } else {
            return false;
        }
    }

    public static function renderVideo(SimpleXMLElement $xml_obj)
    {
        $str = '<img';
        $href = '';
        foreach ($xml_obj->attributes() as $name => $attr) {
            if ($name == 'href') {
                $href = $attr;
                continue;
            }
            if ($name == 'src') {
                $attr = '/i/' . $attr;
            }
            $str .= ' ' . $name . '="' . $attr . '"';
        }
        $str .= '>';
        if ($href) {
            $str = '<a href="' . $href . '" class="video">' . $str . '</a>';
        }
        return $str;
    }

    public static function renderPublication(SimpleXMLElement $xml_obj)
    {
        $attr = $xml_obj->attributes();
        $title = isset($attr->title) ? $attr->title->__toString() : '';
        $date = isset($attr->date) ? $attr->date->__toString() : '';
        $file = isset($attr->file) ? $attr->file->__toString() : '';
        $img = isset($attr->src) ? $attr->src->__toString() : '';
//    $img = pdf_preview($file);

        return '
        <div class="publication">
            <div class="publication-img" style="background-image: url(\'' . $img . '\')"></div>
            <div class="publication-desc">
                <div class="publication-title">
                    <a href="' . $file . '">' . $title . '</a>
                </div>
                <div class="publication-date">' . $date . '</div>
            </div>
        </div>';
    }

    public function renderRightLinkGroup(SimpleXMLElement $xml_obj)
    {
        $str = '';

        foreach ($xml_obj as $k => $v) {
            if (isset($v->attributes()->to)) {
                if ($k == 'link_row' /*preg_match('/^\/row\/(.*)$/i', $v->attributes()->href->__toString(), $matches)*/) {
//                $id = $matches[1];
                    $id = $v->attributes()->to->__toString();
                    $row = $this->em->createQueryBuilder()
                        ->select('r')
                        ->from('App:Rows', 'r')
                        ->where('r.idk = :id or r.idl = :id')
                        ->setParameter('id', $id)
                        ->getQuery()
                        ->getResult();

                    if ($row) {
                        $price = (isset($v->attributes()->price) ? $v->attributes()->price->__toString() : $row['price']);

                        $str .= '
                        <div class="right-link-group-item">
                            <div class="right-link-group-item-img" style="background-image: url(/i/' . $row['file'] . '.jpg)"></div>
                              <div class="right-link-group-item-title">
                                <a href="/row/' . $row['id'] . '">' . $row['name'] . '</a>
                            </div>
                            ' . ($price ? '<div class="right-link-group-item-price">' . Formatter::formatRub($price) . '</div>' : '') . '
                        </div>';
                    }

                } elseif ($k == 'link' /*preg_match('/^\/complect\/(.*)$/i', $v->attributes()->href->__toString(), $matches)*/) {
//                $id = $matches[1];
                    $id = $v->attributes()->to->__toString();

                    $row = $this->em->createQueryBuilder()
                        ->select('c')
                        ->from('App:Complects', 'c')
                        ->where('c.id = :id')
                        ->setParameter('id', $id)
                        ->getQuery()
                        ->getResult();

                    if ($row) {
                        $price = (isset($v->attributes()->price) ? $v->attributes()->price->__toString() : 0);

                        $str .= '
                        <div class="right-link-group-item">
                            <div class="right-link-group-item-img" style="background-image: url(/i/' . $row['img'] . ')"></div>
                            <div class="right-link-group-item-title">
                                <a href="/complect/' . $id . '">' . $row['name'] . '</a>
                            </div>
                            ' . ($price ? '<div class="right-link-group-item-price">' . Formatter::formatRub($price) . '</div>' : '') . '
                        </div>';
                    }

                }
            }
        }

        return '<div class="right-link-group">' . $str . '</div>';
    }

}