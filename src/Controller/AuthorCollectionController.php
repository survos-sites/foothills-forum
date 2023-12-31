<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Survos\ApiGrid\Components\ApiGridComponent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// @todo: if Workflow Bundle active
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route(path: '/browse/', name: 'author_browse', methods: ['GET'])]
    #[Route('/index', name: 'author_index')]
    public function browseauthor(Request $request): Response
    {
        $class = Author::class;
        $shortClass = 'Author';
        $useMeili = 'app_browse' == $request->get('_route');
        // this should be from inspection bundle!
        $apiCall = $useMeili
            ? '/api/meili/' . $shortClass
            : $this->iriConverter->getIriFromResource($class, operation: new GetCollection(),
                context: $context ?? []);

        $this->apiGridComponent->class = $class;
        $c = $this->apiGridComponent->getDefaultColumns();
        $columns = array_values($c);
        $useMeili = 'author_browse' == $request->get('_route');
        // this should be from inspection bundle!
        $apiCall = $useMeili
            ? '/api/meili/' . $shortClass
            : $this->iriConverter->getIriFromResource($class, operation: new GetCollection(),
                context: $context ?? []);

        $columns = [
            'id',
            'uuid',
            'fullName',
            'articleCount'
        ];

        return $this->render('author/browse.html.twig', [
            'class' => $class,
            'useMeili' => $useMeili,
            'apiCall' => $apiCall,
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

            return $this->redirectToRoute('author_index');
        }

        return $this->render('author/new.html.twig', [
            'author' => $author,
            'form' => $form->createView(),
        ]);
    }
}
