<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Survos\ApiGrid\Components\ApiGridComponent;
use Survos\ApiGrid\Service\DatatableService;
use Survos\ApiGrid\State\MeiliSearchStateProvider;
use Survos\InspectionBundle\Services\InspectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// @todo: if Workflow Bundle active

#[Route('/author')]
class AuthorCollectionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ApiGridComponent       $apiGridComponent,
        private ?IriConverterInterface $iriConverter = null
    )
    {
    }

    #[Route('/index', name: 'author_index')]
    #[Route(path: '/{apiRoute}/', name: 'author_browse',
        requirements: ['apiRoute' => '|index|browse|_api_meili/author_get_collection'],
        methods: ['GET'])]
    public function browseauthor(Request $request,
                                 InspectionService $inspectionService,
                                 DatatableService $datatableService,
                                 string $apiRoute='',
    ): Response
    {
        $apiRoute = 'author-meili';
        $class = Author::class;
        $map = $inspectionService->getAllUrlsForResource($class);

        $shortClass = 'Author';
        $useMeili = 'app_browse' == $request->get('_route');
        // this should be from inspection bundle!
//        $apiCall = '_api_meili/author_get_collection';
//        $apiCall = $useMeili
//            ? '/api/meili/' . $shortClass
//            : $this->iriConverter->getIriFromResource($class, operation: new GetCollection(),
//                context: $context ?? []);

        $this->apiGridComponent->setClass($class);
        $c = $this->apiGridComponent->getDefaultColumns();
        $c = $datatableService->getSettingsFromAttributes($class);
//        $c = $this->apiGridComponent->getDefaultColumns();
        $columns = array_values($c);
        $useMeili = 'author_browse' == $request->get('_route');

        // this should be from inspection bundle!
//        $route = $map[$useMeili ? MeiliSearchStateProvider::class: CollectionProvider::class];

//        $apiCall = $useMeili
//            ? '/api/meili/' . $shortClass
//            : $this->iriConverter->getIriFromResource($class, operation: new GetCollection(),
//                context: $context ?? []);
//dd($useMeili, $apiCall, $map, $route);
        $columns = [
            'fullName',
            'id',
            'uuid',
            'articleCount'
        ];

        return $this->render('author/browse.html.twig', [
            'class' => $class,
            'useMeili' => $useMeili,
            'apiCall' => $apiRoute,
            'columns' => $columns,
            'filter' => [],
        ]);
    }

    #[Route('/symfony_crud_index', name: 'author_symfony_crud_index')]
    public function symfony_crud_index(AuthorRepository $authorRepository): Response
    {
        return $this->render('author/index.html.twig', [
            'authors' => $authorRepository->findBy([], [], 30),
        ]);
    }

    #[Route('author/new', name: 'author_new')]
    public function new(Request $request): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($author);
            $entityManager->flush();

            return $this->redirectToRoute('author_browse');
        }

        return $this->render('author/new.html.twig', [
            'author' => $author,
            'form' => $form->createView(),
        ]);
    }
}
