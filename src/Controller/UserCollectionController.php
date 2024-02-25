<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Survos\ApiGrid\Components\ApiGridComponent;
use Survos\WorkflowBundle\Traits\HandleTransitionsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// @todo: if Workflow Bundle active
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
class UserCollectionController extends AbstractController
{
    use HandleTransitionsTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ApiGridComponent $apiGridComponent,
        private ?IriConverterInterface $iriConverter = null
    ) {
    }

    #[Route(path: '/browse/', name: 'user_browse', methods: ['GET'])]
    #[Route('/index', name: 'user_index')]
    public function browseuser(Request $request): Response
    {
        $class = User::class;
        $shortClass = 'User';
        $useMeili = 'app_browse' == $request->get('_route');
        // this should be from inspection bundle!
        $apiCall = $useMeili
        ? '/api/meili/'.$shortClass
        : $this->iriConverter->getIriFromResource($class, operation: new GetCollection(),
            context: $context ?? [])
        ;

        $this->apiGridComponent->setClass($class);
        $c = $this->apiGridComponent->getDefaultColumns();
        $columns = array_values($c);
        $useMeili = 'user_browse' == $request->get('_route');
        // this should be from inspection bundle!
        $apiCall = $useMeili
        ? '/api/meili/'.$shortClass
        : $this->iriConverter->getIriFromResource($class, operation: new GetCollection(),
            context: $context ?? [])
        ;

        return $this->render('user/browse.html.twig', [
        'class' => $class,
        'useMeili' => $useMeili,
        'apiCall' => $apiCall,
        'columns' => $columns,
        'filter' => [],
        ]);
    }

    #[Route('/symfony_crud_index', name: 'user_symfony_crud_index')]
    public function symfony_crud_index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findBy([], [], 30),
        ]);
    }

    #[Route('user/new', name: 'user_new')]
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
