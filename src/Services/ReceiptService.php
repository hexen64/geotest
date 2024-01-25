<?php

namespace App\Services;

use App\Entity\Orders;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class ReceiptService
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function create(Orders $order)
    {
        $orderVariants = $order->getOrdersVariants();
        if (!empty($orderVariants->toArray())) {
            foreach ($orderVariants as $i => $orderVariant) {
                if ($orderVariant->getCnt() <= 0) {
                    $this->entityManager->remove($orderVariant);
                    $this->entityManager->flush();
                } else {
                    $qb = $this->entityManager->createQueryBuilder()
                        ->select('r.id', 'r.name', 'vr.cnt', 'r.name', 'r.idl', 'r.idk')
                        ->from('App:OrdersVariants', 'ov')
                        ->leftJoin('App:VariantsRows', 'vr', 'WITH', 'ov.variantId = vr.variantId')
                        ->leftJoin('App:Rows', 'r', 'WITH', 'r.id = vr.rowId')
                        ->where('ov.id = :order_variant_id')
                        ->setParameter('order_variant_id', $orderVariant->getId());

                    if (!$orderVariant->getIsBase()) {
                        $qb->addSelect('ovr.diff')
                            ->leftJoin('App:OrdersVariantsRows', 'ovr', 'WITH', 'ovr.rowId = r.id AND ovr.orderVariantId = ov.id');
                        $qb->addSelect('((COALESCE(ovr.diff, 0) + vr.cnt) * r.price) AS price');

                    } else {
                        $qb->addSelect('(vr.cnt * r.price) AS price')
                            ->leftJoin('App:Variants', 'v', 'WITH', 'v.id = ov.variantId');
                    }
                    if ($orderVariant->getType()) {
                        $qb->addSelect('CASE WHEN r.idl <> r.idk THEN ov.type ELSE \'\' END AS type');
                    }


                    $result = $qb->getQuery()->getResult();

                }
                $data['variants'][$i]['rows'] = array_map(function ($row) {
                    return [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'cnt' => $row['cnt'],
                        'diff' => $row['diff'] ?? null,
                        'type' => $row['type'] ?? null,
                        'price' => $row['price'],
                        'idl' => $row['idl'],
                        'idk' => $row['idk'],
                    ];
                }, $result);


                $data['variants'][$i]['name'] = $orderVariant->getName();
                $data['variants'][$i]['price'] = array_sum(array_column($result, 'price'));
                $data['variants'][$i]['cnt'] = $orderVariant->getCnt();
                $data['variants'][$i]['type'] = $orderVariant->getType();
                $data['variants'][$i]['id'] = $orderVariant->getVariantId();

            }
        }

        $orderRows = $order->getOrdersRows();
        if (!empty($orderRows->toArray())) {
            foreach ($orderRows as $i => $orderRow) {
                $qb = $this->entityManager->createQueryBuilder()
                    ->select('r.id', 'r.name', 'orows.cnt', 'r.price')
                    ->from('App:OrdersRows', 'orows')
                    ->leftJoin('App:Rows', 'r', 'WITH', 'r.id = orows.rowId')
                    ->where('orows.id = :order_row_id')
                    ->setParameter('order_row_id', $orderRow->getId());
                $result = $qb->getQuery()->getResult();
            }
            if ($orderRow->getType()) {
                $qb->addSelect('CASE WHEN r.idl <> r.idk THEN or.type ELSE \'\' END AS type');
            }
            $data['rows'] = array_map(function ($row) {
                return [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'cnt' => $row['cnt'],
                    'type' => $row['type'] ?? "",
                    'price' => $row['price']
                ];
            }, $result);
        }

        $orderTotal = 0;
        $orderItems = array_merge($data['variants'] ?? [], $data['rows'] ?? []);
        foreach ($orderItems as $item) {
            $orderTotal += $item['price'] * $item['cnt'];
        }

        return array_merge($data, [
            'id' => $order->getId(),
            'orderTotal' => $orderTotal,
            'contact' => [
                'email' => $order->getEmail(),
                'fio' => $order->getFio(),
                'phone' => $order->getPhone(),
                'firm' => $order->getFirm(),
                'address' => $order->getAddress(),
                'comment' => $order->getComment(),
                'delivery' => $order->getDelivery(),
                'total' => $order->getTotal(),
                'submit' => $order->isSubmit(),
            ]
        ]);
    }
}