<?php

namespace App\Controller;

use App\Entity\Orders;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrdersController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/order/{orderId}', name: 'app_order')]
    public function show(int $orderId, Request $request): Response
    {

        $order = null;
        if ($orderId !== null) {
            dd($orderId);
            $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
            dd($order);
        }


        if (!$orderId || !$order || $order->getSubmit() || ($order->getEmail() && $order->getEmail() != $request->cookies->get('email'))) {
            $this->addFlash('notice', 'Заказ не найден.');
            return $this->redirectToRoute('app_devices');
        }

        $form = $this->createForm(OrdersForm::class, $order);

        if (count($form->get('variants')) === 0 && count($form->get('rows')) === 0) {
            $response = new Response();
            $response->headers->clearCookie('orderId');
            $response->sendHeaders();
            return $this->redirectToRoute('app_devices');
        }

        $order_total = u::orderTotal($orderId);

        if (!$order_total) {
            return $this->redirectToRoute('app_devices');
        }

        return $this->render('your_template.html.twig', [
            'form' => $form->createView(),
            'order_total' => $order_total,
        ]);
    }
}
