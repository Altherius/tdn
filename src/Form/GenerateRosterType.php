<?php

namespace App\Form;

use App\Entity\Team;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenerateRosterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod(Request::METHOD_GET)
            ->add('teamsCount', IntegerType::class, [
                'label' => 'Nombre de places',
                'help' => 'En comptant les places réservées.',
                'attr' => [
                    'value' => 64
                ]
            ])
            ->add('qualifiedTeams', EntityType::class, [
                'class' => Team::class,
                'label' => 'Équipes qualifiées',
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'attr' => [
                    'class' => 'select2'
                ],
                'required' => false
            ])
            ->add('threeTicketsTeams', EntityType::class, [
                'class' => Team::class,
                'label' => 'Équipes avec 3 tickets',
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'attr' => [
                    'class' => 'select2'
                ],
                'required' => false
            ])
            ->add('twoTicketsTeams', EntityType::class, [
                'class' => Team::class,
                'label' => 'Équipes avec 2 tickets',
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'attr' => [
                    'class' => 'select2'
                ],
                'required' => false
            ])
            ->add('oneTicketTeams', EntityType::class, [
                'class' => Team::class,
                'label' => 'Équipes avec 1 ticket',
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'attr' => [
                    'class' => 'select2'
                ],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
