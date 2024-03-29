<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service\ServiceControl\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/author/{authorId}')]
class AuthorController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    // there must be a way to do this within the bundle, a separate route!
    #[Route(path: '/transition/{transition}', name: 'author_transition')]
    public function transition(Request $request, WorkflowInterface $authorStateMachine, string $transition, Author $author): Response
    {
        if ('_' === $transition) {
            $transition = $request->request->get('transition'); // the _ is a hack to display the form, @todo: cleanup
        }

        $this->handleTransitionButtons($authorStateMachine, $transition, $author);
        $this->entityManager->flush(); // to save the marking

        return $this->redirectToRoute('author_show', $author->getRP());
    }

    #[Route('/', name: 'author_show', options: ['expose' => true])]
        public function show(Author $author): Response
        {
            return $this->render('author/show.html.twig', [
                'author' => $author,
                'class' => Author::class
            ]);
        }


}
