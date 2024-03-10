<?php

namespace App\Form;

use App\Entity\School;
use App\Entity\Sport;
use App\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('code')
            ->add('section')
            ->add('rSchoolId')
            ->add('school', EntityType::class, [
                'class' => School::class,
'choice_label' => 'id',
            ])
            ->add('sport', EntityType::class, [
                'class' => Sport::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
