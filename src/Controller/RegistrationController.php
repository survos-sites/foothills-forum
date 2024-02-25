<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private UserProviderInterface $userProvider)
    {

    }
    #[Route('/register', name: 'app_register')]
    public function register(Request $request,
                             #[MapQueryParameter] string $clientKey=null,
                             #[MapQueryParameter] string $userId=null,
                             UserPasswordHasherInterface $userPasswordHasher,
                             UserAuthenticatorInterface $userAuthenticator,
                             AppAuthenticator $authenticator,
                             EntityManagerInterface $entityManager
    ): Response
    {
        $identifier = $request->get('userId');
        $user = $identifier ? $this->userProvider->loadUserByIdentifier($identifier): new User();
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
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            if ($form->has('plainPassword'))
            {
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
