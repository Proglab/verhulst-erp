<?php

namespace App\Twig\Components\Projects;

use App\Form\Type\NewProjectType;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('new_project_form', template: 'app/projects/components/new_form.html.twig')]
class NewProjectComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;


    #[LiveProp]
    public string $locale;

    public function __construct(private ProjectRepository $projectRepository)
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

        $this->projectRepository->save($post, true);

        $this->addFlash('success', 'Project saved!');
        return $this->redirectToRoute('project_details', ['project' => $post->getId()]);
    }
}