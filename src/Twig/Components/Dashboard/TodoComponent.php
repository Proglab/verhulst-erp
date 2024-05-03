<?php

namespace App\Twig\Components\Dashboard;

use App\Repository\TodoRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('todo', template: 'app/dashboard/todos.html.twig')]
class TodoComponent
{
    public function __construct(private TodoRepository $todoRepository, private Security $security)
    {

    }
    public function getTodos()
    {
        return $this->todoRepository->findNext10ByUser($this->security->getUser());
    }

}