<?php

namespace App\Command;

use App\Entity\Complects;
use App\Entity\Groups;
use App\Entity\GroupsComplects;
use App\Entity\Rows;
use App\Entity\Variants;
use App\Entity\VariantsRows;
use App\Services\ImportHelper;
use App\Services\XmlRenderer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SimpleXMLElement;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'import2',
    description: 'Add a short description for your command',
)]
class Import2Command extends Command
{

    private static string $dataDir;

    private static string $tplDir;

    private static string $htmlDir;

    public function __construct(
        private EntityManagerInterface $em,
        private ImportHelper           $helper,
        private ParameterBagInterface  $parameterBag
    )
    {
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        self::$dataDir = $projectDir . '/templates/data';
        self::$tplDir = $projectDir . '/templates/data/templates';
        self::$htmlDir = $projectDir . '/templates/html';
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $group_tpl_dir = self::$tplDir . '/groups';
        $complect_tpl_dir = self::$tplDir . '/complects';
        $row_tpl_dir = self::$tplDir . '/rows';

        $complect_html_dir = self::$htmlDir . '/complects';
        $row_html_dir = self::$htmlDir . '/rows';


        $group_xml = new SimpleXMLElement(file_get_contents(self::$dataDir . '/groups.xml'));
        $complect_xml = new SimpleXMLElement(file_get_contents(self::$dataDir . '/complects.xml'));
        $row_xml = new SimpleXMLElement(file_get_contents(self::$dataDir . '/rows.xml'));

        $errors = array();
        $groups = array();
        foreach ($group_xml->group as $item) {
            $gattr = $item->attributes();
            $new = array(
                'id' => $gattr->id->__toString(),
                'name' => $gattr->name->__toString(),
                'fullname' => $gattr->fullname->__toString(),
                'position' => isset($gattr->order) ? ($gattr->order + 0) : 0,
                'visible' => isset($gattr->visible) ? ($gattr->visible + 0) : 1,
                'cnt' => 0,
                'file' => isset($gattr->file) ? $gattr->file->__toString() : '',
                'news' => isset($gattr->news) ? intval($gattr->news->__toString()) : 0,
                'complects' => array(),
            );
            if ($new['file'] and !file_exists($group_tpl_dir . '/' . $new['file'] . '.xml')) {
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
            } elseif (!file_exists($complect_tpl_dir . '/' . $new['file'] . '.xml')) {
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
                        'position' => isset($vattr->order) ? $vattr->order + 0 : 0,
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
                if ($new['file'] and !file_exists($row_tpl_dir . '/' . $new['file'] . '.xml')) {
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

        if ($errors) {
            foreach ($errors as $e) {
                $output->writeln('ERROR: ' . $e);
            }
            return Command::FAILURE;
        }
        if (1) {
            $connection = $this->em->getConnection();

            $connection->executeQuery('UPDATE `groups` SET visible = 0');
            $connection->executeQuery('TRUNCATE groups_complects');


            foreach ($groups as $g) {
                $id = $g['id'];
                $name = $g['name'];
                $fullname = $g['fullname'];
                $file = $g['file'];
                $position = $g['position'];
                $visible = $g['visible'];
                $news_id = $g['news'];

                $existingGroup = $this->em->getRepository(Groups::class)->find($id);

                if ($existingGroup) {
                    $existingGroup->setName($name);
                    $existingGroup->setFullname($fullname);
                    $existingGroup->setPosition($position);
                    $existingGroup->setVisible($visible);
                    $existingGroup->setFile($file);
                    $existingGroup->setNewsId($news_id);

                    $this->em->persist($existingGroup);
                } else {
                    $newGroup = new Groups();
                    $newGroup->setId($id);
                    $newGroup->setName($name);
                    $newGroup->setFullname($fullname);
                    $newGroup->setPosition($position);
                    $newGroup->setVisible($visible);
                    $newGroup->setFile($file);
                    $newGroup->setNewsId($news_id);

                    $this->em->persist($newGroup);
                }

                foreach ($g['complects'] as $c) {
                    $c_id = $c['id'];
                    $c_order = $c['order'];

                    $groupComplect = new GroupsComplects();
                    $groupComplect->setGroup($existingGroup ?? $newGroup);
                    $groupComplect->setComplectId($c_id);
                    $groupComplect->setGroupId($g['id']);
                    $groupComplect->setPosition($c_order);

                    $this->em->persist($groupComplect);
                }
            }

            $connection->executeQuery('UPDATE `complects` SET visible = 0');
            $connection->executeQuery('UPDATE `variants` SET visible = 0');
            foreach ($complects as $c) {
                $id = $c['id'];
                $name = $c['name'];
                $img = $c['img'];
                $description = $c['description'];
                $tag = $c['tag'];
                $tag_end = new \DateTime($c['tag_end']);
                $file = $c['file'];
                $visible = $c['visible'];

                $existingComplect = $this->em->getRepository(Complects::class)->find($id);

                if ($existingComplect) {
                    $existingComplect->setName($name);
                    $existingComplect->setImg($img);
                    $existingComplect->setDescription($description);
                    $existingComplect->setTag($tag);
                    $existingComplect->setTagEnd($tag_end);
                    $existingComplect->setFile($file);
                    $existingComplect->setVisible($visible);

                    $this->em->persist($existingComplect);
                } else {
                    $newComplect = new Complects();
                    $newComplect->setId($id);
                    $newComplect->setName($name);
                    $newComplect->setImg($img);
                    $newComplect->setDescription($description);
                    $newComplect->setTag($tag);
                    $newComplect->setTagEnd($tag_end);
                    $newComplect->setFile($file);
                    $newComplect->setVisible($visible);

                    $this->em->persist($newComplect);

                }

                foreach ($c['variants'] as $v) {

                    $v_id = $v['id'];
                    $v_name = $v['name'];
                    $v_price_base = $v['price_base'];
                    $v_is_base = $v['is_base'];
                    $v_visible = $v['visible'];
                    $v_position = $v['position'];
                    $v_description = $v['description'];

                    $existingVariant = $this->em->getRepository(Variants::class)->find($v_id);

                    if ($existingVariant) {
                        $existingVariant->setName($v_name);
                        $existingVariant->setPriceBase($v_price_base);
                        $existingVariant->setIsBase($v_is_base);
                        $existingVariant->setVisible($v_visible);
                        $existingVariant->setPosition($v_position);
                        $existingVariant->setDescription($v_description);

                        $this->em->persist($existingVariant);
                    } else {
                        $newVariant = new Variants();
                        $newVariant->setId($v_id);
                        $newVariant->setName($v_name);
                        $newVariant->setPriceBase($v_price_base);
                        $newVariant->setIsBase($v_is_base);
                        $newVariant->setVisible($v_visible);
                        $newVariant->setPosition($v_position);
                        $newVariant->setDescription($v_description);

                        $this->em->persist($newVariant);

                    }


                }


            }
            $connection->executeQuery('UPDATE `rows_t` SET visible=0');
            $connection->executeQuery('TRUNCATE variants_rows');

            foreach ($rows as $r) {
                $id = $r['id'];
                $idk = $r['idk'];
                $idl = $r['idl'];
                $name = $r['name'];
                $price = $r['price'];
                $file = $r['file'];
                $fixed = $r['fixed'];
                $visible = $r['visible'];
                $variants = $r['variants'];

                $existingRow = $this->em->getRepository(Rows::class)->find($id);

                if ($existingRow) {
                    $existingRow->setIdk($idk);
                    $existingRow->setIdl($idl);
                    $existingRow->setName($name);
                    $existingRow->setPrice($price);
                    $existingRow->setFile($file);
                    $existingRow->setFixed($fixed);
                    $existingRow->setVisible($visible);

                    $this->em->persist($existingRow);
                } else {
                    $newRow = new Rows();
                    $newRow->setId($id);
                    $newRow->setIdk($idk);
                    $newRow->setIdl($idl);
                    $newRow->setName($name);
                    $newRow->setPrice($price);
                    $newRow->setFile($file);
                    $newRow->setFixed($fixed);
                    $newRow->setVisible($visible);

                    $this->em->persist($newRow);
                }

                foreach ($variants as $v) {
                    $v_id = $v['id'];
                    $v_cnt = $v['cnt'];
                    $v_is_base = $v['is_base'];
                    $v_position = $v['order'];
                    $variant = $this->em->getRepository(Variants::class)->find($v_id);

                    $variantRow = new VariantsRows();
                    $variantRow->setRow($existingRow ?? $newRow);
                    $variantRow->setVariant($variant);
                    $variantRow->setVariantId($v_id);
                    $variantRow->setRowId($r['id']);
                    $variantRow->setCnt($v_cnt);
                    $variantRow->setIsBase($v_is_base);
                    $variantRow->setPosition($v_position);

                    $this->em->persist($variantRow);


                }
                $this->em->flush();
            }

            foreach ($groups as $g) {
                $res = $this->em->createQueryBuilder()
                    ->select('COUNT(gc) AS cnt')
                    ->from('App:GroupsComplects', 'gc')
                    ->leftJoin('App:Groups', 'g', 'WITH', 'g.id = gc.groupId')
                    ->where('gc.groupId = :group_id')
                    ->andWhere('g.visible = 1')
                    ->setParameter('group_id', $g['id'])
                    ->getQuery()
                    ->getResult();

                $cnt = isset($res[0]['cnt']) ? $res[0]['cnt'] : 0;

                $res = $this->em->createQueryBuilder()
                    ->select('DISTINCT r.id')
                    ->from('App:GroupsComplects', 'gc')
                    ->leftJoin('App:Groups', 'g', 'WITH', 'g.id = gc.groupId')
                    ->leftJoin('App:Complects', 'c', 'WITH', 'c.id = gc.complectId')
                    ->leftJoin('App:Variants', 'v', 'WITH', 'v.complectId = c.id')
                    ->leftJoin('App:VariantsRows', 'vr', 'WITH', 'vr.variantId = v.id')->leftJoin('vr.row', 'r')
                    ->where('gc.groupId = :group_id')
                    ->andWhere('r.fixed = 0')
                    ->andWhere('r.visible = 1')
                    ->andWhere('g.visible = 1')
                    ->setParameter('group_id', $g['id'])
                    ->getQuery()
                    ->getResult();
                $cnt += count($res);

                if ($res) {
                    $this->em->createQueryBuilder()
                        ->update('App:Groups', 'g')
                        ->set('g.cnt', ':cnt')
                        ->where('g.id = :group_id')
                        ->setParameter('group_id', $g['id'])
                        ->setParameter('cnt', $cnt)
                        ->getQuery()
                        ->execute();
                }

            }

            $variants = $this->em->getRepository(Variants::class)->findAll();
            foreach ($variants as $v) {
                $res = $this->em->createQueryBuilder()
                    ->select('SUM(r.price*vr.cnt) AS total')
                    ->from('App:Variants', 'v')
                    ->leftJoin('App:VariantsRows', 'vr', 'WITH', 'vr.variantId = v.id')
                    ->leftJoin('App:Rows', 'r', 'WITH', 'r.id = vr.rowId')
                    ->where('v.id = :variant_id')
                    ->andWhere('vr.isBase = 1')
                    ->setParameter('variant_id', $v->getId())
                    ->getQuery()
                    ->getResult();
                $res[0]['total'] += 0;
                $v->setPriceBase($res[0]['total']);
                $this->em->persist($v);

            }

            $this->em->flush();

        }
        $gallery_height = 150;
        $right_width = 200;

        /* конвертация картинок для списка комплектов */
        foreach ($complects as $c) {
            XmlRenderer::convertImg($c['img'], 'devices');
        }

        /* описание групп */
        $output->writeln("сохранить html групп:\n");
        foreach ($groups as $g) {
            if ($g['file']) {
                $group_html_dir = self::$htmlDir . '/groups';
                $this->helper->makeFile($g['file'], $group_tpl_dir, $group_html_dir);
                $output->writeln("  " . $g['file'] . ".htm\n");
            }
        }
        $output->writeln("\n");

        /* страницы комплектов */
        $output->writeln("сохранить html комплетов:\n");
        foreach ($complects as $c) {
            $this->helper->makeFile($c['file'], $complect_tpl_dir, $complect_html_dir);
            $output->writeln("  " . $c['file'] . ".htm\n");
        }
        $output->writeln("\n");

        /* страницы запчастей */
//        $output->writeln("сохранить html запчастей:\n");
//        foreach ($rows as $r) {
//            if ($r['file']) {
//                $this->helper->makeFile($r['file'], $row_tpl_dir, $row_html_dir, $r['id']);
//                $output->writeln("  " . $r['file'] . ".htm\n");
//            }
//        }
        $output->writeln("\n");
        return Command::SUCCESS;

    }
}




