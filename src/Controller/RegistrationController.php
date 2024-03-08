<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class RegistrationController extends AbstractController
{
    use TargetPathTrait;
    public function __construct(private UserProviderInterface $userProvider,
                                private ChatterInterface      $chatter,
    )
    {

    }

    #[Route('/register', name: 'app_register')]
    public function register(Request                     $request,
                             #[MapQueryParameter] string $clientKey = null,
                             #[MapQueryParameter] string $userId = null,
                             UserPasswordHasherInterface $userPasswordHasher,
                             UserAuthenticatorInterface  $userAuthenticator,
                             AppAuthenticator            $authenticator,
                             UrlGeneratorInterface       $urlGenerator,
                             EntityManagerInterface      $entityManager
    ): Response
    {
        $identifier = $request->get('userId');
        $user = $identifier ? $this->userProvider->loadUserByIdentifier($identifier) : new User();
        if ($clientKey) {
            $clientData = $user->getIdentifierData($clientKey);
            foreach ($clientData['data'] as $key => $value) {
                $user = match ($key) {
                    'email' => $user->setEmail($value),
                    'name' => $user->setCreditName($value),
                    default => $user
                };
            }
        }
//        $user->setEmail($request->get('email'));
        $form = $this->createForm(RegistrationFormType::class, $user, ['termsUrl' => $urlGenerator->generate('app_terms')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            if ($form->has('plainPassword')) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            $message = (new ChatMessage(sprintf($user->getUserIdentifier() . ' just registered!')))
                ->transport('slack');
            $sentMessage = $this->chatter->send($message);


            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
