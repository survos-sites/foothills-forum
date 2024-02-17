<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Survos\ApiGrid\Components\ApiGridComponent;
use Survos\WorkflowBundle\Traits\HandleTransitionsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// @todo: if Workflow Bundle active
use Symfony\Component\Routing\Attribute\Route;

#[Route('/event')]
class EventCollectionController extends AbstractController
{
    use HandleTransitionsTrait;

    public function __construct(
private EntityManagerInterface $entityManager,
private ApiGridComponent $apiGridComponent,
private ?IriConverterInterface $iriConverter = null
) {
    }

    #[Route(path: '/browse/', name: 'event_browse', methods: ['GET'])]
    #[Route('/index', name: 'event_index')]
    public function browseevent(Request $request): Response
    {
        $class = Event::class;
        $shortClass = 'Event';
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
        $useMeili = 'event_browse' == $request->get('_route');
        // this should be from inspection bundle!
        $apiCall = $useMeili
        ? '/api/meili/'.$shortClass
        : $this->iriConverter->getIriFromResource($class, operation: new GetCollection(),
            context: $context ?? [])
        ;


        return $this->render('event/browse.html.twig', [
        'class' => $class,
        'useMeili' => $useMeili,
        'apiCall' => $apiCall,
        'columns' => $columns,
        'filter' => [],
        ]);
    }

    #[Route('/symfony_crud_index', name: 'event_symfony_crud_index')]
        public function symfony_crud_index(EventRepository $eventRepository): Response
        {
            return $this->render('event/index.html.twig', [
                'events' => $eventRepository->findBy([], [], 30),
            ]);
        }

    #[Route('event/new', name: 'event_new')]
        public function new(Request $request): Response
        {
            $event = new Event();
            $form = $this->createForm(EventType::class, $event);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->entityManager;
                $entityManager->persist($event);
                $entityManager->flush();

                return $this->redirectToRoute('event_index');
            }

            return $this->render('event/new.html.twig', [
                'event' => $event,
                'form' => $form->createView(),
            ]);
        }
}
