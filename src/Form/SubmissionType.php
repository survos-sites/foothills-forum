<?php

namespace App\Form;

use App\Entity\Submission;
use Symfony\Component\Form\AbstractType;
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
            'required' => false
        ]);

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
