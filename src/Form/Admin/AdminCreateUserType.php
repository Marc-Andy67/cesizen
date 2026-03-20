<?php

namespace App\Form\Admin;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Validator\StrongPassword;

/**
 * @extends AbstractType<User>
 */
class AdminCreateUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom complet',
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner un nom.'),
                    new Length(
                        min: 2,
                        max: 255,
                        minMessage: 'Le nom doit faire au moins {{ limit }} caractères.',
                    ),
                ],
                'attr' => ['class' => 'input input-bordered w-full', 'placeholder' => 'Jean Dupont'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner un email.'),
                    new Email(message: 'L\'email {{ value }} n\'est pas valide.'),
                ],
                'attr' => ['class' => 'input input-bordered w-full', 'placeholder' => 'jean.dupont@cesizen.fr'],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'constraints' => [
                    new NotBlank(message: 'Veuillez renseigner un mot de passe.'),
                    new StrongPassword(),
                ],
                'attr' => ['class' => 'input input-bordered w-full', 'autocomplete' => 'new-password'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
