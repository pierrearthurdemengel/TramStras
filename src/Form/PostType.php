<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextareaType::class, [
                'label' => ' ',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 2000]), // par exemple, limitez à 2000 caractères
                    new Assert\Regex([
                        'pattern' => '/^[\p{L}\p{N}\s.,!?()\-\']++$/uD', // cela permet les lettres, les chiffres, certains signes de ponctuation
                        'message' => 'Le post contient des caractères non valides.'
                    ]),
                ],
                'trim' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'create_post',
        ]);
    }
}
