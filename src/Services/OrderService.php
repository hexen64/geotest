<?php

namespace App\Services;

use App\Entity\Orders;
use App\Entity\OrdersRows;
use App\Entity\OrdersVariants;
use App\Entity\OrdersVariantsRows;
use App\Entity\Rows;
use App\Entity\Variants;
use App\Entity\VariantsRows;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class OrderService
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

    }


    public function execute(Request $request, array $data, ?int $orderId, string $type)
    {
        if ($orderId) {
            $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
        } else {
            $order = new Orders();
        }

        $total = $this->calculateTotal($data, $type);

        if ($type === 'variant') {
            $orderVariant = new OrdersVariants();
            $orderVariant->setPrice($total);
            $orderVariant->setIsBase(true);
            $variant = $this->entityManager->getRepository(Variants::class)->find($data['id']);
            $orderVariant->setVariant($variant);
            $diffs = $this->calculateDiff($data);
            if (!empty($diffs)) {
                $orderVariant = $this->modifyOrderVariant($orderVariant, $diffs);
            }
            $orderVariant->setCnt($data['cnt']);
            $orderVariant->setType($data[0]['type'] ?? null);
            $orderVariant->setName();
            $total *= $data['cnt'];
            if ($order->getTotal()) {
                $order->setTotal($order->getTotal() + $total);
            } else {
                $order->setTotal($total);
            }
            $order->addOrdersVariants($orderVariant);

        } elseif ($type === 'row') {
            $orderRow = new OrdersRows();
            $orderRow->setPrice($total);
            $row = $this->entityManager->getRepository(Rows::class)->find($data['row']['id']);
            $orderRow->setRow($row);
            $orderRow->setCnt($data['row']['count']);
            $orderRow->setType($data['row'][0]['type'] ?? null);
            $orderRow->setName();
            $order->addOrdersRows($orderRow);
            $order->setTotal($total);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $request->cookies->set('order_id', $order->getId());
        return $order;
    }


    public function calculateTotal(array $data, string $type)
    {
        $total = 0;
        $rows = [];

        if ($type === 'variant') {
            foreach ($data['base'] as $key => $val) {
                array_key_exists('row', $val) ? $total += $val['row']['price'] * $val['cnt'] : $total += $val['price'] * $val['cnt'];
                $rows[$key] = $val['row'];
            }

            if (isset($data['not_base'])) {
                foreach ($data['not_base'] as $key => $val) {
                    $total += $val['row']['price'] * $val['cnt'];
                    $rows[$key] = $val['row'];
                }
            }
        } else {
            $total = $data['row']['price'] * $data['row']['count'];
        }

        return $total;
    }


    public function calculateDiff(array $data): array
    {
        if (isset($data['not_base'])) {
            foreach (array_merge($data['base'], $data['not_base']) as $field) {
                if (isset($field['row'])) {
                    $formRows['rows'][$field['row']['id']] = [
                        'base_count' => $field['base_count'],
                        'count' => $field['cnt']
                    ];
                }
            }
        } else {
            foreach ($data['base'] as $field) {
                if (isset($field['row'])) {
                    $formRows['rows'][$field['row']['id']] = [
                        'base_count' => $field['base_count'],
                        'count' => $field['cnt']
                    ];
                }
            }
        }
        $rows = $this->entityManager->getRepository(VariantsRows::class)
            ->findBy(['variantId' => $data['id']]);

        foreach ($rows as $row) {
            if (isset($formRows['rows'][$row->getRowId()])) {
                $cnt = $formRows['rows'][$row->getRowId()]['count'];
                $baseCount = $formRows['rows'][$row->getRowId()]['base_count'];
                if ($cnt - $baseCount > 0) {
                    $formRows['diffs'][$row->getRowId()]['diff'] = $cnt - $baseCount;
                }
            }
        }

        return $formRows['diffs'] ?? [];

    }

    public function modifyOrderVariant(OrdersVariants $orderVariant, array $diffs)
    {
        foreach ($diffs as $rowId => $diff) {
            $variantRow = new OrdersVariantsRows();
            $variantRow->setRow($this->entityManager->getRepository(Rows::class)->find($rowId));
            $variantRow->setDiff($diff['diff']);
            $orderVariant->addRow($variantRow);
            $orderVariant->setIsBase(false);
        }
        return $orderVariant;
    }

    public function getTotal(int $orderId)
    {

        $qb = $this->entityManager->createQueryBuilder()
            ->select('SUM(t3.price * (t2.cnt + COALESCE(t4.diff, 0))) * t1.cnt')
            ->from('App:OrdersVariants', 't1')
            ->leftJoin('App:VariantsRows', 't2', 'WITH', 't1.variantId = t2.variantId')
            ->leftJoin('App:Rows', 't3', 'WITH', 't2.rowId = t3.id')
            ->leftJoin('App:OrdersVariantsRows', 't4', 'WITH', 't4.orderVariantId = t1.id AND t4.rowId = t3.id')
            ->where('t1.orderId = :order_id')
            ->setParameter('order_id', $orderId)
            ->groupBy('t1.id');
        $res = array_sum(array_column($qb->getQuery()->getScalarResult(), 1));

        $qb = $this->entityManager->createQueryBuilder()
            ->select('t7.price * t6.cnt')
            ->from('App:OrdersRows', 't6')
            ->leftJoin('App:Rows', 't7', 'WITH', 't6.rowId = t7.id')
            ->where('t6.orderId = :order_id')
            ->setParameter('order_id', $orderId);
        $res2 = $qb->getQuery()->getScalarResult();

        if ($res2) {
            $res += $res2[0][1];
        }
        return $res;
    }



//    public function prepareOrderData(Orders $order): array
//    {
//        $orderVariants = $order->getOrdersVariants();
//        if (!empty($orderVariants->toArray())) {
//            foreach ($orderVariants as $orderVariant) {
//                if ($orderVariant->getCnt() <= 0) {
//                    $this->deleteVariant($orderVariant->getId());
//                } else {
//                    $orderVariantRows = $orderVariant->getRows()->toArray();
//                    $variant = $orderVariant->getVariant();
//                    $variant->setType($fields[0]['type'] ?? null);
//                    $this->entityManager->persist($variant);
//                    $variantRows = $variant->getVariantsRows();
//                    $price = 0;
//                    $diff = 0;
//                    if (!empty($orderVariantRows)) {
//                        foreach ($orderVariantRows as $item) {
//
////                            foreach ($variantRows as $vRow) {
////                                $row = $vRow->getRow();
////                                $row->setDiff($item->getDiff());
////                                $row->setCount($vRow->getCnt());
////                                $row->setType($orderVariant->getType());
////                                $this->entityManager->persist($row);
////                                $price += $row->getPrice() * ($row->getCount() + $row->getDiff());
////                                $variant->setPrice($price);
////                                $variant->setCnt($orderVariant->getCnt());
////                                $this->entityManager->persist($variant);
////                                $data['variants'][] = $variant;
////                            }
//                        }
//                    } else {
//                        foreach ($variantRows as $vRow) {
//                            $row = $vRow->getRow();
//                            $row->setCount($vRow->getCnt());
//                            $row->setDiff($diff);
//                            $row->setType($orderVariant->getType());
//                            $this->entityManager->persist($row);
//                            $price += $row->getPrice() * ($row->getCount() + $row->getDiff());
//                            $variant->setPrice($price);
//                            $variant->setCnt($orderVariant->getCnt());
//                            $this->entityManager->persist($variant);
//                        }
//                        $data['variants'][] = $variant;
//                    }
//                }
//            }
//        }
//        $orderRows = $order->getOrdersRows();
//        if (!empty($orderRows->toArray())) {
//            foreach ($orderRows as $row) {
//                if ($row->getCnt() <= 0) {
//                    $this->deleteRow($row->getId());
//                } else {
//                    $this->entityManager->persist($row);
//                    $this->entityManager->flush();
//                }
//            }
//        }
//        $this->entityManager->flush();
//        $data['rows'] = $orderRows;
//        return $data;
//    }

    public function deleteVariant(int $id)
    {
        $variantRepository = $this->entityManager->getRepository(OrdersVariants::class);
        $variant = $variantRepository->find($id);

        if ($variant) {
            $this->entityManager->remove($variant);
            $this->entityManager->flush();
        }
    }

    public function deleteRow(int $id)
    {
        $rowRepository = $this->entityManager->getRepository(OrdersRows::class);
        $row = $rowRepository->find($id);

        if ($row) {
            $this->entityManager->remove($row);
            $this->entityManager->flush();
        }
    }


}