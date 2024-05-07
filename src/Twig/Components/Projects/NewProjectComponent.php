<?php

namespace App\Twig\Components\Projects;

use App\Entity\Todo;
use App\Entity\User;
use App\Form\Type\NewProjectType;
use App\Repository\SalesRepository;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsLiveComponent('new_project_form', template: 'app/projects/new_form.html.twig')]
class NewProjectComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;


    #[LiveProp]
    public string $locale;

    public function __construct()
    {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(NewProjectType::class);
    }

    #[LiveAction]
    public function save()
    {
        $this->submitForm();
        $post = $this->getForm()->getData();
        dd($post);
    }
}