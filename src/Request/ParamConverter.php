<?php

namespace App\Request;

use App\Entity\Author;
use App\Entity\Article;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ParamConverter implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        // get the argument type (e.g. BookingId)
        $argumentType = $argument->getType();
        switch ($argumentType) {
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
