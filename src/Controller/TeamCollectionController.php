<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Survos\ApiGrid\Components\ApiGridComponent;
use Survos\WorkflowBundle\Traits\HandleTransitionsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// @todo: if Workflow Bundle active
use Symfony\Component\Routing\Attribute\Route;

#[Route('/team')]
class TeamCollectionController extends AbstractController
{
    use HandleTransitionsTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ApiGridComponent $apiGridComponent,
        private ?IriConverterInterface $iriConverter = null
    ) {
    }

    #[Route(path: '/browse/', name: 'team_browse', methods: ['GET'])]
    #[Route('/index', name: 'team_index')]
    public function browseteam(Request $request): Response
    {
        $class = Team::class;
        $shortClass = 'Team';
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
        $useMeili = 'team_browse' == $request->get('_route');
        // this should be from inspection bundle!
        $apiCall = $useMeili
        ? '/api/meili/'.$shortClass
        : $this->iriConverter->getIriFromResource($class, operation: new GetCollection(),
            context: $context ?? [])
        ;
//        dd($columns);

        return $this->render('team/browse.html.twig', [
        'class' => $class,
        'useMeili' => $useMeili,
        'apiCall' => $apiCall,
        'columns' => $columns,
        'filter' => [],
        ]);
    }

    #[Route('/symfony_crud_index', name: 'team_symfony_crud_index')]
    public function symfony_crud_index(TeamRepository $teamRepository): Response
    {
        return $this->render('team/index.html.twig', [
            'teams' => $teamRepository->findBy([], [], 30),
        ]);
    }

    #[Route('team/new', name: 'team_new')]
    public function new(Request $request): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('team_index');
        }

        return $this->render('team/new.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }
}
