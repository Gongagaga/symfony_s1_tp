<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PasswordChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('plainPassword', PasswordType::class, [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
            'label' => 'Nouveau mot de passe',
            'constraints' => [
                new NotBlank([
                    'message' => 'Le mot de passe est obligatoire',
                ]),
                new Length([
                    'min' => 8,
                    'minMessage' => 'Le mot de passe doit faire au-moins {{ limit }} caractères',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
            ]
            ])
        ->add('passwordConfirm', PasswordType::class, [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
            'label' => 'Confirmer nouveau mot de passe',
            'constraints' => [
                new NotBlank([
                    'message' => 'Le mot de passe est obligatoire',
                ]),
                new Length([
                    'min' => 8,
                    'minMessage' => 'Le mot de passe doit faire au-moins {{ limit }} caractères',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
            ]
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Changer mot de passe',
            'attr' => [
                'class' => 'btn btn-dark'
            ]
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
