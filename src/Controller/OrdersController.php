<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersRows;
use App\Entity\OrdersVariants;
use App\Form\OrdersType;
use App\Services\EmailService;
use App\Services\OrderService;
use App\Services\ReceiptService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class OrdersController extends AbstractController
{

    private EntityManagerInterface $entityManager;
    private OrderService $orderService;
    private SerializerInterface $serializer;
    private EmailService $mailer;

    private ReceiptService $receiptService;

    public function __construct(EntityManagerInterface $entityManager,
                                OrderService           $orderService,
                                SerializerInterface    $serializer,
                                EmailService           $mailer,
                                ReceiptService         $receiptService)
    {
        $this->entityManager = $entityManager;
        $this->orderService = $orderService;
        $this->serializer = $serializer;
        $this->mailer = $mailer;
        $this->receiptService = $receiptService;
    }

    #[Route('/order/{orderId}', name: 'app_order', methods: ['GET'])]
    public function show(int $orderId, Request $request): Response
    {
        $order = null;
        if ($orderId !== null) {
            $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
        }

        if (!$orderId || !$order || $order->isSubmit() || ($order->getEmail() && $order->getEmail() != $request->cookies->get('email'))) {
            $this->addFlash('notice', 'Заказ не найден.');
            return $this->redirectToRoute('app_devices');
        }

        $form = $this->createForm(OrdersType::class, $order);

        if (count($form->get('ordersVariants')) === 0 && count($form->get('ordersRows')) === 0) {
            $response = new Response();
            $response->headers->clearCookie('order_id');
            $response->sendHeaders();
            return $this->redirectToRoute('app_devices');
        }

        $orderTotal = $this->orderService->getTotal($order->getId());

        if (!$orderTotal) {
            return $this->redirectToRoute('app_devices');
        }

        return $this->render('orders/show.html.twig', [
            'form' => $form,
            'orderId' => $order->getId(),
            'order_total' => $orderTotal,
        ]);
    }

    #[Route('/order/{orderId}', name: 'app_order_save', methods: ['POST'])]
    public function save(int $orderId, Request $request)
    {
        $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $response = new Response();
                $response->headers->clearCookie('order_id');
                $response->headers->setCookie(new Cookie('email', $order->getEmail(), time() + $this->getParameter('app_cookie_expire')));
                $response->sendHeaders();
                $order = $form->getData();
                $this->entityManager->flush();
                return $this->redirectToRoute('app_order_done', ['orderId' => $order->getId()]);
            }
            $formErrors = $form->getErrors(true, true);
            foreach ($formErrors as $error) {
                $fieldName = $error->getOrigin()->getName();
                $fieldError = $error->getMessage();
                $errors['form'][$fieldName] = $fieldError;
            }
        }

        return $this->redirectToRoute('app_order', ['orderId' => $order->getId()]);
    }


    #[Route('/order/done/{orderId}', name: 'app_order_done', methods: ['GET'])]
    public function done(int $orderId, Request $request)
    {
        $order = $this->entityManager->getRepository(Orders::class)->find($orderId);
        $orderData = $this->receiptService->create($order);

        $this->mailer->execute($orderData);
        $this->mailer->sendReceipt();
        $this->mailer->toAdmin();

        if (!$order || ($order->getEmail() && $order->getEmail() !== $request->cookies->get('email'))) {
            $this->addFlash('notice', 'Заказ не найден.');
            throw new NotFoundHttpException();
        }


        $order->setSubmit(1);
        $order->setTotal($orderData['orderTotal']);
        $this->entityManager->flush();

        $delivery = [
            "firm" => "На склад заказчика",
            "firm+sklad" => "На склад транспортной компании",
            "self" => "Самовывоз",
        ];

        $orderData['contact'] = array_map(function ($value) use ($delivery) {
            return isset($delivery[$value]) ? $delivery[$value] : $value;
        }, $orderData['contact']);


        return $this->render('orders/done.html.twig', [
            'id' => $orderData['id'],
            'contact' => $orderData['contact'],
            'orderTotal' => $orderData['orderTotal'],
            'variants' => isset($orderData['variants']) ? $orderData['variants'] : [],
            'rows' => isset($orderData['rows']) ? $orderData['rows'] : [],
            'done' => true
        ]);
    }


    #[Route('/order/variant/delete/{id}', name: 'app_order_delete_variant')]
    public function deleteVariant(int $id): JsonResponse
    {
        $variantRepository = $this->entityManager->getRepository(OrdersVariants::class);
        $variant = $variantRepository->find($id);

        if ($variant) {
            $this->entityManager->remove($variant);
            $this->entityManager->flush();
            return new JsonResponse(['success' => true]);
        }
        return new JsonResponse(['success' => false]);
    }

    #[Route('/order/row/delete/{id}', name: 'app_order_delete_row')]
    public function deleteRow(int $id): JsonResponse
    {
        $rowRepository = $this->entityManager->getRepository(OrdersRows::class);
        $row = $rowRepository->find($id);

        if ($row) {
            $this->entityManager->remove($row);
            $this->entityManager->flush();
            return new JsonResponse(['success' => true]);
        }
        return new JsonResponse(['success' => false]);
    }

}
