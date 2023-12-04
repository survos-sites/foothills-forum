<?php

namespace App\Controller;

use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Archetype\Facades\PHPFile;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {

//        $fn = __DIR__ . '/../Entity/Article.php';
//        assert(file_exists($fn), $fn);
//        $phpFile = PHPFile::load($fn)
//            ->className('NewClassName');
//        dd($phpFile);
//
//// Create new files
//        $x = PHPFile::make()->class('App\\Entity\\Product')
////            ->use('Shippable')
//            ->public()->property('stock', -1)
//            ->save();
//        dd($x);
//
        return $this->render('app/index.html.twig', [
            'class' => Article::class
        ]);
    }
}
