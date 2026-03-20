<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\Response as QuizResponse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', EntityType::class, [
                'class' => Question::class,
                'choice_label' => 'title',
                'label' => 'Question associée',
                'placeholder' => 'Sélectionner une question',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Intitulé de la réponse',
                'required' => true,
            ])
            ->add('points', IntegerType::class, [
                'label' => 'Points',
                'required' => true,
                'empty_data' => '0',
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'required' => true,
                'empty_data' => '0',
                'attr' => ['min' => 1],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuizResponse::class,
        ]);
    }
}
