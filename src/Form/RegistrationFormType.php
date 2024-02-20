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

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Assert\Email(),
                ],
            ])
            ->add('pseudo', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Assert\Length(['min' => 3, 'max' => 20]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z0-9_\-]+$/',
                        'message' => 'Le pseudo ne peut contenir que des lettres, des chiffres, des tirets et des underscores.'
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'accepte les <a href="/conditions/CGV TramStras.pdf" target="_blank">Conditions Générales de Vente</a>',
                'label_html' => true,
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => "Vous devez accepter les termes et conditions d'utilisation.",
                    ]),
                ],
            ])
            
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe ne correspond pas.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 8, 'max' => 30]), // Mettre à jour la longueur min à 8
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre, et un caractère spécial.'
                    ]),
                ],
            ])

            ->add('imagesUsers', ImagesUsersType::class, [
                'label' => '*'
            ])

            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3(),
                'action_name' => 'register',
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'S\'inscrire',
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
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'create_account',
        ]);
    }
}
