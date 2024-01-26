<?php

namespace App\Controller;

use App\DTO\OrdersVariantsDTO;
use App\Entity\Complects;
use App\Entity\Groups;
use App\Entity\Orders;
use App\Entity\OrdersVariants;
use App\Entity\OrdersVariantsRows;
use App\Entity\Rows;
use App\Entity\Variants;
use App\Entity\VariantsRows;
use App\Form\VariantsType;
use App\Services\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ComplectsController extends AbstractController
{

    private $entityManager;
    private $orderService;

    public function __construct(EntityManagerInterface $entityManager, OrderService $orderService)
    {
        $this->entityManager = $entityManager;
        $this->orderService = $orderService;
    }

    #[Route('/complect/{id}/{groupId}', name: 'app_complect_group', methods: ['GET'])]
    #[Route('/complect/{id}', name: 'app_complect', methods: ['GET'])]
    public function show(string $id, ?string $groupId, FormFactoryInterface $formFactory): Response
    {
        $complect = $this->entityManager->getRepository(Complects::class)->find($id);
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

            $totalPrice = $this->totalPrice($form);
            $variantCount = $form->get('cnt')->getData();

            $forms[] = $form->createView();

            $price = $totalPrice * $variantCount;

            $params['variants'][] = [
                'entity' => $variant,
                'variant_price' => $totalPrice,
                'is_base' => $variant->getIsBase(),
                'status' => $status,
                'variant_count' => $variantCount
            ];

            if (!$min_price) {
                $min_price = $price;
            } elseif ($price < $min_price) {
                $min_price = $price;
            }
        }

        $leftMenu = [];
        $qb = $this->entityManager->createQueryBuilder()
            ->select('c.id', 'c.name')
            ->from('App:Complects', 'c')
            ->leftJoin('App:GroupsComplects', 'gc', 'WITH', 'gc.complectId = c.id')
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
            'left_menu' => $leftMenu,
        ]);
    }

    #[Route('/complect/order', name: 'app_complect_order', methods: ['POST'])]
    public function order(Request $request, FormFactoryInterface $formFactory, ValidatorInterface $validator): JsonResponse
    {
        $formData = $request->request->all();
        $variant = $this->entityManager->getRepository(Variants::class)->find($formData['variant']['id']);
        $baseRows = $this->entityManager->getRepository(VariantsRows::class)->findBy(['variant' => $variant, 'isBase' => 1]);
        $notBaseRows = $this->entityManager->getRepository(VariantsRows::class)->findBy(['variant' => $variant, 'isBase' => 0]);

        $form = $formFactory->createNamed("variant", VariantsType::class, $variant, [
            'base_rows' => $baseRows,
            'not_base_rows' => $notBaseRows
        ]);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $orderId = $this->addToOrder($request, $formData['variant']);
            $response = [
                'success' => true,
                'error' => null,
                'result' => [
                    'order_id' => $orderId,
//                            'order_total' => format_rub($order->getTotal()),
//                            'status' => order_status($orderId, $row['count'])
                ]
            ];
        } else {
            $response = [
                'success' => false,
                'error' => true,
                'result' => [
                    'errors' => [
                        'form' => $this->getErrorMessages($form)
                    ]
                ]
            ];

        }
        return new JsonResponse($response);
    }


    public
    function addToOrder(Request $request, array $formData): int
    {
        $orderId = $request->cookies->get('order_id');
        $orderVariant = null;
        empty($this->orderService->calculateDiff($formData)) ? $baseForm = true : $baseForm = false;
        if ($orderId && $baseForm) {
            $orderVariant = $this->entityManager->getRepository(OrdersVariants::class)
                ->findOneBy([
                    'orderId' => $orderId,
                    'variantId' => $formData['id'],
                    'type' => $formData[0]['type'] ?? null,
                ]);
        }
        if ($orderVariant) {
            $orderVariant->setCnt($orderVariant->getCnt() + $formData['cnt']);
            $this->entityManager->persist($orderVariant);
            $this->entityManager->flush();
        } else {
            $order = $this->orderService->execute($request, $formData, $orderId, 'variant');
            $orderId = $order->getId();
        }
        return $orderId;
    }


    public
    function totalPrice(FormInterface $form): int
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

// Generate an array contains a key -> value with the errors where the key is the name of the form field
    protected
    function getErrorMessages(FormInterface $form, $prefix = '')
    {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}
