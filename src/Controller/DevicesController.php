<?php

namespace App\Controller;

use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DevicesController extends BaseController
{
    #[Route('/', name: 'app_devices')]
    public function index(EntityManagerInterface $entityManager): Response
    {

        $qb = $entityManager->createQueryBuilder()
            ->select('g')
            ->from('App:Groups', 'g')
            ->where('g.visible = 1')
            ->orderBy('g.position', 'ASC');
        $groups = $qb->getQuery()->getResult();
//
//        $qb>select('n')
//        ->from('App:News', 'n')
//            ->orderBy('n.date', 'DESC')
//            ->orderBy('n.news_order', 'DESC')
//            ->setMaxResults(10);
//        $news = $qb>getQuery()->getResult();

        return $this->render('devices/index.html.twig', [
            'groups' => $groups,
//            'news' => $news,
        ]);

    }

    #[Route('/devices/{groupId}', name: 'app_devices_show')]
    public function show(string $groupId, EntityManagerInterface $entityManager): Response
    {

        $qb = $entityManager->createQueryBuilder()
            ->select('g')
            ->from('App:Groups', 'g')
            ->where('g.id = :id')
            ->setParameter('id', $groupId)
            ->orderBy('g.position', 'ASC');
        $group = $qb->getQuery()->getSingleResult();

        $qb = $entityManager->createQueryBuilder()
            ->select('c.id', 'c.name', 'c.img', 'c.description', 'c.tag', 'c.tagEnd', 'c.visible', 'c.file', 'v.priceBase')
            ->from('App:GroupsComplects', 'gc')
            ->leftJoin('App:Complects', 'c', 'WITH', 'gc.complectId = c.id')
            ->leftJoin('App:Variants', 'v', 'WITH', 'v.complectId = c.id AND v.isBase = 1 AND v.visible = 1')
            ->where('gc.groupId = :group_id')
            ->andWhere('c.visible = 1')
            ->setParameter('group_id', $groupId)
            ->orderBy('gc.position');
        $complects = $qb->getQuery()->getResult();

        foreach ($complects as $k => $complect) {
            if ($complect['tag'] and $complect['tagEnd'] > date('Y-m-d')) {
                $complects[$k]['tag_show'] = true;
            } else {
                $complects[$k]['tag_show'] = false;
            }
            $complects[$k]['url'] = $this->generateUrl('app_complect_group', array(
                'id' => $complect['id'], 'groupId' => $groupId));
            $complects[$k]['img'] = preg_replace('/^([^\.]+)\.(.+)$/', '$1.$2', $complect['img']);
        }


        $qb = $entityManager->createQueryBuilder()
            ->select('r')
            ->from('App:Rows', 'r')
            ->leftJoin('App:VariantsRows', 'vr', 'WITH', 'r.id = vr.rowId')
            ->leftJoin('App:Variants', 'v', 'WITH', 'vr.variantId = v.id')
            ->leftJoin('App:Complects', 'c', 'WITH', 'v.complectId = c.id')
            ->leftJoin('App:GroupsComplects', 'gc', 'WITH', 'gc.complectId = c.id')
            ->where('gc.groupId = :group_id')
            ->andWhere('r.visible = 1')
            ->andWhere('r.fixed = 0')
            ->orderBy('r.price', 'DESC')
            ->distinct()
            ->setParameter('group_id', $groupId);
        $rows = $qb->getQuery()->getResult();

//        $qb = $entityManager->createQueryBuilder()
//            ->select('n')
//            ->from('App:News', 'n')
//            ->where('n.groupId = :group_id')
//            ->orderBy('n.date', 'DESC')
//            ->addOrderBy('n.newsOrder', 'DESC')
//            ->setMaxResults(1)
//            ->setParameter('group_id', $groupId);
//
//        $lastNews = $qb->getQuery()->getOneOrNullResult();
//
//        if (!$lastNews) {
//            $qb = $entityManager->createQueryBuilder()
//                ->select('n')
//                ->from('App:News', 'n')
//                ->orderBy('n.date', 'DESC')
//                ->addOrderBy('n.newsOrder', 'DESC')
//                ->setMaxResults(1);
//
//            $lastNews = $qb->getQuery()->getOneOrNullResult();
//        }

//        $groupNews = null;
//
//        if ($group && $group->getNewsId()) {
//            $groupNews = $entityManager->getRepository(News::class)->find($group->getNewsId());
//        }
        return $this->render('devices/show.html.twig', [
            'group' => $group,
            'complects' => $complects,
            'rows' => $rows,
//            'news' => $news,
        ]);

    }

}



