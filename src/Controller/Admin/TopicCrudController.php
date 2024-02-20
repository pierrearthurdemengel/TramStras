<?php

namespace App\Controller\Admin;

use App\Entity\Topic;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TopicCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Topic::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Topics')
            ->setEntityLabelInSingular('Topic')
            ->setPageTitle("index", "TramStras - Administration des Topics")
            ->setPaginatorPageSize(50);
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm() 
                ->hideOnIndex(),
            TextField::new('titre'),
            DateTimeField::new('creationDate')
                ->hideOnForm(), // cache l'input du form de modification
            AssociationField::new('categorie')
                ->hideOnForm(), // cache l'input du form de modification
            AssociationField::new('posts')
                ->setFormTypeOption('disabled', 'disabled') // empêche la modificacion
                ->hideOnIndex(), // cache dans l'index

        ];
    }
}
