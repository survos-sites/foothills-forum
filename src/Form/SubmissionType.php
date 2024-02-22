<?php

namespace App\Form;

use App\Entity\Submission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class SubmissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Submission $submission */
        $submission = $options['data'];
        $builder
            ->add('imageFile', VichImageType::class, [
                'label' => "Your photo",
                'help' => "photo must be less than " . ini_get("upload_max_filesize")
            ]);
        if ($submission->getEvent()) {
            $builder
                ->add('event', null, ['disabled' => true]);
        }
        if ($submission->getLocation()) {
            $builder
                ->add('location', null, ['disabled' => true]);
        }
        $builder->add('notes', null, [
            'required' => false,
            'help' => "e.g. Player name, action, etc."
        ]);
        $builder
            ->add('email', EmailType::class, [
                'required' => true

            ])
            ->add('credit', null, [
            'required' => true,
            'help' => "Your name for photo credit"
        ])
            ->add('agree_to_terms', CheckboxType::class, [
                'required' => true,
                'mapped' => false,
                'help' => "I agree to license this photo without restriction to Rappahannock News, Foothills Forum and licensees"
            ])
        ;

//            ->add('imageName')
//            ->add('imageSize')
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Submission::class,
        ]);
    }
}
