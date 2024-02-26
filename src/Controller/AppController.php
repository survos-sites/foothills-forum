<?php

namespace App\Controller;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use App\Entity\Article;
use App\Form\UserType;
use KnpU\OAuth2ClientBundle\Client\Provider\GithubClient;
use Survos\ApiGrid\Components\ApiGridComponent;
use Survos\ApiGrid\State\MeiliSearchStateProvider;
use Survos\InspectionBundle\Services\InspectionService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Archetype\Facades\PHPFile;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    #[Template("app/index.html.twig")]
    public function index(Request $request): array|Response
    {
        return $this->redirectToRoute('event_index');
        return [
        'apiRoute' => $request->get('doctrine', false) ? 'doctrine-articles' : 'meili-articles',
        'class' => Article::class];

        $class = Article::class;
        $map = $inspectionService->getAllUrlsForResource($class);
//        dd($map);

        return $this->render('app/index.html.twig', [
            'class' => Article::class,
            'apiRoute' => $apiRoute,
            'apiCall' => null
        ]);
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
        return [];
    }

}
