<?php

declare(strict_types=1);

namespace App\Twig\Components\Dashboard;

use App\Entity\Todo;
use App\Form\Type\TodoType;
use App\Repository\TodoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('todo_form', template: 'app/dashboard/todos_form.html.twig')]
class TodoFormComponent extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp(updateFromParent: true)]
    public ?Todo $todoUpdate = null;

    public function __construct(private TodoRepository $todoRepository, private Security $security)
    {
    }

    #[LiveAction]
    public function save()
    {
        $this->submitForm();

        /** @var Todo $post */
        $post = $this->getForm()->getData();

        $this->todoUpdate = $post;
        $this->todoRepository->save($post, true);

        $this->addFlash('success', 'Todo saved!');
        $this->resetForm();
        $this->dispatchBrowserEvent('modal:close');

        $this->emit('refreshTodo');
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(TodoType::class, $this->todoUpdate);
    }
}
