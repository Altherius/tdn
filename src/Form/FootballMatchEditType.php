<?php

namespace App\Form;

use App\Entity\FootballMatch;
use App\Entity\Team;
use App\Entity\Tournament;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FootballMatchEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('description', TextareaType::class, [
                'label' => 'Description'
            ])
            ->add('hostingTeamScore', NumberType::class, [
                'html5' => true,
                'label' => 'Score'
            ])
            ->add('receivingTeamScore', NumberType::class, [
                'html5' => true,
                'label' => 'Score'
            ])
            ->add('hostingTeam', EntityType::class, [
                'class' => Team::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'label' => 'Équipe domicile'
            ])
            ->add('receivingTeam', EntityType::class, [
                'class' => Team::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'label' => 'Équipe extérieur'
            ])
            ->add('tournament', EntityType::class, [
                'class' => Tournament::class,
                'label' => 'Tournoi'
            ])
            ->add('winner', EntityType::class, [
                'class' => Team::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'label' => 'Vainqueur',
                'required' => false
            ])
            ->add('loser', EntityType::class, [
                'class' => Team::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'label' => 'Perdant',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FootballMatch::class,
        ]);
    }
}
