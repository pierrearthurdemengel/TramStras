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

class UserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void 
    {
        $builder      
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe ne correspond pas.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation du Mot de passe'],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 8, 'max' => 30]), // Mettre à jour la longueur min à 8
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre, et un caractère spécial.'
                    ]),
                ],
            ])
            ->add('newPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'label' =>'Nouveau mot de passe',
                'constraints' => [new Assert\NotBlank()]
            ])

            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3(),
                'action_name' => 'register',
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Changer mon mot de passe',
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
