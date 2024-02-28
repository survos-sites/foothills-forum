<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Submission;
use App\Entity\User;
use App\Form\SubmissionType;
use App\Message\SendPhotoForApproval;
use App\MessageHandler\SendPhotoForApprovalHandler;
use App\Repository\SubmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Survos\ApiGrid\Components\ApiGridComponent;
use Survos\WorkflowBundle\Traits\HandleTransitionsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// @todo: if Workflow Bundle active
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    #[IsGranted('ROLE_ADMIN')]
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

    #[Route('/event/submission/{eventId}/new', name: 'event_submission_new', options: ['expose' => true])]
    #[Route('/location/submission/{locationId}/new', name: 'location_submission_new', options: ['expose' => true])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function new(
        Request                   $request,
        PropertyAccessorInterface $propertyAccessor,
        MessageBusInterface       $bus,
        ?Event                     $event=null,
        ?Location $location = null
    ): Response
    {
        // @todo: use MapQuery to extract the request
        $submission = new Submission();
        if ($event) {
            $submission->setEvent($event);
        }
        if ($location) {
            $submission->setLocation($location);
        }
        /** @var User $user */
        $user = $this->getUser();

        // saved by session, could eventually be event or place when this is generic
        $session = $request->getSession();
        $formVarsToSaveInSession = ['credit', 'email'];
        foreach ($formVarsToSaveInSession as $formVar) {

            if ($user) {
                $value = $propertyAccessor->getValue($user,
                    $formVar == 'credit' ? 'creditName' : $formVar, '');
                $propertyAccessor->setValue($submission, $formVar, $value);
            }
            $value = $session->get($formVar, '');
            if ($value) {
                $propertyAccessor->setValue($submission, $formVar, $value);
            }
        }
        $form = $this->createForm(SubmissionType::class, $submission, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user) {
                $user->addSubmission($submission);
            }
            $entityManager = $this->entityManager;
            $entityManager->persist($submission);
            $entityManager->flush();
            $entityManager->refresh($user);

            $this->addFlash('info', 'Thanks! Your photo is now being reviewed');
            $bus->dispatch((new SendPhotoForApproval($submission->getId())));

            foreach ($formVarsToSaveInSession as $formVar) {
//                $session->set($formVar, $propertyAccessor->getValue($submission, $formVar));
            }


            $redirect = $this->redirectToRoute('submission_show', $submission->getrp());
            return $redirect;
        }
        return $this->render('submission/new.html.twig', [
            'submission' => $submission,
            'form' => $form->createView(),
        ]);
    }


}
