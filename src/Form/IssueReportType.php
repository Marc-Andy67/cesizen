<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire public de signalement d'un problème sur l'application.
 *
 * Non mappé sur une entité : les données saisies sont directement transmises
 * au service GitHubIssueService pour créer un ticket de suivi (voir
 * App\Controller\Front\LegalController::contact()).
 */
class IssueReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre email (pour être recontacté)',
                'required' => false,
                'attr' => ['placeholder' => 'vous@exemple.fr'],
            ])
            ->add('environnement', ChoiceType::class, [
                'label' => 'Où avez-vous rencontré le problème ?',
                'choices' => [
                    'Page d\'accueil' => 'accueil',
                    'Compte / connexion' => 'compte',
                    'Espace documentation' => 'documentation',
                    'Diagnostic de stress' => 'diagnostic',
                    'Autre' => 'autre',
                ],
                'placeholder' => 'Sélectionnez une rubrique',
                'constraints' => [
                    new NotBlank(message: 'Merci de préciser où le problème est survenu.'),
                ],
            ])
            ->add('sujet', TextType::class, [
                'label' => 'Résumé du problème',
                'attr' => ['placeholder' => 'Ex : Le questionnaire de diagnostic ne se valide pas'],
                'constraints' => [
                    new NotBlank(message: 'Merci de résumer le problème en quelques mots.'),
                    new Length(max: 150),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description détaillée',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => "Que s'est-il passé ? Que vous attendiez-vous à voir à la place ?",
                ],
                'constraints' => [
                    new NotBlank(message: 'Merci de décrire le problème rencontré.'),
                    new Length(min: 10, max: 3000),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'issue_report',
        ]);
    }
}
