<?php

namespace App\Form;

use App\Entity\StressThreshold;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StressThresholdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('level', TextType::class, [
                'label' => 'Identifiant technique (ex: LowStress)',
                'disabled' => true, // L'identifiant correspond aux classes Strategy
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom public (ex: Stress mineur)',
            ])
            ->add('minScore', IntegerType::class, [
                'label' => 'Score minimum (inclus)',
            ])
            ->add('maxScore', IntegerType::class, [
                'label' => 'Score maximum (exclu, optionnel)',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du diagnostic',
                'attr' => ['rows' => 4],
            ])
            ->add('advice', TextareaType::class, [
                'label' => 'Recommandations & Conseils',
                'attr' => ['rows' => 4],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StressThreshold::class,
        ]);
    }
}
