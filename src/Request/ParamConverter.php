<?php

namespace App\Request;

use App\Entity\Author;
use App\Entity\Article;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Sport;
use App\Entity\Submission;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ParamConverter implements ValueResolverInterface
{
    /**
     * @return array<mixed>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        // get the argument type (e.g. BookingId)
        $argumentType = $argument->getType();
        switch ($argumentType) {
            case User::class:
                $value = $request->attributes->get('userId');
                $repository = $this->entityManager->getRepository($argumentType);
                $entity = $repository->findOneBy(['id' => $value]);
                return [$entity];
            case Event::class:
                $value = $request->attributes->get('eventId');
                $repository = $this->entityManager->getRepository($argumentType);
                $entity = $repository->findOneBy(['code' => $value]);
                return [$entity];
            case Sport::class:
                $value = $request->attributes->get('sportId');
                $repository = $this->entityManager->getRepository($argumentType);
                $entity = $repository->findOneBy(['id' => $value]);
                return [$entity];
            case Team::class:
                $value = $request->attributes->get('teamId');
                $repository = $this->entityManager->getRepository($argumentType);
                $entity = $repository->findOneBy(['id' => $value]);
                return [$entity];
            case Location::class:
                $value = $request->attributes->get('locationId');
                $repository = $this->entityManager->getRepository($argumentType);
                $entity = $repository->findOneBy(['code' => $value]);
                return [$entity];
            case Submission::class:
                $repository = $this->entityManager->getRepository($argumentType);
                $value = $request->attributes->get('submissionId');
                $event = $repository->findOneBy(['id' => $value]);
                return [$event];
            case Author::class:
                $repository = $this->entityManager->getRepository($argumentType);
                $value = $request->attributes->get('authorId');
                $song = $repository->findOneBy(['id' => $value]);
                return [$song];
            case Article::class:
                $repository = $this->entityManager->getRepository($argumentType);
                $value = $request->attributes->get('articleId');
                if (!is_string($value)) {
                    return [];
                }
                // Try to find video by its uniqueParameters.  Inspect the class to get this
                return [$repository->findOneBy(['id' => $value])];

        }

        return [];
    }

    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }


}
