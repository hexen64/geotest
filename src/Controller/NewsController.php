<?php

namespace App\Controller;

use App\Entity\News;
use App\Services\NewsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    private $entityManager;
    private $newsRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->newsRepository = $entityManager->getRepository(News::class);
    }

    #[Route('/news', name: 'app_news')]
    public function index(): Response
    {
        $news = $this->newsRepository->findByLimit(10);
        $newsTotal = $this->newsRepository->findNewsTotal();

        return $this->render('news/index.html.twig', [
            'news' => $news,
            'newsTotal' => $newsTotal,
        ]);
    }

    #[Route('/news/{id}', name: 'app_news_show')]
    public function show($id): Response
    {
        $news = $this->newsRepository->find($id);
        if (!$news) {
            $this->addFlash('notice', 'Новость не найдена.');
            throw $this->createNotFoundException();
        }

        $newsBottom = $this->newsRepository->findBottomNews($id);
        return $this->render('news/show.html.twig', [
            'news' => $news,
            'newsBottom' => $newsBottom,
        ]);

    }
}
