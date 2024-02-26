<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/user/{userId}')]
class UserController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    // there must be a way to do this within the bundle, a separate route!
    #[Route(path: '/transition/{transition}', name: 'user_transition')]
    public function transition(Request $request, WorkflowInterface $userStateMachine, string $transition, User $user): Response
    {
        if ('_' === $transition) {
            $transition = $request->request->get('transition'); // the _ is a hack to display the form, @todo: cleanup
        }

        $this->handleTransitionButtons($userStateMachine, $transition, $user);
        $this->entityManager->flush(); // to save the marking

        return $this->redirectToRoute('user_show', $user->getRP());
    }

    #[Route('/', name: 'user_show', options: ['expose' => true])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit', name: 'user_edit', options: ['expose' => true])]
    public function edit(Request $request, User $user): Response
    {
        $loggedInUser = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            if ($loggedInUser == $user) {
                return $this->redirectToRoute('app_profile');
            } else {
                return $this->redirectToRoute('user_show', $user->getrp());
            }

        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete', name: 'user_delete', methods: ['DELETE', 'POST'])]
    public function delete(Request $request, User $user): Response
    {
        // hard-coded to getId, should be get parameter of uniqueIdentifiers()
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            foreach ($user->getSubmissions() as $submission) {
                $user->removeSubmission($submission);
            }
            $entityManager = $this->entityManager;

            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_homepage');
    }
}
