<?php

namespace App\MessageHandler;

use App\Message\SendPhotoForApproval;
use App\Repository\SubmissionRepository;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final class SendPhotoForApprovalHandler
{


    public function __construct(
        private SubmissionRepository $submissionRepository,
        private CacheManager         $imagineCacheManager,
        private MailerInterface $mailer,
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
    )
    {
    }

    public function __invoke(
        SendPhotoForApproval $message
    )
    {
        $submission = $this->submissionRepository->find($message->getSubmissionId());


        $filter = 'squared_thumbnail_medium';
        $image = $submission->getImageName();
        $forced = false;


//        $resolvedPathx = $imagineCacheManager->getBrowserPath($image, $filter);
        $resolvedPath = $this->imagineCacheManager->resolve($image, $filter);

        $path = $this->projectDir . '/public/media/cache/' . $filter . '/' . $image;
//        dd($path, $resolvedPath, $resolvedPathx);
//        assert(file_exists($path), $path);


        $addr = 'tacman@gmail.com';
        $survos = 'tac@survos.com';
        $cidId = 'image-' . $submission->getId();
        // @todo: serialize the entities
        $email = (new TemplatedEmail())
            ->htmlTemplate('emails/submission.html.twig', ['sub'])
            ->context([
                'cidId' => $cidId,
                'imageUrl' => $resolvedPath,
//                    'submission' => $submission,
//                'event' => $submission->getEvent(),
                'submission' => [
                    'rp' => $submission->getrp(),
                    'id' => $submission->getId(),
                    'credit' => $submission->getCredit(),
                    'imageName' => $submission->getImageName()
                ],
                'expiration_date' => new \DateTime('+7 days'),
                'username' => 'foo',
            ])
//            ->addPart((new DataPart(new File($path), $cidId, 'image/jpeg'))->asInline())
//            ->addPart(new DataPart($path))
            ->from($survos)
            ->to(...[$addr])
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Photo submitted to ' . $submission->getEvent()->getTitle());
//            $email->attach(4)

        dd($email);
                $this->mailer->send($email);
        try {
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
        // do something with your message
    }
}
