<?php

namespace App\Twig;

use App\Entity\Orders;
use App\Services\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class OrderExtension extends AbstractExtension
{
    private $orderService;

    private $entityManager;

    public function __construct(OrderService $orderService, EntityManagerInterface $entityManager)
    {
        $this->orderService = $orderService;
        $this->entityManager = $entityManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getTotal', [$this->orderService, 'getTotal']),
            new TwigFunction('getOrder', [$this,  'getOrder'])
        ];
    }

    public function getOrder(int $orderId)
    {
        return $this->entityManager->getRepository(Orders::class)->find($orderId);
    }
}