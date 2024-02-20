<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ImagesUsersType;
use Symfony\Component\Form\AbstractType;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('pseudo', TextType::class, [
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 3, 'max' => 20]),
                new Assert\Regex([
                    'pattern' => '/^[a-zA-Z0-9_\-]+$/',
                    'message' => 'Le pseudo ne peut contenir que des lettres, des chiffres, des tirets et des underscores.'
                ]),
            ],
        ])
        ->add('plainPassword', PasswordType::class, [
            'mapped' => false,
            'label' => 'Vérification du mot de passe',
            'attr' => ['class' => 'password-field'],
            'required' => true,
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 8, 'max' => 30]), 
                new Assert\Regex([
                    'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/',
                    'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre, et un caractère spécial.'
                ]),
            ],
        ])
        
        
        ->add('submit', SubmitType::class, [
            'label' => 'Modifier les informations personelles',
            'attr' => [
                'class' => 'custom-login-button btn btn-lg btn-primary'
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
