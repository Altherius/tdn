<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\Tournament;
use App\Entity\Trophy;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrophyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du trophée'
            ])
            ->add('tournament', EntityType::class, [
                'class' => Tournament::class,
                'label' => 'Tournoi',
                'choice_name' => 'name'
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'label' => 'Équipe',
                'choice_name' => 'name'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trophy::class,
        ]);
    }
}
