<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Postes')
            ->setEntityLabelInSingular('Poste')
            ->setPageTitle("index", "TramStras - Administration des Postes")
           
            ->setPaginatorPageSize(50)

            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig');

    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm() 
                ->hideOnIndex(),
            TextField::new('text')
                ->setFormType(CKEditorType::Class),
            DateTimeField::new('datePost')
                ->setFormTypeOption('disabled', 'disabled'), // empêche la modificacion
            IdField::new('topic')
                ->setFormTypeOption('disabled', 'disabled'), // empêche la modificacion
            IdField::new('user')
                ->setFormTypeOption('disabled', 'disabled'), // empêche la modificacion
        ];
    }
}
