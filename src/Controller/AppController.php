<?php

namespace App\Controller;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use App\Entity\Article;
use Survos\ApiGrid\Components\ApiGridComponent;
use Survos\ApiGrid\State\MeiliSearchStateProvider;
use Survos\InspectionBundle\Services\InspectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Archetype\Facades\PHPFile;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(InspectionService $inspectionService): Response
    {
        $class = Article::class;
        $map = $inspectionService->getAllUrlsForResource($class);
//        dd($map);

        return $this->render('app/index.html.twig', [
            'class' => Article::class
        ]);
    }
    #[Route('/doctrine', name: 'app_articles_with_doctrine')]
    public function articles(ApiGridComponent $apiGridComponent, InspectionService $inspectionService): Response
    {
        $useMeili = true;
        $class = Article::class;

        $apiGridComponent->class = $class;
        $shortClass = 'Article';
        $defaultColumns = $apiGridComponent->getDefaultColumns();
        $apiGridComponent->columns = array_keys($defaultColumns);
        $columns = $apiGridComponent->getNormalizedColumns();
        $columns = [
            'headline',
            'authorCount'
        ];

//        dd($columns, $class);
        $endpoints = $inspectionService->getAllUrlsForResource($class);
        $apiCall = 'api_doctrine_articles';
//        $apiCall = $endpoints[$useMeili ? MeiliSearchStateProvider::class : CollectionProvider::class];


        return $this->render('@SurvosApiGrid/datatables.html.twig', get_defined_vars() + [
                'apiCall' => $apiCall,
                'useMeili' => $useMeili,
                'caller' => '@SurvosApiGrid/datatables.html.twig',
                'indexName' => $shortClass, // @todo: call the name method, might include a prefix
                'columns' => $columns,
                'class' => $class
            ]);

        return $this->render('app/index.html.twig', [
            'class' => Article::class,
        ]);


        return $this->render('app/index.html.twig', [
            'class' => Article::class,
        ]);
    }
}
