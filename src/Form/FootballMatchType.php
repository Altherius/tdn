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

class FootballMatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class)
            ->add('hostingTeamScore', NumberType::class)
            ->add('receivingTeamScore', NumberType::class)
            ->add('hostingTeam', EntityType::class, [
                'class' => Team::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                }
            ])
            ->add('receivingTeam', EntityType::class, [
                'class' => Team::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                }])
            ->add('tournament', EntityType::class, [
                'class' => Tournament::class
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
