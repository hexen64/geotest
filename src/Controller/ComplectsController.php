<?php

namespace App\Controller;

use App\Entity\Complects;
use App\Entity\Groups;
use App\Entity\Orders;
use App\Entity\OrdersVariants;
use App\Entity\OrdersVariantsRows;
use App\Entity\VariantsRows;
use App\Form\VariantsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComplectsController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/complect/{id}/{groupId}', name: 'app_complect_group', methods: ['GET'])]
    #[Route('/complect/{id}', name: 'app_complect', methods: ['GET'])]
    public function show(string  $id, ?string $groupId = null,
                         Request $request, FormFactoryInterface $formFactory): Response
    {
//        $request = $requestStack->getCurrentRequest();
        $complect = $this->entityManager->getRepository(Complects::class)->find($id);

        //        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Design'));
//        define('COMPLECT_NAME', $complect->getName() . (($complect->getTag() and $complect->getTagEnd() > date('Y-m-d')) ? '<span class="tag">' . $complect->getTag() . '</span>' : ''));
        $qb = $this->entityManager->createQueryBuilder();
        $res = null;
        if (!$groupId) {
            $qb->select('gc.groupId')
                ->from('App:GroupsComplects', 'gc')
                ->where('gc.complectId = :complect_id')
                ->setParameter('complect_id', $id)
                ->orderBy('gc.position', 'ASC');
            $res = $qb->getQuery()->getOneOrNullResult();
            $groupId = $res !== null ? $res['groupId'] : null;
        }
        $group = $this->entityManager->getRepository(Groups::class)->find($groupId);

        $qb = $this->entityManager->createQueryBuilder()
            ->select('v')
            ->from('App:Variants', 'v')
            ->where('v.complectId = :complect_id')
            ->andWhere('v.visible = :visible')
            ->setParameter('complect_id', $id)
            ->setParameter('visible', 1)
            ->orderBy('v.isBase', 'DESC');
        $variants = $qb->getQuery()->getResult();

        $params = ['curr_group' => $groupId, 'active' => ''];
        $min_price = 0;
        $forms = [];
        foreach ($variants as $index => $variant) {
            $status = '';
            $baseRows = $this->entityManager->getRepository(VariantsRows::class)->findBy(['variant' => $variant, 'isBase' => 1]);
            $notBaseRows = $this->entityManager->getRepository(VariantsRows::class)->findBy(['variant' => $variant, 'isBase' => 0]);
            $form = $formFactory->createNamed("variant", VariantsType::class, $variant, [
                'base_rows' => $baseRows,
                'not_base_rows' => $notBaseRows
            ]);

            $forms[] = $form->createView();
            $totalPrice = $this->totalPrice($form);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                dd($form);
            }

            $params['variants'][] = [
                'entity' => $variant,
                'variant_price' => $totalPrice,
//                'carving' => u::variantCarving($id), // Replace with appropriate logic
                'is_base' => $variant->getIsBase(),
                'status' => $status,
            ];

            $price = $totalPrice * $form->get('count')->getData();

            if (!$min_price) {
                $min_price = $price;
            } elseif ($price < $min_price) {
                $min_price = $price;
            }
        }

        $left_menu = [];
        $qb = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from('App:Complects', 'c')
            ->join('App:GroupsComplects', 'gc')
            ->where('gc.groupId = :group_id')
            ->andWhere('c.visible = :visible')
            ->setParameter('group_id', $groupId)
            ->setParameter('visible', 1)
            ->orderBy('gc.position', 'ASC');
        $complects = $qb->getQuery()->getResult();

        foreach ($complects as $complectRow) {
            $leftMenu[] = $complectRow;
        }
        $rowsCount = 0;

        if ($groupId) {
            $qb = $this->entityManager->createQueryBuilder()
                ->select('r')
                ->from('App:Rows', 'r')
                ->leftJoin('App:VariantsRows', 'vr', 'WITH', 'vr.rowId = r.id')
                ->leftJoin('App:Variants', 'v', 'WITH', 'v.id = vr.variantId')
                ->leftJoin('App:GroupsComplects', 'gc', 'WITH', 'gc.complectId = v.complectId')
                ->where('gc.groupId = :group_id')
                ->andWhere('r.visible = :visible')
                ->andWhere('r.fixed = :fixed')
                ->setParameter('visible', 1)
                ->setParameter('fixed', 0)
                ->setParameter('group_id', $groupId)
                ->orderBy('r.price', 'desc')
                ->distinct(true);
            $rows = $qb->getQuery()->getResult();
            $rowsCount = count($rows);
        }

        return $this->render('complects/show.html.twig', [
            'form' => $forms,
            'complect' => $complect,
            'params' => $params,
            'group' => $group,
            'min_price' => $min_price,
            'rows_count' => $rowsCount,
        ]);
    }

    #[Route('/complect/order', name: 'app_complect_order', methods: ['POST'])]
    public function orderAction(Request $request): Response
    {

        $fields = $request->request->all();
        $orderId = $this->addToOrder($request, $fields['variant']);
        $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
        $qb = $this->entityManager->createQueryBuilder()
            ->select('SUM(ov.cnt) as count')
            ->from('App:OrdersVariants', 'ov')
            ->where('ov.variantId = :variantId')
            ->andWhere('ov.orderId = :orderId')
            ->setParameter('variantId', $fields['variant']['variant_id'])
            ->setParameter('orderId', $orderId)
            ->groupBy('ov.variantId')
            ->addGroupBy('ov.orderId');

        $row = $qb->getQuery()->getOneOrNullResult();

        $response = [
            'order_id' => $order->getId(),
            'order_total' => format_rub($order->getTotal()),
            'status' => order_status($orderId, $row['count'])
        ];

        $json = new JsonResponse($response);


        return $this->redirectToRoute('app_order', [
            'orderId' => $orderId
        ]);

    }

    public function addToOrder(Request $request, array $fields): int
    {
        $order = new Orders();

        $this->entityManager->persist($order);
        $this->entityManager->flush();
        dd($order);
        $orderId = $order->getId();
        $total = 0;
        $rows = [];
        foreach ($fields['base'] as $key => $val) {
            array_key_exists('row', $val) ? $total += $val['row']['price'] * $val['cnt'] : $total += $val['price'] * $val['cnt'];
            $rows[$key] = $val['row'];
        }

        if (isset($fields['not_base'])) {
            foreach ($fields['not_base'] as $key => $val) {
                $total += $val['row']['price'] * $val['cnt'];
                $rows[$key] = $val['row'];
            }
        }

        $total *= $fields['count'];

        $order->setTotal($order->getTotal() + $total);
        $this->entityManager->flush();

        $is_base = true;

        $rows = $this->entityManager->getRepository(VariantsRows::class)
            ->findBy(['variantId' => $fields['variant_id']]);


        foreach (array_merge($fields['base'], $fields['not_base']) as $field) {
            if (isset($field['row'])) {
                $formRows['rows'][$field['row']['id']] = ['cnt' => $field['cnt']];
            }
        }

        foreach ($rows as $row) {
            if (isset($formRows['rows'][$row->getRowId()]) && $formRows['rows'][$row->getRowId()]['cnt'] !== $row->getCnt()) {
                $is_base = false;
                break;
            }
        }

        $variant = null;

        if ($is_base) {
            $variant = $this->entityManager->getRepository(OrdersVariants::class)
                ->findOneBy([
                    'orderId' => $orderId,
                    'variantId' => $fields['id'],
                    'type' => $fields['type'] ?? null,
                    'isBase' => true,
                ]);
        }

        if ($variant) {
            $variant->setCnt($variant->getCnt() + $fields['count']);
            $this->entityManager->flush();
        } else {
            $variant = new OrdersVariants();
            $variant->setOrderId($orderId);
            $variant->setVariantId($fields['variant_id']);
            $variant->setIsBase($is_base);
            $variant->setCnt($fields['count']);
            $variant->setType($fields['type'] ?? null);
            $this->entityManager->persist($variant);
            $this->entityManager->flush();
        }

        if (!$is_base) {
            foreach ($rows as $row) {
                $rowId = $row->getRowId();
                $rowCount = $formRows['rows'][$rowId]['cnt'];
                $rowCountDiff = $rowCount - $row->getCnt();

                if ($rowCountDiff !== 0) {
                    $variantRow = new OrdersVariantsRows();
                    $variantRow->setOrderVariantId($variant->getId());
                    $variantRow->setRowId($rowId);
                    $variantRow->setDiff($rowCountDiff);
                    $this->entityManager->persist($variantRow);
                }
            }

            $this->entityManager->flush();
        }

        return $orderId;
    }


    public
    function totalPrice(Form $form): int
    {
        $total = 0;
        if (isset($form['base']) and is_array($form['base']->getData())) {
            foreach ($form['base']->getData() as $val) {
                if (!$val->getCnt()) continue;
                $total += $val->getCnt() * $val->getRow()->getPrice();
            }
        }
        if (isset($form['not_base']) and is_array($form['not_base']->getData())) {
            foreach ($form['not_base']->getData() as $val) {
                if (!$val->getCnt()) continue;
                $total += $val->getCnt() * $val->getRow()->getPrice();
            }
        }
        return $total;
    }
}
