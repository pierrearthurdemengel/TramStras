<?php

namespace App\Form;

use App\Entity\Topic;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TopicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le titre ne peut pas être vide',
                    ]),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le titre doit comporter au moins {{ limit }} caractères',
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[\p{L}\p{N}\s.,!?()\-\']++$/uD', // cela permet les lettres, les chiffres, certains signes de ponctuation
                        'message' => 'Le topic contient des caractères non valides.'
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Topic::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'create_topic',
        ]);
    }
}