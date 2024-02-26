<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        /** @var User $user */
        $user = $options['data'];

        $termsUrl = $options['termsUrl']; // or even set it here.
        $builder
            ->add('email')
            ->add('creditName', null, [
                'help' => "Your name as you'd like it to appear when crediting your photo"
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'help_html' => true,
                    'help' =>
                    sprintf("I agree to the <a href='%s'>Terms and Conditions</a>", $termsUrl),
                'constraints' => [
                    new IsTrue([
                        'message' => 'You must agree to the terms before registration',
                    ]),
                ],
            ]);
        if (!$user->getPassword()) {
            $builder
                ->add('plainPassword', PasswordType::class, [
                    // instead of being set onto the object directly,
                    // this is read and encoded in the controller
                    'mapped' => false,
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'termsUrl' => null, // hmm, could set it here, too.
        ]);
    }
}
