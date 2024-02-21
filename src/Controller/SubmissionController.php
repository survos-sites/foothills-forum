<?php

// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use App\Entity\Submission;
use App\Form\SubmissionType;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Survos\WorkflowBundle\Controller\HandleTransitionsInterface;
use Survos\WorkflowBundle\Traits\HandleTransitionsTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

#[Route('/submission/{submissionId}')]
class SubmissionController extends AbstractController implements HandleTransitionsInterface
{
    use HandleTransitionsTrait;
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
        public function show(Submission $submission, UploaderHelper $uploaderHelper
    , MailerInterface $mailer, CacheManager $imagineCacheManager,
    #[Autowire('%kernel.project_dir%')] $projectDir
    ): Response
        {

            $resolvedPath = $imagineCacheManager->getBrowserPath($submission->getImageName(), 'squared_thumbnail_medium');
//            $path = $projectDir . '/public/media/cache/squared_thumbnail_medium/' . $submission->getImageName();
//            assert(file_exists($path), $path);



        $addr = 'tacman@gmail.com';
            $survos = 'tac@survos.com';
            // @todo: dispatch!
            $email = (new TemplatedEmail())
                ->htmlTemplate('emails/submission.html.twig', ['sub'])
                ->context([
                    'imageUrl' => $resolvedPath,
                    'submission' => $submission,
                    'expiration_date' => new \DateTime('+7 days'),
                    'username' => 'foo',
                ])
//                ->addPart(new DataPart($path))
                ->from($survos)
                ->to(...[$addr] )
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                ->priority(Email::PRIORITY_HIGH)
                ->subject('Photo submitted to ' . $submission->getEvent()->getTitle())
                // get the image contents from a PHP resource
                // get the image contents from an existing file
                ->html(sprintf('<p>See Twig integration for better HTML integration!

%s
</p>', $submission->getImageName()));
//            $email->attach(4)

        try {
//            $mailer->send($email);
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }

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
            if ($this->isCsrfTokenValid('delete'.$submission->getId(), $request->request->get('_token'))) {
                $entityManager = $this->entityManager;
                $entityManager->remove($submission);
                $entityManager->flush();
            }

            return $this->redirectToRoute('submission_index');
        }
}
