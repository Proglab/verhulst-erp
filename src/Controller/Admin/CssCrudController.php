<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Css;
use App\Repository\CssRepository;
use App\Service\SecurityChecker;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CssCrudController extends BaseCrudController
{
    public function __construct(private CssRepository $cssRepository, private AdminUrlGenerator $adminUrlGenerator, protected SecurityChecker $securityChecker)
    {
        parent::__construct($securityChecker);
    }

    public static function getEntityFqcn(): string
    {
        return Css::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setEntityLabelInPlural('Css')
            ->setEntityLabelInSingular('Css')
            ->showEntityActionsInlined(true)
        ->setHelp(Crud::PAGE_EDIT, 'Ne modifier que si vous savez ce que vous faites !!!')
        ->setEntityPermission('ROLE_TECH');

        return parent::configureCrud($crud);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            CodeEditorField::new('content')->setLanguage('css')->setHelp('Ne modifier que si vous savez ce que vous faites !!!')->setLabel('Contenu du fichier CSS'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions); // TODO: Change the autogenerated stub

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_RETURN)
            ->disable(Action::NEW)
            ->disable(Action::DELETE)
            ->disable(Action::INDEX)
            ->disable(Action::SAVE_AND_ADD_ANOTHER)
            ->disable(Action::SAVE_AND_CONTINUE)
            ->disable(Action::DETAIL)

        ;
    }

    public function index(AdminContext $context): RedirectResponse
    {
        $css = $this->cssRepository->findOneBy([]);
        if (empty($css)) {
            $path = realpath(__DIR__ . '/../../../../../shared/public/css/app.css');
            $css = new Css();
            if (!file_exists(realpath($path))) {
                file_put_contents($path, '');
            }
            $css->setContent(file_get_contents(realpath($path)));
            $this->cssRepository->save($css, true);
        }

        $url = $this->adminUrlGenerator->setAction(Action::EDIT)->setEntityId($css->getId());

        return $this->redirect($url->generateUrl());
    }

    /**
     * @param Css $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub

        file_put_contents(realpath(__DIR__ . '/../../../../../shared/public/css/app.css'), $entityInstance->getContent());
    }
}
