<?php

namespace App\Controller;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use App\Entity\Article;
use App\Entity\Event;
use App\Entity\Sport;
use App\Form\UserType;
use App\Repository\EventRepository;
use App\Repository\SportRepository;
use KnpU\OAuth2ClientBundle\Client\Provider\GithubClient;
use Survos\ApiGrid\Components\ApiGridComponent;
use Survos\ApiGrid\State\MeiliSearchStateProvider;
use Survos\InspectionBundle\Services\InspectionService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Archetype\Facades\PHPFile;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Clock\now;

class AppController extends AbstractController
{

    public function __construct(private ParameterBagInterface $bag)
    {
    }

    #[Route('/', name: 'app_homepage')]
    #[Template("app/current_sports.html.twig")]
    public function index(Request $request, SportRepository $sportRepository, EventRepository $eventRepository): array|Response
    {
        // get all current and recent events
        $query = $eventRepository->createQueryBuilder('e')
            ->andWhere('e.eventDate >= :ago')
            ->setParameter('ago', date_modify(new \DateTime(), '-1 days'))
//            ->andWhere('e.eventDate <= :now')
//            ->setParameter('now', date_modify(new \DateTime(), '-2 days'))
//                ->orderBy('e.id', 'ASC')
                ->orderBy('e.eventDate', 'ASC')
            ->setMaxResults(30);

        $currentEvents = $query->getQuery()->getResult();
        // hack!
        $currentSports  = [];
        /* @var $event Event */
        foreach ($currentEvents as $event) {
            $sportId = $event->getSport()->getId();
            $currentSports[$sportId] = true;
        }

        return [
            'sports' => $sportRepository->findBy([
                'id' => array_keys($currentSports)

            ]), // @todo: filter by current
            'events' => $currentEvents];

        return $this->redirectToRoute('event_index');
    }
    #[Route('/sport/{sportId}', name: 'app_sports')]
    #[Template("app/current_events.html.twig")]
    public function eventsBySport(Request $request, Sport $sport, EventRepository $eventRepository): array|Response
    {
        // get all current and recent events
        $query = $eventRepository->createQueryBuilder('e')
            ->andWhere('e.sport = :sport')
            ->setParameter('sport', $sport)
            ->andWhere('e.eventDate >= :ago')
            ->setParameter('ago', date_modify(new \DateTime(), '-1 days'))
//            ->andWhere('e.eventDate <= :now')
//            ->setParameter('now', date_modify(new \DateTime(), '-2 days'))
//                ->orderBy('e.id', 'ASC')
            ->orderBy('e.eventDate', 'ASC')
            ->setMaxResults(30);

        $currentEvents = $query->getQuery()->getResult();

        return [
            'events' => $currentEvents
        ];

        return $this->redirectToRoute('event_index');
    }

    #[Route('/doctrine', name: 'article_browse')]
    public function articles(
        ApiGridComponent $apiGridComponent,
        InspectionService $inspectionService): Response
    {
//        assert(false, "pass the correct key to browse");
        $useMeili = false;
        $class = Article::class;

        $apiGridComponent->setClass($class);
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
        $apiRoute = 'doctrine-articles';
//        $apiCall = $endpoints[$useMeili ? MeiliSearchStateProvider::class : CollectionProvider::class];

        return $this->render('@SurvosApiGrid/datatables.html.twig', get_defined_vars() + [
                'apiRoute' => $apiRoute,
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

    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function userProfile(GithubClient $client): Response
    {

//        $authorizations = $client->api('authorizations')->all();
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user, ['attr' => ['readonly' => true, 'disabled' => true]]);

        return $this->render('app/profile.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/terms', name: 'app_terms')]
    #[Template('app/terms.html.twig')]
    public function terms(): array
    {
        $reviewers = $this->bag->get('photo_reviewers');
        return ['reviewers' =>$reviewers];
    }

    #[Route('/mmenu', name: 'app_mmenu')]
    #[Template('app/mmenu-test.html.twig')]
    public function mmenu(): array
    {
        $reviewers = $this->bag->get('photo_reviewers');
        return ['reviewers' =>$reviewers];
    }

}
