<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Utilisateurs')
            ->setEntityLabelInSingular('Utilisateur')
            ->setPageTitle("index", "TramStras - Administration des utilisateurs")
            ->setPaginatorPageSize(50);
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm() 
                ->hideOnIndex(),
            TextField::new('pseudo'),
            TextField::new('email')
                ->hideOnForm(),
            TextField::new('password')
                ->hideOnForm(),
            BooleanField::new('isVerified')
                ->setFormTypeOption('disabled', 'disabled'),
            ArrayField::new('roles'),
        ];
    }
        
}
