<?php

namespace App\Form;

use App\Entity\ContactMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 100]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z\s]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres et des espaces.'
                    ]),
                ],
                'trim' => true,
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
                'trim' => true,
            ])
            ->add('message', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 1000]),
                ],
                'trim' => true,
            ])
            ->add('phone', TelType::class, [
                'label' => 'Numéro de Téléphone',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 20]),
                ],
                'trim' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContactMessage::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'form_intention',
        ]);
    }
}