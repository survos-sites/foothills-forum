<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Submission;
use App\Form\SubmissionType;
use App\Message\SendPhotoForApproval;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Service\FilterService;
use Survos\WorkflowBundle\Controller\HandleTransitionsInterface;
use Survos\WorkflowBundle\Traits\HandleTransitionsTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;


#[Route('/submission/{submissionId}')]
class SubmissionController extends AbstractController implements HandleTransitionsInterface
{
    use HandleTransitionsTrait;

    public function __construct(
//        #[Autowire('%liip_imagine.service.filter%')]
//        private FilterService $filterService,
        private MessageBusInterface $bus,
        private EntityManagerInterface $entityManager)
   {
    }

    // there must be a way to do this within the bundle, a separate route!
    #[Route(path: '/transition/{transition}', name: 'submission_transition')]
    public function transition(Request $request, WorkflowInterface $submissionStateMachine, string $transition, Submission $submission): Response
    {
        if ('_' === $transition) {
            $transition = $request->request->get('transition'); // the _ is a hack to display the form, @todo: cleanup
        }

        $this->handleTransitionButtons($submissionStateMachine, $transition, $submission);
        $this->entityManager->flush(); // to save the marking

        return $this->redirectToRoute('submission_show', $submission->getRP());
    }

    #[Route('/', name: 'submission_show', options: ['expose' => true])]
    public function show(Submission                          $submission, UploaderHelper $uploaderHelper
    ): Response
    {
//        if ($this->filterService->warmUpCache($image, $filter, null, $forced)) {
//
//        } else {
//        }
//

//        $this->bus->dispatch(new SendPhotoForApproval($submission->getId()));

        return $this->render('submission/show.html.twig', [
            'uploadHelper' => $uploaderHelper,
            'submission' => $submission,
        ]);
    }

    #[Route('/edit', name: 'submission_edit', options: ['expose' => true])]
    public function edit(Request $request, Submission $submission): Response
    {
        $form = $this->createForm(SubmissionType::class, $submission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('submission_index');
        }

        return $this->render('submission/edit.html.twig', [
            'submission' => $submission,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete', name: 'submission_delete', methods: ['DELETE'])]
    public function delete(Request $request, Submission $submission): Response
    {
        // hard-coded to getId, should be get parameter of uniqueIdentifiers()
        if ($this->isCsrfTokenValid('delete' . $submission->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($submission);
            $entityManager->flush();
        }

        return $this->redirectToRoute('submission_index');
    }
}
