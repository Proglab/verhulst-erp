<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Microsoft\Graph\GraphServiceClient;


#[IsGranted(User::ROLE_USER)]
class ProjectController extends AbstractController
{
    #[Route('/app/{_locale}/projects/new', name: 'project_new')]
    public function project_new(Request $request, $_locale): Response
    {
        return $this->render('app/projects/new.html.twig', [
            'locale' => $_locale,
        ]);
    }

    #[Route('/app/{_locale}/projects/{project}', name: 'project_details')]
    public function project_details(Request $request, $_locale, Project $project): Response
    {
        return $this->render('app/projects/details.html.twig', [
            'locale' => $_locale,
            'project' => $project,
        ]);
    }
    #[Route('/app/{_locale}/projects/{project}/addEvent', name: 'project_add_event')]
    public function project_add_event(Request $request, $_locale, Project $project): Response
    {
        return $this->render('app/projects/add_event.html.twig', [
            'locale' => $_locale,
            'project' => $project,
        ]);
    }
}