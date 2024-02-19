<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use App\Entity\Submission;
use App\Form\SubmissionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/submission/{submissionId}')]
class SubmissionController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
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
        public function show(Submission $submission): Response
        {
            return $this->render('submission/show.html.twig', [
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
            if ($this->isCsrfTokenValid('delete'.$submission->getId(), $request->request->get('_token'))) {
                $entityManager = $this->entityManager;
                $entityManager->remove($submission);
                $entityManager->flush();
            }

            return $this->redirectToRoute('submission_index');
        }
}
