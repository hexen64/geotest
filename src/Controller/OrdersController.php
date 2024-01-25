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
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
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

    public function __construct(EntityManagerInterface $entityManager,
                                OrderService           $orderService,
                                SerializerInterface    $serializer)
    {
        $this->entityManager = $entityManager;
        $this->orderService = $orderService;
        $this->serializer = $serializer;
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
    public function save(int $orderId, Request $request, EmailService $mailer)
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
                $orderData = ReceiptService::create($order, $this->entityManager);
                $mailer->execute($orderData);
                $mailer->sendReceipt();
                $mailer->toAdmin();

                return $this->done($orderData, $request, $response);
            }
            $formErrors = $form->getErrors(true, true);
            foreach ($formErrors as $error) {
                $fieldName = $error->getOrigin()->getName();
                $fieldError = $error->getMessage();
                $errors['form'][$fieldName] = $fieldError;
            }
        }
//        $response->headers->setCookie(new Cookie('errors', $errors, time() + $this->getParameter('app_cookie_expire')));


        return $this->redirectToRoute('app_order', ['orderId' => $order->getId()]);
    }


    #[Route('/order/done/{orderId}', name: 'app_order_done', methods: ['GET'])]
    public function done(array $orderData, Request $request)
    {
        $order = $this->entityManager->getRepository(Orders::class)->find($orderData['id']);

        if (!$order || ($order->getEmail() && $order->getEmail() !== $request->cookies->get('email'))) {
            $this->addFlash('notice', 'Заказ не найден.');
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(OrdersType::class, $order);
        $labels = [];

        foreach ($form->createView()->children as $name => $field) {
            $label = $field->vars['label'];

            if ($label) {
                $labels[$name] = $label;
            }
        }

        $info = [];
        $labels_order = [
            'fio',
            'firm',
            'phone',
            'email',
            'address',
            'comment',
            'delivery',
        ];

        foreach ($labels_order as $name) {
            $val = $labels[$name] ?? '';

            if ($name === 'delivery') {
                $delivery = $form->get('delivery')->getData();
                $delivery_val = $order->getDelivery();
                $info[$name] = [
                    'label' => $val,
                    'value' => isset($delivery[$delivery_val]) ? $delivery[$delivery_val] : '',
                ];
                continue;
            }

            $info[$name] = [
                'label' => $val,
                'value' => $order->{'get' . ucfirst($name)}(),
            ];
        }


        $order->setSubmit(1);
        $order->setTotal($orderData['orderTotal']);
        $this->entityManager->flush();

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
