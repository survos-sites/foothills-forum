<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Submission;
use App\Form\SubmissionType;
use App\Repository\SubmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Survos\ApiGrid\Components\ApiGridComponent;
use Survos\WorkflowBundle\Traits\HandleTransitionsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// @todo: if Workflow Bundle active
use Symfony\Component\Routing\Attribute\Route;

#[Route('/submissions')]
class SubmissionCollectionController extends AbstractController
{
    use HandleTransitionsTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ApiGridComponent       $apiGridComponent,
        private ?IriConverterInterface $iriConverter = null
    )
    {
    }

    #[Route(path: '/browse/', name: 'submission_browse', methods: ['GET'])]
    #[Route('/index', name: 'submission_index')]
    public function browsesubmission(Request $request): Response
    {
        $class = Submission::class;
        $shortClass = 'Submission';
        $useMeili = 'app_browse' == $request->get('_route');
        // this should be from inspection bundle!
        $apiCall = $useMeili
            ? '/api/meili/' . $shortClass
            : $this->iriConverter->getIriFromResource($class, operation: new GetCollection(),
                context: $context ?? []);

        $this->apiGridComponent->setClass($class);
        $c = $this->apiGridComponent->getDefaultColumns();
        $columns = array_values($c);
        $useMeili = 'submission_browse' == $request->get('_route');
        // this should be from inspection bundle!
        $apiCall = $useMeili
            ? '/api/meili/' . $shortClass
            : $this->iriConverter->getIriFromResource($class, operation: new GetCollection(),
                context: $context ?? []);

        return $this->render('submission/browse.html.twig', [
            'class' => $class,
            'useMeili' => $useMeili,
            'apiCall' => $apiCall,
            'columns' => $columns,
            'filter' => [],
        ]);
    }

    #[Route('/symfony_crud_index', name: 'submission_symfony_crud_index')]
    public function symfony_crud_index(SubmissionRepository $submissionRepository): Response
    {
        return $this->render('submission/index.html.twig', [
            'submissions' => $submissionRepository->findBy([], [], 30),
        ]);
    }

    #[Route('submission/new', name: 'submission_new')]
    public function new(Request $request): Response
    {
        $submission = new Submission();
        $form = $this->createForm(SubmissionType::class, $submission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($submission);
            $entityManager->flush();

            return $this->redirectToRoute('submission_index');
        }

        return $this->render('submission/new.html.twig', [
            'submission' => $submission,
            'form' => $form->createView(),
        ]);
    }
}
