<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Submission;
use App\Form\EventType;
use App\Form\SubmissionType;
use App\Message\SendPhotoForApproval;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/event/{eventId}')]
class EventController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    // there must be a way to do this within the bundle, a separate route!
    #[Route(path: '/transition/{transition}', name: 'event_transition')]
    public function transition(Request $request, WorkflowInterface $eventStateMachine, string $transition, Event $event): Response
    {
        if ('_' === $transition) {
            $transition = $request->request->get('transition'); // the _ is a hack to display the form, @todo: cleanup
        }

        $this->handleTransitionButtons($eventStateMachine, $transition, $event);
        $this->entityManager->flush(); // to save the marking

        return $this->redirectToRoute('event_show', $event->getRP());
    }

    #[Route('/', name: 'event_show', options: ['expose' => true])]
    #[Template('event/show.html.twig')]
    public function show(Event $event): Response|array
    {
        return get_defined_vars();
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/slideshow', name: 'event_slideshow', options: ['expose' => true])]
    #[Template('event/slideshow.html.twig')]
    public function slideshow(Event $event): Response|array
    {
        return get_defined_vars();
    }





    #[Route('/edit', name: 'event_edit', options: ['expose' => true])]
    public function edit(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete', name: 'event_delete', methods: ['DELETE'])]
    public function delete(Request $request, Event $event): Response
    {
        // hard-coded to getId, should be get parameter of uniqueIdentifiers()
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index');
    }
}
