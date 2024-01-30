<?php

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
set_time_limit(0);

//include_once(__DIR__.'/include/db.inc');
//include_once(__DIR__.'/include/fn.inc');
//include_once(__DIR__.'/sf/lib/helper/TypoHelper.php');
//include_once(__DIR__.'/sf/lib/helper/DesignHelper.php');

$data_dir = __DIR__ . '/templates/data';
$tpl_dir = $data_dir . '/templates';
$html_dir = __DIR__ . '/templates/html';

$group_tpl_dir = $tpl_dir . '/groups';
$complect_tpl_dir = $tpl_dir . '/complects';
$row_tpl_dir = $tpl_dir . '/rows';

$group_html_dir = $html_dir . '/groups';
$complect_html_dir = $html_dir . '/complects';
$row_html_dir = $html_dir . '/rows';

$group_xml = new SimpleXMLElement(file_get_contents($data_dir . '/groups.data'));
$complect_xml = new SimpleXMLElement(file_get_contents($data_dir . '/complects.data'));
$row_xml = new SimpleXMLElement(file_get_contents($data_dir . '/rows.data'));

$errors = array();
//var_dump($group_xml);
$groups = array();
foreach ($group_xml->group as $item) {
    $gattr = $item->attributes();
    $new = array(
        'id' => $gattr->id->__toString(),
        'name' => $gattr->name->__toString(),
        'fullname' => $gattr->fullname->__toString(),
        'order' => isset($gattr->order) ? ($gattr->order + 0) : 0,
        'visible' => isset($gattr->visible) ? ($gattr->visible + 0) : 1,
        'cnt' => 0,
        'file' => isset($gattr->file) ? $gattr->file->__toString() : '',
        'news' => isset($gattr->news) ? intval($gattr->news->__toString()) : 0,
        'complects' => array(),
    );
    if ($new['file'] and !file_exists($group_tpl_dir . '/' . $new['file'] . '.data')) {
        $errors[] = 'файл не найден: "' . $new['file'] . '"';
    }
    if (isset($item->complects->complect)) {
        foreach ($item->complects->complect as $c) {
            $new['complects'][] = array(
                'id' => $c->attributes()->id->__toString(),
                'order' => isset($c->attributes()->order) ? $c->attributes()->order + 0 : 0,
            );
        }
        $groups[] = $new;
    } else {
        $errors[] = 'нет комплектов в группе: "' . $new['id'] . '"';
    }
}
//var_dump($groups); die;
//var_dump($complect_xml);
$complects = array();
foreach ($complect_xml->complect as $item) {
    $text = $item->description->saveXML();
    $text = preg_replace('/<description\/?>/isU', '', $text);
    $text = preg_replace('/<\/description>/isU', '', $text);
    $text = preg_replace('/\s+/is', ' ', $text);
    $text = trim($text);
    $new = array(
        'id' => $item->attributes()->id->__toString(),
        'name' => $item->attributes()->name->__toString(),
        'img' => $item->attributes()->img->__toString(),
        'description' => $text,
        'tag' => $item->attributes()->tag->__toString(),
        'tag_end' => $item->attributes()->tag_end->__toString(),
        'visible' => isset($item->attributes()->visible) ? ($item->attributes()->visible + 0) : 1,
        'file' => $item->attributes()->file->__toString(),
        'variants' => array(),
    );
    if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $new['tag_end'], $matches)) {
        $new['tag_end'] = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
    } else {
        $new['tag_end'] = '';
    }
    if (!$new['file']) {
        $errors[] = 'шаблон комплекта не задан: "' . $new['id'] . '"';
    } elseif (!file_exists($complect_tpl_dir . '/' . $new['file'] . '.data')) {
        $errors[] = 'файл не найден: "' . $new['file'] . '"';
    }
    if (isset($item->variants->variant)) {
        foreach ($item->variants->variant as $v) {
            $variant_description = '';

            foreach ($v as $index => $item) {
                if ($index == 'description') {
                    $variant_description = $item[0];
                }
            }

            $vattr = $v->attributes();
            $new['variants'][] = array(
                'id' => $vattr->id->__toString(),
                'name' => $vattr->name->__toString(),
                'price_base' => 0,
                'is_base' => isset($vattr->base) ? ($vattr->base + 0) : 0,
                'visible' => isset($vattr->visible) ? ($vattr->visible + 0) : 1,
                'order' => isset($vattr->order) ? $vattr->order + 0 : 0,
                'description' => $variant_description,
            );
        }
        if (count($new['variants']) == 1) {
            $new['variants'][0]['is_base'] = 1;
        }
        $complects[] = $new;
    } else {
        $errors[] = 'нет вариантов комплектов в комплекте: "' . $new['id'] . '"';
    }
}
//var_dump($complects);
//var_dump($row_xml);
//$rows = array();
foreach ($row_xml->row as $item) {
    $rattr = $item->attributes();
    $idk = null;
    $idl = null;
    if (isset($item->id)) {
        foreach ($item->id as $v) {
            if ($v->attributes()->type == 'k') $idk = $v->attributes()->name->__toString();
            if ($v->attributes()->type == 'l') $idl = $v->attributes()->name->__toString();
        }
    } else {
        $id = $rattr->id->__toString();
        $idk = $id;
        $idl = $id;
    }
    if (!$idk and !$idl) {
        $errors[] = 'не задан идентификатор запчасти: "' . $rattr->name . '"';
    } else {
        $new = array(
            'id' => md5($idk . $idl),
            'idk' => $idk,
            'idl' => $idl,
            'name' => $rattr->name->__toString(),
            'price' => $rattr->price->__toString(),
            'file' => $rattr->file->__toString(),
            'fixed' => isset($rattr->fixed) ? ($rattr->fixed + 0) : 0,
            'visible' => isset($rattr->visible) ? ($rattr->visible + 0) : 1,
            'variants' => array(),
        );
        if ($new['file'] and !file_exists($row_tpl_dir . '/' . $new['file'] . '.data')) {
            $errors[] = 'файл не найден: "' . $new['file'] . '"';
        }
        if (isset($item->variants->variant)) {
            foreach ($item->variants->variant as $v) {
                $new['variants'][] = array(
                    'id' => $v->attributes()->id->__toString(),
                    'cnt' => $v->attributes()->count + 0,
                    'is_base' => $v->attributes()->base + 0,
                    'order' => isset($v->attributes()->order) ? $v->attributes()->order + 0 : 0,
                );
            }
            $rows[] = $new;
        } else {
            $errors[] = 'нет вариатов комплектов в запчасти: "' . $new['idk'] . '.' . $new['idl'] . '"';
        }
    }
}
//var_dump($rows);
if ($errors) {
    foreach ($errors as $e) {
        echo $e . "\n";
    }
    die;
}

if (1) {
    $sql = "
UPDATE `groups`
SET visible=0";
    execute_query($sql);

    $sql = "
TRUNCATE groups_complects";
    execute_query($sql);

    foreach ($groups as $g) {
        $id = db_escape($g['id']);
        $name = db_escape($g['name']);
        $fullname = db_escape($g['fullname']);
        $file = db_escape($g['file']);
        $order = $g['order'];
        $visible = $g['visible'];
        $news_id = $g['news'];

        $sql = "
SELECT *
FROM `groups`
WHERE id='$id'";
        $row = execute_query_row($sql);

        if ($row) {
            $sql = "
UPDATE `groups`
SET name='$name',
    fullname='$fullname',
    `order`=$order,
    visible=$visible,
    `file`='$file',
    news_id = $news_id
WHERE id='$id'";
        } else {
            $sql = "
INSERT INTO `groups` (id, name, fullname, `order`, visible, `file`, news_id)
VALUES ('$id', '$name', '$fullname', $order, $visible, '$file', $news_id)";
        }

        execute_query($sql);

        foreach ($g['complects'] as $c) {
            $c_id = db_escape($c['id']);
            $c_order = $c['order'];

            $sql = "
INSERT INTO groups_complects (group_id, complect_id, `order`)
VALUES ('$id', '$c_id', $c_order)";
            execute_query($sql);
        }
    }

    $sql = "
UPDATE complects
SET visible=0";
    execute_query($sql);

    $sql = "
UPDATE variants
SET visible=0";
    execute_query($sql);

    foreach ($complects as $c) {
        $id = db_escape($c['id']);
        $name = db_escape($c['name']);
        $img = db_escape($c['img']);
        $description = db_escape($c['description']);
        $tag = db_escape($c['tag']);
        $tag_end = db_escape($c['tag_end']);
        $file = db_escape($c['file']);
        $visible = $c['visible'];

        $sql = "
SELECT *
FROM complects
WHERE id='$id'";
        $row = execute_query_row($sql);

        if ($row) {
            $sql = "
UPDATE complects
SET name='$name',
    img='$img',
    description='$description',
    tag='$tag',
    tag_end=" . ($tag_end ? "'$tag_end'" : 'NULL') . ",
    `file`='$file',
    visible=$visible
WHERE id='$id'";
        } else {
            $sql = "
INSERT INTO complects (id, name, img, description, tag, tag_end, `file`, visible)
VALUES ('$id', '$name', '$img', '$description', '$tag', '$tag_end', '$file', $visible)";
        }

        execute_query($sql);

        foreach ($c['variants'] as $v) {
            $v_id = db_escape($v['id']);
            $v_name = db_escape($v['name']);
            $v_price_base = $v['price_base'];
            $v_is_base = $v['is_base'];
            $v_order = $v['order'];
            $v_visible = $v['visible'];
            $v_description = $v['description'];

            $sql = "
SELECT *
FROM variants
WHERE id='$v_id'";
            $vrow = execute_query_row($sql);

            if ($vrow) {
                $sql = "
UPDATE variants
SET complect_id='$id',
    name='$v_name',
    price_base=$v_price_base,
    is_base=$v_is_base,
    `order`=$v_order,
    visible=$v_visible,
    `description`='$v_description'
WHERE id='$v_id'";
            } else {
                $sql = "
INSERT INTO variants (id, complect_id, name, price_base, is_base, `order`, visible, `description`)
VALUES ('$v_id', '$id', '$v_name', $v_price_base, $v_is_base, $v_order, $v_visible, '$v_description')";
            }

            execute_query($sql);
        }
    }

    $sql = "UPDATE `rows_t`
SET visible=0";
    execute_query($sql);

    $sql = "
TRUNCATE variants_rows";
    execute_query($sql);

    foreach ($rows as $r) {
        $id = db_escape($r['id']);
        $idk = db_escape($r['idk']);
        $idl = db_escape($r['idl']);
        $name = db_escape($r['name']);
        $price = $r['price'];
        $file = db_escape($r['file']);
        $fixed = $r['fixed'];
        $visible = $r['visible'];

        $sql = "
SELECT *
FROM `rows_t`
WHERE id='$id'";
        $row = execute_query_row($sql);

        if ($row) {
            $sql = "
UPDATE `rows_t`
SET idk='$idk',
    idl='$idl',
    name='$name',
    price=$price,
    `file`='$file',
    fixed=$fixed,
    visible=$visible
WHERE id='$id'";
        } else {
            $sql = "
INSERT INTO `rows_t` (id, idk, idl, name, price, `file`, fixed, visible)
VALUES ('$id', '$idk', '$idl', '$name', $price, '$file', $fixed, $visible)";
        }

        execute_query($sql);

        foreach ($r['variants'] as $v) {
            $v_id = db_escape($v['id']);
            $v_cnt = $v['cnt'];
            $v_is_base = $v['is_base'];
            $v_order = $v['order'];

            $sql = "
INSERT INTO variants_rows (variant_id, row_id, cnt, is_base, `order`)
VALUES ('$v_id', '$id', $v_cnt, $v_is_base, $v_order)";
            execute_query($sql);
        }
    }

    foreach ($groups as $g) {
        $sql = "
SELECT count(*) cnt
FROM groups_complects gc
LEFT JOIN `groups` g ON gc.group_id=g.id
WHERE gc.group_id='{$g['id']}'
  AND g.visible=1";
        $row = execute_query_row($sql);

        $cnt = $row['cnt'];

        $sql = "
SELECT count(*) cnt
FROM
  (SELECT DISTINCT r.id
   FROM groups_complects gc
   LEFT JOIN `groups` g ON gc.group_id=g.id
   LEFT JOIN variants v ON v.complect_id=gc.complect_id
   LEFT JOIN variants_rows vr ON vr.variant_id=v.id
   LEFT JOIN `rows_t` r ON r.id=vr.row_id
   WHERE gc.group_id='{$g['id']}'
     AND r.fixed=0
     AND r.visible=1
     AND g.visible=1 ) t";
        $row = execute_query_row($sql);

        $cnt += $row['cnt'];

        if ($row) {
            $sql = "
UPDATE `groups`
SET cnt=$cnt
WHERE id='{$g['id']}'";
            execute_query($sql);
        }
    }

    $variants = execute_query_array('
SELECT id
FROM variants');

    foreach ($variants as $v) {
        $sql = "
SELECT sum(r.price*vr.cnt) total
FROM variants v
LEFT JOIN variants_rows vr ON vr.variant_id=v.id
LEFT JOIN `rows_t` r ON r.id=vr.row_id
WHERE vr.is_base=1
  AND v.id='{$v['id']}'";
        $row = execute_query_row($sql);

        $row['total'] += 0;

        $sql = "
UPDATE variants
SET price_base=" . $row['total'] . "
WHERE id='{$v['id']}'";
        execute_query($sql);
    }
}

$gallery_height = 150;
$right_width = 200;

/* конвертация картинок для списка комплектов */
foreach ($complects as $c) {
    convert_img($c['img'], 'devices');
}
/* описание групп */
echo "сохранить html групп:<br>\n";
foreach ($groups as $g) {
    if ($g['file']) {
        make_file($g['file'], $group_tpl_dir, $group_html_dir);
        echo "  " . $g['file'] . ".htm<br>\n";
    }
}
echo "<br>\n";
/* страницы комплектов */
echo "сохранить html комплектов:<br>\n";
foreach ($complects as $c) {
    make_file($c['file'], $complect_tpl_dir, $complect_html_dir);
    echo "  " . $c['file'] . ".htm<br>\n";
}
echo "<br>\n";
/* страницы запчастей */
echo "сохранить html запчастей:<br>\n";
foreach ($rows as $r) {
    if ($r['file']) {
        make_file($r['file'], $row_tpl_dir, $row_html_dir, $r['id']);
        echo "  " . $r['file'] . ".htm<br>\n";
    }
}
echo "<br>\n";

function make_file($name, $tpl_dir, $html_dir, $row_id = null)
{
    $text = file_get_contents($tpl_dir . '/' . $name . '.data');
    $html = xml2html($text, $row_id);
    file_put_contents($html_dir . '/' . $name . '.htm', $html);
}

function xml2html($text, $row_id = null)
{
    $text = replace_links($text);
    $text = preg_replace('/(allowfullscreen)([^=])/iU', 'allowfullscreen="allowfullscreen" $2', $text); // youtube
    $text = hide_entities($text);

    $xml = new SimpleXMLElement($text);

    $html = '';

    foreach ($xml as $block) {
        $block_title = '';
        $block_content = '';
        $right_link_group = '';
        $right_publication = '';

        if ($block->attributes()->title and !$block->order and !$block->usage) {
            $block_title = '<h2><span>' . typograf($block->attributes()->title) . '</span></h2>' . PHP_EOL;
        }

        foreach ($block as $k => $v) {
            if ($k == 'gallery') {
                $block_content .= typograf(render_gallery_smipro($v)) . PHP_EOL;
            } elseif ($k == 'video') {
                $block_content .= typograf(render_video2($v)) . PHP_EOL;
            } elseif ($k == 'spec') {
                $block_content .= typograf(render_spec($v)) . PHP_EOL;
            } elseif ($k == 'consist') {
                $block_content .= typograf(render_consist($v)) . PHP_EOL;
            } elseif ($row_id and $k == 'usage') {
                $sql = "
SELECT v.id id,
       v.name name,
       v.complect_id
FROM variants_rows vr
LEFT OUTER JOIN variants v ON v.id=vr.variant_id
WHERE vr.row_id='$row_id'
  AND v.visible=1";
                $usage = execute_query_array($sql);
                $block_content .= typograf(render_usage($v, $usage)) . PHP_EOL;
            } elseif ($k == 'order') {
                $block_content .= '<?php include_partial("order", array("form" => $form, "params" => $params)) ?>' . PHP_EOL;
            } elseif ($k == 'right') {
                continue;
            } elseif ($k == 'publication') {
                $right_publication .= typograf(render_publication($v));
            } elseif ($k == 'right_link_group') {
                $right_link_group .= typograf(render_right_link_group($v));
            } elseif ($k == 'note') {
                $block_content .= str_replace('</note>',
                    '</p>',
                    str_replace('<note>',
                        '<p class="note">',
                        typograf($v->saveXML())));
            } elseif ($k == 'p') {
                $block_content .= typograf($v->saveXML()) . PHP_EOL;
            } else {
                $block_content .= preg_replace('/\&amp;nbsp;/',
                    '&nbsp;',
                    typograf($v->saveXML()) . PHP_EOL);
            }
        }

        $html .= '<div class="block">' . $block_title;

        if ($right_link_group) {
            $html .= '
<div class="block-row">
    <div class="block-column">' . $block_content . '</div>
    <div class="block-column right-link-group">' . $right_link_group . '</div>
</div>';
        } elseif ($right_publication) {
            $html .= '
<div class="block-row">
    <div class="block-column">' . $block_content . '</div>
    <div class="block-column right-publication">' . $right_publication . '</div>
</div>';
        } else {
            $html .= $block_content;
        }

        $html .= '</div>';
    }

    if ($xml->attributes()->expand_title) {
        $html = '<div class="expand"><div class="expand-title"><i></i><span>' . $xml->attributes()->expand_title . '</span></div><div class="expand-text">' . $html . '</div></div>';
    }

    return $html;
}
