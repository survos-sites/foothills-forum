<?php

namespace App\MessageHandler;

use App\Message\SendPhotoForApproval;
use App\Repository\SubmissionRepository;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsMessageHandler]
final class SendPhotoForApprovalHandler
{


    public function __construct(
        private SubmissionRepository $submissionRepository,
        private CacheManager         $imagineCacheManager,
        private NormalizerInterface $normalizer,
        private MailerInterface $mailer,
        #[Autowire('%env(json:PHOTO_REVIEWERS)%')] private array $reviewers,
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


//        $eventData = $this->normalizable->normalize($event, '');
        $submissionData = [];
        $submissionData = $this->normalizer->normalize($submission, null, ['groups' => ['submission.email', 'submission.read','rp']]);
        $survos = 'tac@survos.com';
        $addresses = [];
        foreach ($this->reviewers as $reviewer) {
            $addresses[] = new Address($reviewer['email'], $reviewer['name']);
        }
        $cidId = 'image-' . $submission->getId();
        $email = (new TemplatedEmail())
            ->htmlTemplate('emails/submission.html.twig', ['sub'])
            ->context($context = [
                'cidId' => $cidId,
                'imageUrl' => $resolvedPath,
//                    'submission' => $submission,
//                'event' => $submission->getEvent(),
                'submission' =>$submissionData,
                'expiration_date' => new \DateTime('+7 days'),
                'username' => 'foo',
            ])
//            ->addPart((new DataPart(new File($path), $cidId, 'image/jpeg'))->asInline())
//            ->addPart(new DataPart($path))
            ->from($survos)
            ->to(...$addresses)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject(sprintf('Photo #%d: %s', $submission->getId(), $submission->getEvent()->getTitle()))
        ;
//            $email->attach(4)

//        dd($context, $email);
                $this->mailer->send($email);
        try {
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
        // do something with your message
    }
}
