<?php

namespace App\Controller;

use App\Entity\Groups;
use App\Entity\OrdersRows;
use App\Entity\Rows;
use App\Form\RowType;
use App\Services\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RowsController extends AbstractController
{
    private $entitymanager;

    private $orderService;

    public function __construct(EntityManagerInterface $entityManager, OrderService $orderService)
    {
        $this->entityManager = $entityManager;
        $this->orderService = $orderService;
    }

    #[Route('/row/{rowId}', name: 'app_row_show', methods: ['GET'])]
    #[Route('/row/{rowId}/{groupId}', name: 'app_row_show_group', methods: ['GET'])]
    public function show(string $rowId, ?string $groupId, Request $request): Response
    {
        $row = $this->entityManager->getRepository(Rows::class)->find($rowId);

        if (!$row) {
            $this->addFlash('notice', 'Запчасть не найдена.');
            throw $this->createNotFoundException();
        }
        $group = null;
        $leftMenu = [];
        if ($groupId) {
            $group = $this->entityManager->getRepository(Groups::class)->find($groupId);
            $qb = $this->entityManager->createQueryBuilder()
                ->select('r.id', 'r.name', 'r.price', 'r.visible', 'r.fixed')
                ->from(Rows::class, 'r')
                ->leftJoin('App:VariantsRows', 'vr', 'WITH', 'vr.rowId = r.id')
                ->leftJoin('App:Variants', 'v', 'WITH', 'v.id = vr.variantId')
                ->leftJoin('App:GroupsComplects', 'gc', 'WITH', 'gc.complectId = v.complectId')
                ->leftJoin('App:Groups', 'g', 'WITH', 'g.id = gc.groupId')
                ->where('gc.groupId = :group_id')
                ->andWhere('r.visible = :visible')
                ->andWhere('r.fixed = :fixed')
                ->orderBy('r.price', 'DESC')
                ->distinct()
                ->setParameter('group_id', $groupId)
                ->setParameter('visible', 1)
                ->setParameter('fixed', 0);
            $rows = $qb->getQuery()->getResult();
            foreach ($rows as $rowItem) {
                $leftMenu[] = $rowItem;
            }
        }

        $form = $this->createForm(RowType::class, $row);

        $minPrice = $row->getPrice();


        return $this->render('rows/show.html.twig', [
            'row' => $row,
            'form' => $form->createView(),
            'minPrice' => $minPrice,
            'params' => [
                'row' => $row,
                'curr_group' => $groupId,
            ],
            'group' => $group,
            'leftMenu' => $leftMenu,
        ]);
    }

    #[Route('/row/order', name: 'app_row_order', methods: ['POST'])]
    public function order(Request $request)
    {
        $formData = $request->request->all();
        $row = $this->entityManager->getRepository(Rows::class)->find($formData['row']['id']);
        $orderId = $request->cookies->get('order_id');

        $form = $this->createForm(RowType::class, $row);

        $form->handleRequest($request);
        $response = ['submit' => false];
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $orderRow = null;
                if ($orderId) {
                    $orderRow = $this->entityManager->getRepository(OrdersRows::class)->findOneBy([
                        'orderId' => $orderId,
                        'rowId' => $row->getId(),
                        'type' => $formData['row'][0]['type'] ?? null,
                    ]);
                }
                if ($orderRow) {
                    $orderRow->setCnt($orderRow->getCnt() + $formData['row']['count']);
                    $this->entityManager->persist($orderRow);
                    $this->entityManager->flush();
                } else {
                    $order = $this->orderService->execute($request, $formData, $orderId, 'row');
                    $orderId = $order->getId();
                }
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
        }
        return new JsonResponse($response);
    }


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
