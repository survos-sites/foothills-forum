<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Submission;
use App\Form\LocationType;
use App\Form\SubmissionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/location/{locationId}')]
class LocationController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    // there must be a way to do this within the bundle, a separate route!
    #[Route(path: '/transition/{transition}', name: 'location_transition')]
    public function transition(Request $request, WorkflowInterface $locationStateMachine, string $transition, Location $location): Response
    {
        if ('_' === $transition) {
            $transition = $request->request->get('transition'); // the _ is a hack to display the form, @todo: cleanup
        }

        $this->handleTransitionButtons($locationStateMachine, $transition, $location);
        $this->entityManager->flush(); // to save the marking

        return $this->redirectToRoute('location_show', $location->getRP());
    }

    #[Route('/', name: 'location_show', options: ['expose' => true])]
        public function show(Location $location): Response
        {
            return $this->render('location/show.html.twig', [
                'location' => $location,
            ]);
        }

    #[Route('/edit', name: 'location_edit', options: ['expose' => true])]
        public function edit(Request $request, Location $location): Response
        {
            $form = $this->createForm(LocationType::class, $location);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->flush();

                return $this->redirectToRoute('location_index');
            }

            return $this->render('location/edit.html.twig', [
                'location' => $location,
                'form' => $form->createView(),
            ]);
        }

    #[Route('submission/new', name: 'location_submission_new', options: ['expose' => true])]
    public function new(Location $location, Request $request): Response
    {
        $submission = new Submission();

        $location->addSubmission($submission);
//        $submission->setEvent($event);
        $form = $this->createForm(SubmissionType::class, $submission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($submission);
            $entityManager->flush();

            return $this->redirectToRoute('submission_show', $submission->getrp());
        }

        return $this->render('submission/new.html.twig', [
            'submission' => $submission,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/delete', name: 'location_delete', methods: ['DELETE'])]
        public function delete(Request $request, Location $location): Response
        {
            // hard-coded to getId, should be get parameter of uniqueIdentifiers()
            if ($this->isCsrfTokenValid('delete'.$location->getId(), $request->request->get('_token'))) {
                $entityManager = $this->entityManager;
                $entityManager->remove($location);
                $entityManager->flush();
            }

            return $this->redirectToRoute('location_index');
        }
}
