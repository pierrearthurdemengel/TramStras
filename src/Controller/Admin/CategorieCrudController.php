<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CategorieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Categorie::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Catégories')
            ->setEntityLabelInSingular('Catégorie')
            ->setPageTitle("index", "TramStras - Administration des Catégories")
            ->setPaginatorPageSize(50);
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm() 
                ->hideOnIndex(),
            TextField::new('nom_categorie'),
            AssociationField::new('topics')
                ->setFormTypeOption('disabled', 'disabled') // empêche la modificacion
                ->hideOnIndex(), // cache dans l'index
        ];
    }
}
