<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComplectsController extends AbstractController
{
    #[Route('/complect/{id}/{group}', name: 'app_complect_group')]
    #[Route('/complect/{id}', name: 'app_complect')]
    public function show(int $id, ?string $group = null, EntityManagerInterface $entityManager): Response
    {

    }
}
