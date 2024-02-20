<?php

namespace App\Controller\Admin;

use App\Entity\ImagesUsers;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ImagesUsersCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ImagesUsers::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            'imageName',
            'imageSize',
            'updatedAt',
            'createdAt',
            AssociationField::new('user'),
            TextareaField::new('imageFile', "ImagesUsers")
    ->setFormType(VichImageType::class)
    ->hideOnIndex()
    ->setFormTypeOption('allow_delete', false)
    ->setLabel('Image')

        ];
    }
}
