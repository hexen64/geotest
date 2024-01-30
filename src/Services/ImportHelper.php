<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use SimpleXMLElement;

final class ImportHelper
{

    public function __construct(
        private EntityManagerInterface $em)
    {
    }

    public function replaceLinks($text)
    {
        // ссылка на комплектующее
        $text = preg_replace_callback('/<link_row to="(.*)">([^<]*)<\/link_row>/iU', function ($matches) {
            $id = $matches[1];
            $row = $this->em->createQueryBuilder()
                ->select('r')
                ->from('App:Rows', 'r')
                ->where('r.idk = :idk or r.idl = :idl')
                ->setParameter('idk', $id)
                ->setParameter('idl', $id)
                ->setMaxResults(1)
                ->getQuery()
                ->getScalarResult();
            if (!$row) {
                return $matches[2];
            }
            return '<a href="/row/' . $row[0]['r_id'] . '">' . $matches[2] . '</a>';
        }, $text);

        // ссылка на комплект
        $text = preg_replace('/<link to="(.*)">([^<]*)<\/link>/iU', '<a href="/complect/$1">$2</a>', $text);

        return $text;
    }

    public function xml2html($text, $row_id = null)
    {
        $text = $this->replaceLinks($text);
        $text = preg_replace('/(allowfullscreen)([^=])/iU', 'allowfullscreen="allowfullscreen" $2', $text); // youtube
        $text = $this->replaceLinks($text);

        $xml = new SimpleXMLElement($text);

        $html = '';

        foreach ($xml as $block) {
            $block_title = '';
            $block_content = '';
            $right_link_group = '';
            $right_publication = '';

            if ($block->attributes()->title and !$block->order and !$block->usage) {
                $block_title = '<h2><span>' . Typograf::process($block->attributes()->title) . '</span></h2>' . PHP_EOL;
            }

            foreach ($block as $k => $v) {
                if ($k == 'gallery') {
                    $block_content .= Typograf::process(XmlRenderer::renderGallerySmipro($v)) . PHP_EOL;
                } elseif ($k == 'video') {
                    $block_content .= Typograf::process(XmlRenderer::renderVideo2($v)) . PHP_EOL;
                } elseif ($k == 'spec') {
                    $block_content .= Typograf::process(XmlRenderer::renderSpec($v)) . PHP_EOL;
                } elseif ($k == 'consist') {
                    $block_content .= Typograf::process(XmlRenderer::renderConsist($v)) . PHP_EOL;
                } elseif ($row_id and $k == 'usage') {
                    $usage = $this->em->createQueryBuilder()
                        ->select('v.id', 'v.name', 'v.complectId')
                        ->from('App:VariantsRows', 'vr')
                        ->leftJoin('vr.variant', 'v', 'WITH', 'v.id = vr.variantId')
                        ->where('vr.rowId = :row_id')
                        ->andWhere('v.visible', 1)
                        ->setParameter('row_id', $row_id)
                        ->getQuery()
                        ->getResult();
                    $block_content .= Typograf::process(XmlRenderer::renderUsage($v, $usage)) . PHP_EOL;
//                } elseif ($k == 'order') {
/*                    $block_content .= '<?php include_partial("order", array("form" => $form, "params" => $params)) ?>' . PHP_EOL;*/
                } elseif ($k == 'right') {
                    continue;
                } elseif ($k == 'publication') {
                    $right_publication .= Typograf::process(XmlRenderer::renderPublication($v));
                } elseif ($k == 'right_link_group') {
//                    $right_link_group .= Typograf::process(XmlRenderer::renderRightLinkGroup($v));
                } elseif ($k == 'note') {
                    $block_content .= str_replace('</note>',
                        '</p>',
                        str_replace('<note>',
                            '<p class="note">',
                            Typograf::process($v->saveXML())));
                } elseif ($k == 'p') {
                    $block_content .= Typograf::process($v->saveXML()) . PHP_EOL;
                } else {
                    $block_content .= preg_replace('/\&amp;nbsp;/',
                        '&nbsp;',
                        Typograf::process($v->saveXML()) . PHP_EOL);
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


    public
    function makeFile($name, $tpl_dir, $html_dir, $row_id = null)
    {
        $text = file_get_contents($tpl_dir . '/' . $name . '.xml');
        $html = $this->xml2html($text, $row_id);
        file_put_contents($html_dir . '/' . $name . '.htm', $html);
    }


    public
    function hideEntities($data)
    {
        return str_replace("&", "&amp;", $data);
    }

}