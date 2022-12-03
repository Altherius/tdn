<?php

namespace App\Form;

use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('region', ChoiceType::class, [
                'label' => 'Région',
                'choices' => [
                    'Afrique' => "Afrique",
                    'Asie' => "Asie",
                    'Amérique du Nord' => "Amérique du Nord",
                    'Amérique du Sud' => "Amérique du Sud",
                    'Europe' => "Europe",
                    'Océanie' => "Océanie",
                ]
            ])
            ->add('color', ColorType::class, [
                'label' => 'Couleur'
            ])
            ->add('countryCode', CountryType::class, [
                'label' => 'Pays'
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
