<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Location;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Survos\ApiGrid\Components\ApiGridComponent;
use Survos\WorkflowBundle\Traits\HandleTransitionsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// @todo: if Workflow Bundle active
use Symfony\Component\Routing\Attribute\Route;

#[Route('/location')]
class LocationCollectionController extends AbstractController
{
    use HandleTransitionsTrait;

    public function __construct(
private EntityManagerInterface $entityManager,
private ApiGridComponent $apiGridComponent,
private ?IriConverterInterface $iriConverter = null
) {
    }

    #[Route(path: '/browse/', name: 'location_browse', methods: ['GET'])]
    #[Route('/index', name: 'location_index')]
    public function browselocation(Request $request): Response
    {
        $class = Location::class;
        $shortClass = 'Location';
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
        $useMeili = 'location_browse' == $request->get('_route');
        // this should be from inspection bundle!
        $apiCall = $useMeili
        ? '/api/meili/'.$shortClass
        : $this->iriConverter->getIriFromResource($class, operation: new GetCollection(),
            context: $context ?? [])
        ;

        return $this->render('location/browse.html.twig', [
        'class' => $class,
        'useMeili' => $useMeili,
        'apiCall' => $apiCall,
        'columns' => $columns,
        'filter' => [],
        ]);
    }

    #[Route('/symfony_crud_index', name: 'location_symfony_crud_index')]
        public function symfony_crud_index(LocationRepository $locationRepository): Response
        {
            return $this->render('location/index.html.twig', [
                'locations' => $locationRepository->findBy([], [], 30),
            ]);
        }

    #[Route('location/new', name: 'location_new')]
        public function new(Request $request): Response
        {
            $location = new Location();
            $form = $this->createForm(LocationType::class, $location);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->entityManager;
                $entityManager->persist($location);
                $entityManager->flush();

                return $this->redirectToRoute('location_index');
            }

            return $this->render('location/new.html.twig', [
                'location' => $location,
                'form' => $form->createView(),
            ]);
        }
}
