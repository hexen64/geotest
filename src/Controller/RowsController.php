<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RowsController extends AbstractController
{
    #[Route('/rows/{rowId}/{groupId}', name: 'app_row_show')]
    public function show(): Response
    {
        return $this->render('rows/show.html.twig', [
            'controller_name' => 'RowsController',
        ]);
    }
}
