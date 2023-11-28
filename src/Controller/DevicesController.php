<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DevicesController extends BaseController
{
    #[Route('/', name: 'app_devices')]
    public function index(EntityManagerInterface $entityManager): Response
    {

        $qb = $entityManager->createQueryBuilder();
        $qb->select('g.name')
            ->from('App:Groups', 'g')
            ->orderBy('g.position', 'ASC');
        $groups = $qb->getQuery()->getResult();

        return $this->render('devices/index.html.twig', [
            'groups' => $groups
        ]);

    }

    #[Route('/devices/{group}', name: 'app_devices_show')]
    public function show()
    {

    }
        
}



