<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Marker;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class MarkerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Marker::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Marqueurs')
            ->setEntityLabelInSingular('Marqueur')
            ->setPageTitle("index", "TramStras - Administration des Marqueurs")
            ->setPaginatorPageSize(50);
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm() 
                ->hideOnIndex(),
            NumberField::new('latitude')
            ->setFormTypeOption('disabled', 'disabled'), // empêche la modificacion
            NumberField::new('longitude')
            ->setFormTypeOption('disabled', 'disabled'), // empêche la modificacion
            DateTimeField::new('creationDate')
                ->hideOnForm(), // cache l'input du form de modification
            TextField::new('User')
                ->setFormTypeOption('disabled', 'disabled'), // empêche la modificacion
            TextField::new('text'),
        ];
    }
}
