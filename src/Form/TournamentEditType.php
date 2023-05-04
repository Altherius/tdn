<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\Tournament;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TournamentEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startedAt', DateType::class, [
                'label' => 'Date de début'
            ])
            ->add('endedAt', DateType::class, [
                'label' => 'Date de fin'
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom du tournoi'
            ])
            ->add('major', CheckboxType::class, [
                'label' => 'Tournoi majeur (donne des étoiles)',
                'required' => false
            ])
            ->add('balancing', CheckboxType::class, [
                'label' => 'Tournoi de rééquilibrage',
                'required' => false
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du tournoi',
                'required' => false
            ])
            ->add('eloMultiplier', NumberType::class, [
                'label' => 'Multiplicateur Elo',
                'scale' => 2,
            ])
            ->add('startedAt', DateType::class, [
                'label' => 'Date de début'
            ])
            ->add('winner', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'label' => 'Vainqueur du tournoi',
                'required' => false,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'attr' => [
                    'class' => 'select2'
                ]
            ])
            ->add('finalists', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'label' => 'Finalistes du tournoi',
                'multiple' => true,
                'required' => false,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'attr' => [
                    'class' => 'select2'
                ]
            ])
            ->add('finalPhasesTeams', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'label' => 'Équipes en phases finales',
                'multiple' => true,
                'required' => false,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'attr' => [
                    'class' => 'select2'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tournament::class,
        ]);
    }
}
