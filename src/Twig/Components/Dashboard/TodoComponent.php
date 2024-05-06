<?php

namespace App\Twig\Components\Dashboard;

use App\Entity\Todo;
use App\Repository\TodoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('todo', template: 'app/dashboard/todos.html.twig')]
class TodoComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    public ?Todo $todo = null;

    public function __construct(private TodoRepository $todoRepository, private Security $security)
    {
    }
    public function getTodos()
    {
        return $this->todoRepository->findNext10ByUser($this->security->getUser());
    }

    #[LiveAction]
    public function valid(#[LiveArg] int $id)
    {
        $todo = $this->todoRepository->find($id);
        $todo->setDone(true);
        $todo->setDateDone(new \DateTime('now'));
        $this->todoRepository->save($todo, true);
    }

    #[LiveAction]
    public function unvalid(#[LiveArg] int $id)
    {
        $todo = $this->todoRepository->find($id);
        $todo->setDone(false);
        $todo->setDateDone(null);
        $this->todoRepository->save($todo, true);
    }

    #[LiveAction]
    public function edit(#[LiveArg] int $id)
    {
        $this->todo = $this->todoRepository->find($id);
        $this->emit('editTodo', ['id' => $id]);
        $this->dispatchBrowserEvent('modal:open');
    }

    #[LiveListener('refreshTodo')]
    public function refresh()
    {

    }
}