<?php

namespace App\Form\Admin;

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
                'label' => 'Niveau (ex: Faible, Modéré)',
                'attr' => ['class' => 'input input-bordered w-full'],
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom / Titre du seuil',
                'attr' => ['class' => 'input input-bordered w-full'],
            ])
            ->add('minScore', IntegerType::class, [
                'label' => 'Score Minimum',
                'attr' => ['class' => 'input input-bordered w-full'],
            ])
            ->add('maxScore', IntegerType::class, [
                'label' => 'Score Maximum (Optionnel)',
                'required' => false,
                'attr' => ['class' => 'input input-bordered w-full'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'textarea textarea-bordered w-full', 'rows' => 3],
            ])
            ->add('advice', TextareaType::class, [
                'label' => 'Conseils',
                'attr' => ['class' => 'textarea textarea-bordered w-full', 'rows' => 3],
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
