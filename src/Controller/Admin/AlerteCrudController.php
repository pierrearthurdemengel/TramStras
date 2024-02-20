<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Alerte;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AlerteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Alerte::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Alertes')
            ->setEntityLabelInSingular('Alerte')
            ->setPageTitle("index", "TramStras - Administration des Alertes")
            ->setPaginatorPageSize(50);
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm() 
                ->hideOnIndex(),
            TextField::new('ligne')
                ->setFormTypeOption('disabled', 'disabled'), // empêche la modificacion
            TextField::new('sens')
                   ->setFormTypeOption('disabled', 'disabled'), // empêche la modificacion
            DateTimeField::new('alerteDate')
                ->setFormTypeOption('disabled', 'disabled'), // empêche la modificacion
            IdField::new('user')
                ->setFormTypeOption('disabled', 'disabled'), // empêche la modificacion
        ];
    }
    
}
