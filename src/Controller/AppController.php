<?php

namespace App\Controller;

use App\Entity\Article;
use Survos\ApiGrid\State\MeilliSearchStateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Archetype\Facades\PHPFile;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {
        return $this->render('app/index.html.twig', [
            'class' => Article::class
        ]);
    }
    #[Route('/doctrine', name: 'app_articles_with_doctrine')]
    public function articles(): Response
    {
        return $this->render('app/index.html.twig', [
            'class' => Article::class,
        ]);
    }
}
